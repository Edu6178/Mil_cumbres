<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoReservacion;
use App\Models\Persona;
use App\Models\Programacion;
use App\Models\Reservacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReservacionesCalendarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_la_portada_y_el_calendario_muestran_cupos_con_reservaciones_reales(): void
    {
        $programacion = $this->programacion();
        $this->reservacion($programacion, 2, 'Pre-reserva');
        $this->reservacion($programacion, 3, 'Confirmada');
        $this->reservacion($programacion, 7, 'Cancelada');

        $this->assertSame(5, $programacion->fresh()->cupos_ocupados);
        $this->assertSame(15, $programacion->fresh()->load('reservaciones.estado')->cupos_disponibles);
        $this->get('/')->assertOk()->assertSee('15 cupos');

        $this->autorizar();
        $this->get('/administracion/calendario')->assertOk()->assertSee('Recorrido de prueba')
            ->assertSee('(5 ocupados)');
    }

    public function test_registra_un_grupo_sin_correo_y_actualiza_los_cupos(): void
    {
        $programacion = $this->programacion();
        $datos = [...$this->datosSolicitud($programacion), 'numero_personas' => 3,
            'acompanantes' => [$this->acompanante('Ana'), $this->acompanante('Luis')]];

        $this->from('/')->post('/pre-reservaciones', $datos)
            ->assertRedirect('/#pre-reservacion')->assertSessionHas('folio');

        $this->assertDatabaseCount('reservaciones', 1);
        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('personas', 3);
        $this->assertDatabaseCount('asigna_reservacion_invitados', 2);
        $this->assertDatabaseHas('personas', ['nombre' => 'Responsable', 'correo' => null]);
        $this->assertSame(17, $programacion->fresh()->cupos_disponibles);
        $this->get('/')->assertOk()->assertSee('17 cupos');
    }

    public function test_el_correo_opcional_se_valida_si_se_proporciona(): void
    {
        $programacion = $this->programacion();
        $this->from('/')->post('/pre-reservaciones', [
            ...$this->datosSolicitud($programacion), 'correo' => 'correo-invalido',
        ])->assertSessionHasErrors('correo');
        $this->assertDatabaseCount('reservaciones', 0);
        $this->assertDatabaseCount('personas', 0);
    }

    public function test_rechaza_sobreventa_sin_crear_clientes_ni_participantes_parciales(): void
    {
        $programacion = $this->programacion();
        $this->reservacion($programacion, 19, 'Confirmada');
        $this->from('/')->post('/pre-reservaciones', [
            ...$this->datosSolicitud($programacion), 'numero_personas' => 2,
            'acompanantes' => [$this->acompanante('Ana')],
        ])->assertSessionHasErrors('numero_personas');

        $this->assertDatabaseCount('reservaciones', 1);
        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('personas', 1);
        $this->assertDatabaseCount('asigna_reservacion_invitados', 0);
    }

    #[DataProvider('situacionesNoDisponibles')]
    public function test_oculta_y_rechaza_programaciones_no_disponibles(string $situacion): void
    {
        $programacion = $this->programacion();
        if ($situacion === 'pasada') {
            $programacion->update(['fecha_inicio' => now()->subDay(), 'fecha_fin' => now()->subHours(20)]);
        } elseif ($situacion === 'bloqueada') {
            $programacion->update(['estado' => 'bloqueada']);
        } elseif ($situacion === 'territorio') {
            $programacion->servicioRegion->region->update(['disponibilidad' => false]);
        } else {
            $programacion->servicioRegion->servicio->update(['estado_servicio' => 'oculto']);
        }

        $this->get('/')->assertOk()->assertViewHas('programaciones', fn ($fechas) => $fechas->isEmpty());
        $this->from('/')->post('/pre-reservaciones', $this->datosSolicitud($programacion))
            ->assertSessionHasErrors('programacion_id');
        $this->assertDatabaseCount('reservaciones', 0);
        $this->assertDatabaseCount('personas', 0);
    }

    public static function situacionesNoDisponibles(): array
    {
        return [['pasada'], ['bloqueada'], ['territorio'], ['oculta']];
    }

    #[DataProvider('cuposInvalidos')]
    public function test_editar_cupo_respeta_reservaciones_y_limite_territorial(int $cupo): void
    {
        $this->autorizar();
        $programacion = $this->programacion();
        $this->reservacion($programacion, 5, 'Confirmada');

        $this->put('/administracion/calendario/'.$programacion->id, $this->datosCalendario($programacion, $cupo))
            ->assertSessionHasErrors('cupo_maximo');
        $this->assertSame(20, $programacion->fresh()->cupo_maximo);
        $this->assertDatabaseCount('reservaciones', 1);
    }

    public static function cuposInvalidos(): array
    {
        return ['menor que lo reservado' => [1], 'mayor que el territorio' => [99]];
    }

    public function test_permite_editar_un_cupo_valido_sin_contar_reservaciones_canceladas(): void
    {
        $this->autorizar();
        $programacion = $this->programacion();
        $this->reservacion($programacion, 5, 'Confirmada');
        $this->reservacion($programacion, 10, 'Cancelada');
        $this->put('/administracion/calendario/'.$programacion->id, $this->datosCalendario($programacion, 10))
            ->assertRedirect('/administracion/calendario')->assertSessionHasNoErrors();
        $this->assertSame(10, $programacion->fresh()->cupo_maximo);
        $this->assertSame(5, $programacion->fresh()->cupos_disponibles);
    }

    public function test_recupera_acompanantes_escapados_al_volver_de_una_validacion(): void
    {
        $programacion = $this->programacion();
        $respuesta = $this->withSession(['_old_input' => [
            'programacion_id' => $programacion->id, 'numero_personas' => 2,
            'acompanantes' => [$this->acompanante('Ana <script>prueba</script>')],
        ]])->get('/')->assertOk();

        $respuesta->assertSee('name="acompanantes[0][nombre]"', false)
            ->assertSee('value="Ana &lt;script&gt;prueba&lt;/script&gt;"', false)
            ->assertDontSee('<script>prueba</script>', false);
        $documento = new \DOMDocument();
        @$documento->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent());
        $this->assertSame(0, (new \DOMXPath($documento))->query('//form[contains(@class,"reserva-formulario")]//input[@name="correo"]')->length);
    }

    private function programacion(): Programacion
    {
        $categoria = DB::table('categorias')->insertGetId(['nombre_categoria' => 'Categoría de prueba']);
        $servicio = DB::table('servicios')->insertGetId([
            'categoria_id' => $categoria, 'nombre_servicio' => 'Recorrido de prueba',
            'precio_base' => '100.00', 'estado_servicio' => 'activo',
        ]);
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Territorio de prueba']);
        $relacion = DB::table('servicio_regiones')->insertGetId([
            'servicio_id' => $servicio, 'region_id' => $region, 'cupo_maximo' => 20,
        ]);

        return Programacion::create([
            'servicio_reg_id' => $relacion, 'cupo_maximo' => 20, 'estado' => 'programada',
            'fecha_inicio' => now()->addDays(3), 'fecha_fin' => now()->addDays(3)->addHours(3),
        ]);
    }

    private function reservacion(Programacion $programacion, int $personas, string $estado): void
    {
        // Estos registros existen únicamente dentro de la base aislada de cada prueba.
        $persona = Persona::create(['nombre' => 'Cliente', 'apellido_paterno' => 'Prueba']);
        $cliente = Cliente::create(['persona_id' => $persona->id, 'fecha_registro' => today()]);
        $estado = EstadoReservacion::firstOrCreate(['nombre_estado' => $estado]);
        Reservacion::create([
            'cliente_id' => $cliente->id, 'programacion_id' => $programacion->id,
            'estado_reservacion_id' => $estado->id, 'numero_personas' => $personas, 'fecha_solicitud' => now(),
        ]);
    }

    private function datosSolicitud(Programacion $programacion): array
    {
        return ['programacion_id' => $programacion->id, 'numero_personas' => 1,
            'nombre' => 'Responsable', 'apellido_paterno' => 'Prueba',
            'telefono' => '5512345678', 'fecha_nacimiento' => '2000-01-01'];
    }

    private function acompanante(string $nombre): array
    {
        return ['nombre' => $nombre, 'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '2000-01-01'];
    }

    private function datosCalendario(Programacion $programacion, int $cupo): array
    {
        return ['servicio_reg_id' => $programacion->servicio_reg_id, 'cupo_maximo' => $cupo,
            'fecha_inicio' => $programacion->fecha_inicio->format('Y-m-d H:i:s'),
            'fecha_fin' => $programacion->fecha_fin->format('Y-m-d H:i:s'), 'estado' => 'programada'];
    }

    private function autorizar(): void
    {
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->actingAs($usuario);
    }
}
