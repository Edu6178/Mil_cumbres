<?php

namespace Tests\Feature;

use App\Models\Programacion;
use App\Models\Reservacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RequisitosClienteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_recibe_edades_sin_correo_ni_nacimientos_y_las_conserva_en_el_acuse(): void
    {
        $datos = $this->datos();
        $this->post('/pre-reservaciones', $datos)->assertRedirect('/#pre-reservacion')->assertSessionHasNoErrors();
        $venta = Reservacion::with(['cliente.persona', 'acompanantes', 'programacion', 'estado'])->firstOrFail();
        $this->assertSame(32, $venta->edad_responsable);
        $this->assertSame(0, (int) $venta->acompanantes->first()->pivot->edad);
        $this->assertNull($venta->cliente->persona->fecha_nacimiento);
        $this->assertNull($venta->cliente->persona->correo);
        $this->assertNull($venta->acompanantes->first()->fecha_nacimiento);
        $this->assertSame('Valeria Castillo Soto', $venta->cliente->persona->nombre);
        $html = view('pdf.pre-reservacion', ['solicitud' => $venta, 'simbolo' => ''])->render();
        $this->assertStringContainsString('32 años', $html);
        $this->assertStringContainsString('0 años', $html);
        $this->assertStringNotContainsString('Fecha de nacimiento', $html);
        $this->get(session('ultima_pre_reserva.pdf'))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->actingAs($usuario)->get('/administracion/ventas/'.$venta->id)->assertOk()->assertSee('32 años')->assertSee('0 años');
    }

    #[DataProvider('edadesInvalidas')]
    public function test_rechaza_edades_invalidas_sin_registros_parciales(mixed $edad): void
    {
        $datos = $this->datos();
        $datos['acompanantes'][0]['edad'] = $edad;
        $this->from('/')->post('/pre-reservaciones', $datos)->assertSessionHasErrors('acompanantes.0.edad');
        $this->assertDatabaseCount('reservaciones', 0);
        $this->assertDatabaseCount('personas', 0);
    }

    public static function edadesInvalidas(): array
    {
        return [[-1], [121], ['doce'], [null], [2.5]];
    }

    public function test_el_calendario_muestra_bloqueos_y_cupos_completos_sin_habilitar_su_reserva(): void
    {
        $datos = $this->datos();
        $fecha = Programacion::findOrFail($datos['programacion_id']);
        $fecha->update(['estado' => 'bloqueada']);
        $this->get('/')->assertOk()->assertSee('Fecha bloqueada')
            ->assertViewHas('programaciones', fn ($fechas) => $fechas->isEmpty())
            ->assertViewHas('fechasCalendario', fn ($fechas) => $fechas->first()['estado'] === 'bloqueada');
        $this->from('/')->post('/pre-reservaciones', $datos)->assertSessionHasErrors('programacion_id');
        $fecha->update(['estado' => 'programada']);
        $this->post('/pre-reservaciones', $datos)->assertSessionHasNoErrors();
        $fecha->update(['cupo_maximo' => 2]);
        $this->get('/')->assertOk()->assertSee('Cupo completo')
            ->assertViewHas('programaciones', fn ($fechas) => $fechas->isEmpty());
    }

    public function test_el_formulario_y_el_contenido_respetan_los_audios(): void
    {
        $this->datos();
        $html = $this->get('/')->assertOk()->assertSee('Nombre completo')->assertSee('Tu edad al realizar la visita')
            ->assertSee('Pareja')->assertSee('Familia con niños')->getContent();
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(0, $xpath->query('//form[contains(@class,"reserva-formulario")]//input[@name="correo" or @name="fecha_nacimiento"]')->length);
        $this->get('/consultoria')->assertOk()->assertSee('Contacto para empresas')
            ->assertSee('https://www.facebook.com/milcumbresturismoalternativo')->assertDontSee('wa.me/');
        $this->get('/turismo-regenerativo')->assertOk()->assertSee('La relación conmigo mismo')
            ->assertSee('La relación con la naturaleza')->assertSee('La relación con los demás');
        $this->get('/guias')->assertOk()->assertSee('Mil Cumbres asigna');
    }

    private function datos(): array
    {
        $categoria = DB::table('categorias')->insertGetId(['nombre_categoria' => 'Experiencias turísticas']);
        $servicio = DB::table('servicios')->insertGetId(['categoria_id' => $categoria, 'nombre_servicio' => 'Recorrido del bosque', 'precio_base' => '450', 'estado_servicio' => 'activo']);
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Bosque de prueba']);
        $relacion = DB::table('servicio_regiones')->insertGetId(['servicio_id' => $servicio, 'region_id' => $region, 'cupo_maximo' => 10]);
        $fecha = Programacion::create(['servicio_reg_id' => $relacion, 'fecha_inicio' => now()->addDays(3), 'fecha_fin' => now()->addDays(3)->addHours(4), 'cupo_maximo' => 10, 'estado' => 'programada']);

        return ['programacion_id' => $fecha->id, 'numero_personas' => 2, 'nombre' => 'Valeria Castillo Soto', 'telefono' => '5512345678',
            'edad' => 32, 'tipo_grupo' => 'Familia con niños', 'proposito_visita' => 'Convivir y conocer el bosque',
            'acompanantes' => [['nombre' => 'Emilio Castillo Soto', 'edad' => 0]]];
    }
}
