<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoReservacion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Programacion;
use App\Models\Reservacion;
use App\Models\User;
use App\Services\Finanzas\Dinero;
use Database\Seeders\MetodoPagoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FinanzasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(now()->setDate(2026, 11, 17)->setTime(12, 0));
    }

    public function test_las_finanzas_exigen_sesion_y_autorizacion_en_cada_accion(): void
    {
        $venta = $this->venta();
        $this->pagoConfirmado($venta, '100', now()->toDateTimeString());
        foreach (['/pagos', '/pagos/crear', '/administracion/ventas', '/administracion/finanzas'] as $ruta) {
            $this->get($ruta)->assertRedirect('/acceso');
        }
        $this->actingAs(User::factory()->create());
        config(['administracion.usuarios' => []]);
        foreach (['/pagos', '/pagos/crear', '/pagos/1', '/administracion/ventas', '/administracion/ventas/1', '/administracion/finanzas'] as $ruta) {
            $this->get($ruta)->assertForbidden();
        }
        foreach (['/pagos', '/pagos/1/confirmar', '/pagos/1/anular', '/administracion/ventas/1/confirmar'] as $ruta) {
            $this->post($ruta, [])->assertForbidden();
        }
        $this->assertDatabaseCount('pagos', 1);
    }

    public function test_listados_vacios_y_menu_conservan_los_modulos_del_equipo(): void
    {
        $this->autorizar();
        $this->get('/pagos')->assertOk()->assertSee('No hay pagos para mostrar')->assertSee('Calendario y cupos')->assertSee('Canales externos');
        $this->get('/pagos/crear')->assertOk()->assertSee('Primero confirma una venta');
        $this->get('/administracion/ventas')->assertOk()->assertSee('No hay solicitudes para mostrar');
        $this->get('/administracion/finanzas')->assertOk()->assertSee('Sin movimientos en este periodo');
        $this->get('/catalogo')->assertOk();
    }

    public function test_confirma_una_solicitud_con_importe_pactado_fecha_y_responsable(): void
    {
        $usuario = $this->autorizar();
        $venta = $this->venta('Pre-reserva', null);
        $this->get('/administracion/ventas/'.$venta->id)->assertOk()->assertSee('Confirmar la venta');
        $this->post('/administracion/ventas/'.$venta->id.'/confirmar', ['monto_total' => '1250.50', 'moneda' => 'MXN', 'aceptar' => 1])
            ->assertRedirect('/administracion/ventas/'.$venta->id)->assertSessionHasNoErrors();
        $venta->refresh();
        $this->assertSame('1250.50', $venta->monto_total);
        $this->assertSame('Confirmada', $venta->estado->nombre_estado);
        $this->assertEquals($usuario->id, $venta->confirmada_por);
        $this->assertSame(now()->toDateTimeString(), $venta->fecha_confirmacion->toDateTimeString());
        $venta->programacion->servicioRegion->servicio->update(['precio_base' => '9999.00']);
        $this->post('/administracion/ventas/'.$venta->id.'/confirmar', ['monto_total' => '1300', 'moneda' => 'MXN', 'aceptar' => 1])->assertSessionHasErrors('monto_total');
        $this->assertSame('1250.50', $venta->fresh()->monto_total);
        $this->travel(1)->hours();
        $this->post('/administracion/ventas/'.$venta->id.'/confirmar', ['monto_total' => '1250.50', 'moneda' => 'MXN', 'aceptar' => 1])->assertSessionHasNoErrors();
        $this->assertSame($venta->fecha_confirmacion->toDateTimeString(), $venta->fresh()->fecha_confirmacion->toDateTimeString());
    }

    #[DataProvider('situacionesDeVenta')]
    public function test_no_confirma_ventas_con_estado_o_disponibilidad_invalida(string $situacion): void
    {
        $this->autorizar();
        $venta = $this->venta($situacion === 'cancelada' ? 'Cancelada' : 'Pre-reserva', null);
        if ($situacion === 'bloqueada') $venta->programacion->update(['estado' => 'bloqueada']);
        if ($situacion === 'pasada') $venta->programacion->update(['fecha_inicio' => now()->subDay()]);
        if ($situacion === 'sobrecupo') $venta->programacion->update(['cupo_maximo' => 1]);
        if ($situacion === 'territorio') $venta->programacion->servicioRegion->region->update(['disponibilidad' => false]);
        $this->post('/administracion/ventas/'.$venta->id.'/confirmar', ['monto_total' => '500', 'moneda' => 'MXN', 'aceptar' => 1])->assertSessionHasErrors('venta');
        $this->assertNull($venta->fresh()->fecha_confirmacion);
    }

    public static function situacionesDeVenta(): array
    {
        return array_combine(['cancelada', 'bloqueada', 'pasada', 'sobrecupo', 'territorio'], [['cancelada'], ['bloqueada'], ['pasada'], ['sobrecupo'], ['territorio']]);
    }

    public function test_registrar_no_confirma_el_cobro_y_reenviar_no_lo_duplica(): void
    {
        $this->autorizar();
        $venta = $this->venta();
        $datos = $this->datosPago($venta, ['fecha_pago' => now()->subHour()->toDateTimeString(), 'referencia_comprobante' => 'BANCO-01']);
        $this->post('/pagos', $datos)->assertRedirect('/pagos/1')->assertSessionHasNoErrors();
        $this->post('/pagos', $datos)->assertRedirect('/pagos/1')->assertSessionHasNoErrors();
        $this->assertDatabaseCount('pagos', 1);
        $this->assertSame('pendiente', Pago::first()->estado);
        $this->get('/pagos/1')->assertOk()->assertSee('Confirmar el cobro');
        $this->get('/administracion/finanzas')->assertViewHas('totalCobros', 0)->assertViewHas('saldoPendiente', 100000);
    }

    public function test_los_abonos_reducen_el_saldo_sin_duplicar_ventas_o_reconfirmaciones(): void
    {
        $this->autorizar();
        $venta = $this->venta();
        foreach (['300.10', '199.90'] as $indice => $monto) {
            $this->post('/pagos', $this->datosPago($venta, ['monto' => $monto]))->assertSessionHasNoErrors();
            $id = Pago::latest('id')->first()->id;
            $confirmacion = ['verificado' => 1, 'fecha_pago' => now()->subDay()->toDateTimeString(), 'referencia_comprobante' => 'ABONO-'.$indice];
            $this->post('/pagos/'.$id.'/confirmar', $confirmacion)->assertSessionHasNoErrors();
            $this->post('/pagos/'.$id.'/confirmar', $confirmacion)->assertSessionHasNoErrors();
        }
        $this->get('/administracion/finanzas')->assertOk()->assertViewHas('numeroVentas', 1)->assertViewHas('totalVentas', 100000)
            ->assertViewHas('numeroCobros', 2)->assertViewHas('totalCobros', 50000)->assertViewHas('saldoPendiente', 50000)
            ->assertViewHas('serie', fn ($serie) => $serie->firstWhere('fecha', '2026-11-16')['cobros'] === 50000);
        $this->get('/administracion/ventas/'.$venta->id)->assertOk()->assertSee('500.00');
    }

    public function test_revalida_el_saldo_al_confirmar_y_no_permite_anular_un_cobro_confirmado(): void
    {
        $this->autorizar();
        $venta = $this->venta();
        foreach (range(1, 2) as $i) $this->post('/pagos', $this->datosPago($venta, ['monto' => '800']));
        $this->post('/pagos/1/confirmar', ['fecha_pago' => now()->toDateTimeString(), 'verificado' => 1, 'referencia_comprobante' => 'COBRO-1'])->assertSessionHasNoErrors();
        $this->post('/pagos/2/confirmar', ['fecha_pago' => now()->toDateTimeString(), 'verificado' => 1, 'referencia_comprobante' => 'COBRO-2'])->assertSessionHasErrors('monto');
        $this->post('/pagos/1/anular')->assertSessionHasErrors('pago');
        $this->assertDatabaseHas('pagos', ['id' => 1, 'estado' => 'confirmado']);
        $this->assertDatabaseHas('pagos', ['id' => 2, 'estado' => 'pendiente']);
    }

    public function test_anular_conserva_el_historial_sin_generar_ingresos(): void
    {
        $this->autorizar();
        $this->post('/pagos', $this->datosPago($this->venta()))->assertSessionHasNoErrors();
        $this->post('/pagos/1/anular')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pagos', ['id' => 1, 'estado' => 'anulado']);
        $this->post('/pagos/1/confirmar', ['fecha_pago' => now()->toDateTimeString(), 'verificado' => 1, 'referencia_comprobante' => 'NO-COBRO'])->assertSessionHasErrors('pago');
        $this->get('/administracion/finanzas')->assertViewHas('totalCobros', 0);
    }

    #[DataProvider('importesInvalidos')]
    public function test_rechaza_importes_que_no_representan_un_abono_valido(string $monto): void
    {
        $this->autorizar();
        $this->post('/pagos', $this->datosPago($this->venta(), ['monto' => $monto]))->assertSessionHasErrors('monto');
        $this->assertDatabaseCount('pagos', 0);
    }

    public static function importesInvalidos(): array
    {
        return [['-1'], ['0'], ['1.001'], ['1e2'], ['1,000.00'], ['10000000000'], ['1000.01']];
    }

    public function test_rechaza_pagos_sin_venta_o_con_otra_moneda(): void
    {
        $this->autorizar();
        $this->post('/pagos', $this->datosPago($this->venta('Pre-reserva', null)))->assertSessionHasErrors('reservacion_id');
        $this->post('/pagos', $this->datosPago($this->venta(), ['moneda' => 'USD']))->assertSessionHasErrors('moneda');
        $this->assertDatabaseCount('pagos', 0);
    }

    public function test_un_enlace_exige_https_y_proveedor_y_no_contacta_servicios_externos(): void
    {
        $this->autorizar();
        $venta = $this->venta();
        $metodo = MetodoPago::create(['metodo' => 'Link de pago']);
        $datos = $this->datosPago($venta, ['metodo_pago_id' => $metodo->id, 'url_pago' => 'http://proveedor.example/pagar', 'proveedor' => 'Proveedor']);
        $this->post('/pagos', $datos)->assertSessionHasErrors('url_pago');
        $this->post('/pagos', [...$datos, 'url_pago' => 'https://proveedor.example/pagar', 'proveedor' => ''])->assertSessionHasErrors('proveedor');
        $this->post('/pagos', [...$datos, 'url_pago' => 'https://proveedor.example/pagar'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pagos', ['estado' => 'pendiente', 'url_pago' => 'https://proveedor.example/pagar']);
    }

    public function test_referencias_repetidas_no_crean_abonos_duplicados(): void
    {
        $this->autorizar();
        $venta = $this->venta();
        $this->post('/pagos', $this->datosPago($venta, ['referencia_comprobante' => 'UNICA']))->assertSessionHasNoErrors();
        $this->post('/pagos', $this->datosPago($venta, ['referencia_comprobante' => 'UNICA']))->assertSessionHasErrors('referencia_comprobante');
        $datos = ['proveedor' => 'Proveedor', 'proveedor_referencia' => 'TRANSACCION-1'];
        $this->post('/pagos', $this->datosPago($venta, $datos))->assertSessionHasNoErrors();
        $this->post('/pagos', $this->datosPago($venta, $datos))->assertSessionHasErrors('referencia_comprobante');
        $this->assertDatabaseCount('pagos', 2);
    }

    public function test_confirmar_cobro_exige_revision_fecha_valida_y_referencia_bancaria(): void
    {
        $this->autorizar();
        $this->post('/pagos', $this->datosPago($this->venta()));
        $this->post('/pagos/1/confirmar', ['fecha_pago' => now()->toDateTimeString()])->assertSessionHasErrors(['verificado', 'referencia_comprobante']);
        $this->post('/pagos/1/confirmar', ['fecha_pago' => now()->addDay()->toDateTimeString(), 'verificado' => 1, 'referencia_comprobante' => 'FUTURO'])->assertSessionHasErrors('fecha_pago');
        $this->assertDatabaseHas('pagos', ['id' => 1, 'estado' => 'pendiente']);
    }

    public function test_fechas_y_monedas_separan_ventas_de_cobros_y_saldos(): void
    {
        $this->autorizar();
        $anterior = $this->venta();
        $anterior->update(['fecha_confirmacion' => '2026-10-10 12:00:00']);
        $this->pagoConfirmado($anterior, '250', '2026-11-16 10:00:00');
        $actual = $this->venta();
        $this->pagoConfirmado($actual, '100', '2026-10-31 23:59:59');
        $extranjera = $this->venta();
        $extranjera->update(['moneda' => 'USD']);
        $this->pagoConfirmado($extranjera, '800', '2026-11-16 10:00:00', 'USD');
        $historica = $this->venta();
        $historica->update(['fecha_confirmacion' => null]);
        $this->get('/administracion/finanzas?desde=2026-11-01&hasta=2026-11-30&moneda=MXN')->assertOk()
            ->assertViewHas('totalVentas', 100000)->assertViewHas('totalCobros', 25000)->assertViewHas('saldoPendiente', 265000)
            ->assertViewHas('sinFecha', 1)->assertSee('no tienen fecha de confirmación');
        $this->get('/administracion/finanzas?moneda=USD')->assertViewHas('totalVentas', 100000)->assertViewHas('totalCobros', 80000)->assertViewHas('saldoPendiente', 20000);
        $this->get('/administracion/finanzas?desde=2026-12-01&hasta=2026-11-01')->assertSessionHasErrors('hasta');
    }

    public function test_el_reporte_muestra_demanda_estados_y_ocupacion_sin_contar_canceladas(): void
    {
        $this->autorizar();
        $venta = $this->venta();
        $this->get('/administracion/finanzas')->assertViewHas('estados', fn ($estados) => $estados['Confirmada'] === 1)
            ->assertViewHas('demanda', fn ($demanda) => $demanda['Camino del bosque'] === 1)
            ->assertViewHas('ocupacion', fn ($ocupacion) => $ocupacion->first()->cupos_ocupados === 2);
        $this->get('/administracion/ventas?buscar='.$venta->folio)->assertOk()->assertSee($venta->folio);
    }

    public function test_migracion_conserva_pagos_previos_y_no_inventa_confirmaciones(): void
    {
        $venta = $this->venta();
        $pago = $this->pagoConfirmado($venta, '300.10', now()->toDateTimeString());
        $migracion = require database_path('migrations/2026_11_17_220000_completar_control_financiero.php');
        $migracion->down();
        $migracion->up();
        $this->assertDatabaseHas('pagos', ['id' => $pago->id, 'monto' => '300.10', 'estado' => 'pendiente', 'fecha_confirmacion' => null]);
        $this->assertDatabaseHas('reservaciones', ['id' => $venta->id, 'monto_total' => '1000.00', 'fecha_confirmacion' => null]);
    }

    public function test_el_catalogo_de_metodos_no_borra_datos_y_puede_prepararse_dos_veces(): void
    {
        MetodoPago::create(['metodo' => 'Convenio existente']);
        $this->seed(MetodoPagoSeeder::class);
        $this->seed(MetodoPagoSeeder::class);
        $this->assertDatabaseCount('metodo_pago', 5);
        $this->assertDatabaseHas('metodo_pago', ['metodo' => 'Convenio existente']);
        $this->assertSame('0.30', Dinero::decimal(Dinero::centavos('0.10') + Dinero::centavos('0.20')));
    }

    private function autorizar(): User
    {
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->actingAs($usuario);

        return $usuario;
    }

    private function venta(string $estado = 'Confirmada', ?string $importe = '1000.00'): Reservacion
    {
        $categoria = DB::table('categorias')->where('nombre_categoria', 'Senderismo')->value('id')
            ?? DB::table('categorias')->insertGetId(['nombre_categoria' => 'Senderismo']);
        $servicio = DB::table('servicios')->insertGetId(['categoria_id' => $categoria, 'nombre_servicio' => 'Camino del bosque', 'precio_base' => '100', 'estado_servicio' => 'activo']);
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Valle de Bravo', 'disponibilidad' => true]);
        $relacion = DB::table('servicio_regiones')->insertGetId(['servicio_id' => $servicio, 'region_id' => $region, 'cupo_maximo' => 20]);
        $programacion = Programacion::create(['servicio_reg_id' => $relacion, 'cupo_maximo' => 20, 'estado' => 'programada', 'fecha_inicio' => now()->addDays(3), 'fecha_fin' => now()->addDays(3)->addHours(3)]);
        $persona = Persona::create(['nombre' => 'Ana', 'apellido_paterno' => 'López']);
        $cliente = Cliente::create(['persona_id' => $persona->id, 'fecha_registro' => today()]);

        return Reservacion::create(['cliente_id' => $cliente->id, 'programacion_id' => $programacion->id,
            'estado_reservacion_id' => EstadoReservacion::firstOrCreate(['nombre_estado' => $estado])->id,
            'numero_personas' => 2, 'fecha_solicitud' => now(), 'monto_total' => $importe, 'moneda' => 'MXN',
            'fecha_confirmacion' => $estado === 'Confirmada' ? now() : null]);
    }

    private function datosPago(Reservacion $venta, array $cambios = []): array
    {
        return [...['reservacion_id' => $venta->id, 'metodo_pago_id' => MetodoPago::firstOrCreate(['metodo' => 'Transferencia bancaria'])->id,
            'monto' => '100', 'moneda' => 'MXN', 'clave_registro' => (string) Str::uuid()], ...$cambios];
    }

    private function pagoConfirmado(Reservacion $venta, string $monto, string $fecha, string $moneda = 'MXN'): Pago
    {
        return Pago::create([...$this->datosPago($venta), 'estado' => 'confirmado', 'monto' => $monto, 'fecha_pago' => $fecha, 'moneda' => $moneda]);
    }
}
