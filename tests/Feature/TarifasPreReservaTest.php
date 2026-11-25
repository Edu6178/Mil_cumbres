<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Programacion;
use App\Models\Reservacion;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\CatalogoWixSeeder;
use Database\Seeders\EscenarioPropuestaSeeder;
use App\Services\Demostracion\RegistroDemostracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TarifasPreReservaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guarda_el_precio_del_servidor_y_permite_acordar_otro_total_sin_perder_el_original(): void
    {
        [$servicio, $datos] = $this->solicitud('780.00', 'persona', 'fijo', 3);
        $this->post('/pre-reservaciones', [...$datos, 'monto_total' => '1.00', 'precio_base' => '1.00'])->assertSessionHasNoErrors();
        $venta = Reservacion::firstOrFail();
        $this->assertSame('2340.00', $venta->monto_total);
        $this->assertSame('780.00', $venta->tarifa_publicada);
        $this->assertSame('2340.00', $venta->importe_publicado);
        $this->assertNull($venta->fecha_confirmacion);
        $servicio->update(['precio_base' => '990.00', 'unidad_cobro' => 'grupo']);
        $this->assertSame('780.00', $venta->fresh()->tarifa_catalogo['precio']);
        $this->assertSame('2340.00', $venta->fresh()->tarifa_catalogo['importe']);
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->actingAs($usuario)->get('/administracion/ventas/'.$venta->id)->assertOk()
            ->assertSee('value="2340.00"', false)->assertSee('Precio al solicitar')->assertSee('2,340.00');
        $this->get('/administracion/ventas')->assertOk()->assertSee('2,340.00')->assertSee('Pendiente de confirmar');
        $this->get('/administracion/finanzas')->assertViewHas('totalVentas', 0);
        $this->post('/administracion/ventas/'.$venta->id.'/confirmar', ['monto_total' => '2100.00', 'moneda' => 'MXN', 'aceptar' => 1])->assertSessionHasNoErrors();
        $this->assertSame('2100.00', $venta->fresh()->monto_total);
        $this->assertSame('2340.00', $venta->fresh()->importe_publicado);
        $this->get('/administracion/finanzas')->assertViewHas('totalVentas', 210000)->assertViewHas('totalCobros', 0);
        $venta->refresh()->load(['cliente.persona', 'programacion.servicioRegion.servicio', 'estado', 'acompanantes']);
        $html = view('pdf.pre-reservacion', ['solicitud' => $venta, 'simbolo' => ''])->render();
        $this->assertStringContainsString('$2,340.00', $html);
        $this->assertStringContainsString('$2,100.00', $html);
    }

    #[DataProvider('tarifas')]
    public function test_respeta_la_unidad_los_precios_desde_y_las_cotizaciones_sin_importe(?string $precio, ?string $unidad, string $tipo, ?string $total): void
    {
        [, $datos] = $this->solicitud($precio, $unidad, $tipo, 3);
        $this->post('/pre-reservaciones', $datos)->assertSessionHasNoErrors();
        $venta = Reservacion::firstOrFail();
        $this->assertSame($total, $venta->monto_total);
        $this->assertSame($precio, $venta->tarifa_publicada);
        $this->assertSame($tipo, $venta->tipo_precio);
    }

    public static function tarifas(): array
    {
        return [
            'centavos exactos' => ['0.29', 'persona', 'fijo', '0.87'],
            'importe del grupo' => ['7500.00', 'grupo', 'fijo', '7500.00'],
            'servicio completo' => ['10000.00', 'servicio', 'desde', '10000.00'],
            'desde por persona' => ['500.00', 'persona', 'desde', '1500.00'],
            'desde sin unidad definida' => ['1300.00', null, 'desde', null],
            'cotización personalizada' => [null, null, 'fijo', null],
        ];
    }

    public function test_rechaza_un_total_que_no_cabe_sin_crear_un_grupo_parcial(): void
    {
        [, $datos] = $this->solicitud('9999999999.99', 'persona', 'fijo', 3);
        $this->post('/pre-reservaciones', $datos)->assertSessionHasErrors('numero_personas');
        $this->assertDatabaseCount('reservaciones', 0);
        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('personas', 0);
    }

    public function test_los_servicios_de_wix_conservan_desde_y_el_escenario_no_se_duplica(): void
    {
        $ruta = storage_path('framework/testing/wix-'.bin2hex(random_bytes(8)).'.json');
        config(['demostracion.registro' => $ruta]);
        try {
            $this->seed(CatalogoWixSeeder::class);
            $consultoria = Servicio::where('nombre_servicio', 'Consultoría turística')->firstOrFail();
            $this->assertSame('10000.00', $consultoria->precio_base);
            $this->assertSame('desde', $consultoria->tipo_precio);
            $this->assertNull($consultoria->unidad_cobro);
            $consultoria->update(['precio_base' => '12000.00']);
            $this->seed(CatalogoWixSeeder::class);
            $this->assertSame('12000.00', $consultoria->fresh()->precio_base);
            $this->assertDatabaseCount('servicios', 3);
            $this->seed(EscenarioPropuestaSeeder::class);
            $this->assertDatabaseCount('programaciones', 6);
            $this->assertDatabaseCount('reservaciones', 5);
            $this->assertDatabaseCount('pagos', 2);
            $this->seed(EscenarioPropuestaSeeder::class);
            $this->assertDatabaseCount('reservaciones', 5);
            $this->assertDatabaseCount('pagos', 2);
        } finally {
            File::delete($ruta.'.propuesta.json');
        }
    }

    private function solicitud(?string $precio, ?string $unidad, string $tipo, int $personas): array
    {
        $categoria = Categoria::create(['nombre_categoria' => 'Experiencias turísticas']);
        $servicio = Servicio::create(['categoria_id' => $categoria->id, 'nombre_servicio' => 'Miradores al amanecer', 'precio_base' => $precio,
            'estado_servicio' => 'activo', 'unidad_cobro' => $unidad, 'tipo_precio' => $tipo, 'moneda' => 'MXN']);
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Mirador de prueba', 'disponibilidad' => true]);
        $relacion = DB::table('servicio_regiones')->insertGetId(['servicio_id' => $servicio->id, 'region_id' => $region, 'cupo_maximo' => 12]);
        $programacion = Programacion::create(['servicio_reg_id' => $relacion, 'fecha_inicio' => now()->addDays(3), 'fecha_fin' => now()->addDays(3)->addHours(4), 'cupo_maximo' => 12, 'estado' => 'programada']);
        return [$servicio, ['programacion_id' => $programacion->id, 'numero_personas' => $personas, 'nombre' => 'Visitante de prueba',
            'telefono' => '0000000000', 'edad' => 30, 'acompanantes' => array_fill(0, $personas - 1, ['nombre' => 'Acompañante', 'edad' => 28])]];
    }
}
