<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\User;
use App\Services\Demostracion\RegistroDemostracion;
use Database\Seeders\CatalogoClienteSeeder;
use Database\Seeders\DatosDemostracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CatalogoClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_carga_las_lineas_sin_inventar_tarifas_o_programaciones_y_conserva_ediciones(): void
    {
        $this->seed(CatalogoClienteSeeder::class);
        $this->assertDatabaseCount('categorias', 6);
        $this->assertDatabaseCount('servicios', 10);
        $this->assertDatabaseCount('programaciones', 0);
        $this->assertSame(10, Servicio::whereNull('precio_base')->whereNull('duracion_estimada')->whereNull('itinerario')->count());
        $senderismo = Servicio::where('nombre_servicio', 'Senderismo')->firstOrFail();
        $this->assertSame('Experiencias turísticas', $senderismo->categoria->nombre_categoria);
        $senderismo->update(['precio_base' => '875.00', 'descripcion' => 'Texto revisado por la empresa']);
        $this->seed(CatalogoClienteSeeder::class);
        $this->assertDatabaseCount('servicios', 10);
        $this->assertSame('875.00', $senderismo->fresh()->precio_base);
        $this->assertSame('Texto revisado por la empresa', $senderismo->fresh()->descripcion);
    }

    public function test_la_carga_no_se_ejecuta_en_produccion(): void
    {
        $this->app->instance('env', 'production');
        try {
            app(CatalogoClienteSeeder::class)->run();
            $this->fail('No debe cargar el catálogo en producción.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('solo se carga en local', $error->getMessage());
        } finally {
            $this->app->instance('env', 'testing');
        }
        $this->assertDatabaseCount('servicios', 0);
        $this->assertDatabaseCount('categorias', 0);
    }

    public function test_administracion_guarda_un_precio_pendiente_y_no_lo_muestra_como_cero(): void
    {
        $this->withoutVite();
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $categoria = Categoria::create(['nombre_categoria' => 'Ecovoluntariado']);
        $this->actingAs($usuario)->post('/administracion/servicios', [
            'nombre_servicio' => 'Ecovoluntariado', 'categoria_id' => $categoria->id,
            'estado_servicio' => 'activo', 'precio_base' => '',
        ])->assertSessionHasNoErrors()->assertRedirect('/administracion/servicios');
        $this->assertNull(Servicio::firstOrFail()->precio_base);
        $this->get('/administracion/servicios')->assertOk()->assertSee('Por confirmar')->assertDontSee('$0.00');
        $this->get('/administracion')->assertOk()->assertSee('Por confirmar')->assertDontSee('$0.00');
    }

    public function test_el_catalogo_publico_filtra_las_lineas_y_formacion_sin_cambiar_las_rutas_existentes(): void
    {
        $this->withoutVite();
        $ruta = storage_path('framework/testing/catalogo-'.bin2hex(random_bytes(8)).'.json');
        config(['demostracion.registro' => $ruta]);
        try {
            $this->seed(DatosDemostracionSeeder::class);
            $this->seed(CatalogoClienteSeeder::class);
            $this->assertDatabaseCount('servicios', 15);
            $this->assertDatabaseCount('programaciones', 10);
            $this->get('/catalogo?linea=ecovoluntariado')->assertOk()
                ->assertSee('data-total-servicios>1</strong>', false)->assertSee('Precio por confirmar');
            $this->get('/catalogo?linea=talleres-y-cursos')->assertOk()
                ->assertSee('data-total-servicios>1</strong>', false);
            $servicio = Servicio::where('nombre_servicio', 'Avistamiento de aves')->firstOrFail();
            $this->get('/experiencias/'.$servicio->id)->assertOk()->assertSee('Por confirmar')
                ->assertSee('Aún no hay fechas publicadas')->assertDontSee('$0.00');
            $id = app(RegistroDemostracion::class)->leer()['ids']['servicios']['pinos'];
            $this->get('/experiencias/'.$id)->assertOk()->assertSee('MXN por persona');
        } finally {
            File::delete($ruta);
        }
    }
}
