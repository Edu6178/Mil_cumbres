<?php

namespace Tests\Feature;

use App\Models\Programacion;
use App\Models\Servicio;
use App\Models\User;
use App\Services\Demostracion\RegistroDemostracion;
use Database\Seeders\DatosDemostracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatosDemostracionTest extends TestCase
{
    use RefreshDatabase;

    private string $registro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->registro = storage_path('framework/testing/demostracion-'.bin2hex(random_bytes(8)).'.json');
        config(['demostracion.registro' => $this->registro]);
    }

    protected function tearDown(): void
    {
        File::delete($this->registro);
        parent::tearDown();
    }

    public function test_carga_un_escenario_coherente_sin_tocar_la_cuenta_existente_y_sin_duplicar(): void
    {
        $usuario = User::factory()->create();
        $this->seed(DatosDemostracionSeeder::class);
        $this->assertDatabaseCount('servicios', 5);
        $this->assertDatabaseCount('personal', 3);
        $this->assertDatabaseCount('programaciones', 10);
        $this->assertDatabaseCount('clientes', 8);
        $this->assertDatabaseCount('reservaciones', 12);
        $this->assertDatabaseCount('pagos', 5);
        $this->assertDatabaseCount('users', 1);
        foreach (Programacion::all() as $programacion) {
            $this->assertLessThanOrEqual($programacion->cupo_maximo, $programacion->cupos_ocupados);
        }
        $servicio = Servicio::first();
        $servicio->update(['precio_base' => '500.00']);
        $this->seed(DatosDemostracionSeeder::class);
        $this->assertDatabaseCount('reservaciones', 12);
        $this->assertSame('500.00', $servicio->fresh()->precio_base);
        $this->assertTrue($usuario->fresh()->is($usuario));
    }

    public function test_publica_catalogo_detalles_guias_y_comunidades_sin_datos_privados(): void
    {
        $this->seed(DatosDemostracionSeeder::class);
        $ids = app(RegistroDemostracion::class)->leer()['ids'];
        $this->get('/catalogo')->assertOk()->assertSee('Sendero del bosque de pinos');
        $this->get('/experiencias/'.$ids['servicios']['pinos'])->assertOk()->assertSee('MXN por persona')->assertSee('lugares disponibles');
        $this->get('/guias')->assertOk()->assertSee('María Elena Castillo Rojas');
        $this->get('/guias/'.$ids['personal']['maria'])->assertOk()->assertSee('Interpretación ambiental')
            ->assertDontSee('maria@demostracion.milcumbres.test');
        $this->get('/comunidades')->assertOk()->assertSee('El Encinal');
        $this->get('/creditos-fotograficos')->assertOk()->assertSee('Annie Spratt')->assertSee('Kampus Production');
        $this->get('/?programacion='.$ids['programaciones']['pinos-0'])->assertOk()
            ->assertSee('value="'.$ids['programaciones']['pinos-0'].'"', false);
        Servicio::findOrFail($ids['servicios']['pinos'])->update(['estado_servicio' => 'oculto']);
        $this->get('/experiencias/'.$ids['servicios']['pinos'])->assertNotFound();
        $this->get('/catalogo')->assertDontSee('Sendero del bosque de pinos');
    }

    public function test_no_publica_personal_ajeno_al_lote_y_actualiza_cupos_al_reservar(): void
    {
        $this->seed(DatosDemostracionSeeder::class);
        $persona = DB::table('personas')->insertGetId(['nombre' => 'PERSONA PRIVADA', 'apellido_paterno' => 'NO PUBLICAR']);
        $personal = DB::table('personal')->insertGetId(['persona_id' => $persona, 'tipo_rol' => 'guia', 'estado_laboral' => 'activo']);
        $this->get('/guias')->assertDontSee('PERSONA PRIVADA');
        $this->get('/guias/'.$personal)->assertNotFound();
        $ids = app(RegistroDemostracion::class)->leer()['ids'];
        $programacion = Programacion::findOrFail($ids['programaciones']['pinos-0']);
        $antes = $programacion->cupos_disponibles;
        $this->from('/')->post('/pre-reservaciones', [
            'programacion_id' => $programacion->id, 'numero_personas' => 1, 'nombre' => 'Visitante',
            'apellido_paterno' => 'De prueba', 'telefono' => '0000000199', 'fecha_nacimiento' => '1995-05-18',
        ])->assertRedirect('/#pre-reservacion')->assertSessionHas('folio');
        $this->assertSame($antes - 1, $programacion->fresh()->cupos_disponibles);
        $this->assertDatabaseCount('reservaciones', 13);
    }

    public function test_el_retiro_es_solo_del_lote_y_conserva_las_cuentas(): void
    {
        User::factory()->create();
        $this->seed(DatosDemostracionSeeder::class);
        $this->artisan('demostracion:limpiar')->assertSuccessful();
        $this->assertDatabaseCount('reservaciones', 12);
        $this->artisan('demostracion:limpiar --confirmar')->assertSuccessful();
        $this->assertDatabaseCount('servicios', 0);
        $this->assertDatabaseCount('personas', 0);
        $this->assertDatabaseCount('users', 1);
        $this->assertFileDoesNotExist($this->registro);
    }

    public function test_el_retiro_se_detiene_si_hay_datos_nuevos_relacionados(): void
    {
        $this->seed(DatosDemostracionSeeder::class);
        $categoria = Servicio::first()->categoria_id;
        DB::table('servicios')->insert(['categoria_id' => $categoria, 'nombre_servicio' => 'Servicio creado después', 'precio_base' => '100.00']);
        $this->artisan('demostracion:limpiar --confirmar')->assertFailed();
        $this->assertDatabaseCount('servicios', 6);
        $this->assertDatabaseCount('reservaciones', 12);
        $this->assertFileExists($this->registro);
    }

    public function test_rechaza_la_carga_en_produccion(): void
    {
        $this->app->instance('env', 'production');
        $this->expectException(\RuntimeException::class);
        $this->app->make(DatosDemostracionSeeder::class)->run();
    }

    public function test_publica_el_escenario_en_produccion_solo_con_activacion_expresa(): void
    {
        $this->app->instance('env', 'production');
        config(['demostracion.habilitada' => true, 'app.debug' => false]);
        $this->artisan('db:seed', ['--class' => DatosDemostracionSeeder::class, '--force' => true])->assertSuccessful();
        $ids = app(RegistroDemostracion::class)->leer()['ids'];

        $this->get('/catalogo')->assertOk()->assertSee('Sendero del bosque de pinos')
            ->assertSee('imagenes/demostracion/bosque-pinos.jpg');
        $this->get('/guias/'.$ids['personal']['maria'])->assertOk()->assertSee('María Elena Castillo Rojas')
            ->assertDontSee('maria@demostracion.milcumbres.test');
        $this->get('/comunidades')->assertOk()->assertSee('El Encinal');
        $this->assertTrue(app()->environment('production'));
        $this->assertFalse(config('app.debug'));
        $this->assertDatabaseCount('users', 0);

        $this->artisan('db:seed', ['--class' => DatosDemostracionSeeder::class, '--force' => true])->assertSuccessful();
        $this->assertDatabaseCount('reservaciones', 12);
    }

    public function test_desactivar_el_escenario_oculta_los_perfiles_sin_borrar_sus_datos(): void
    {
        $this->app->instance('env', 'production');
        config(['demostracion.habilitada' => true]);
        $this->artisan('db:seed', ['--class' => DatosDemostracionSeeder::class, '--force' => true])->assertSuccessful();
        $id = app(RegistroDemostracion::class)->leer()['ids']['personal']['maria'];

        config(['demostracion.habilitada' => false]);
        $this->get('/guias')->assertOk()->assertDontSee('María Elena Castillo Rojas');
        $this->get('/guias/'.$id)->assertNotFound();
        $this->assertFalse(app(RegistroDemostracion::class)->activo());
        $this->assertDatabaseCount('personal', 3);
        $this->assertDatabaseCount('reservaciones', 12);
        $this->assertFileExists($this->registro);
    }

    public function test_la_demostracion_en_produccion_no_habilita_la_cuenta_compartida_ni_el_panel(): void
    {
        $this->app->instance('env', 'production');
        config(['demostracion.habilitada' => true]);
        $this->artisan('db:seed', ['--class' => \Database\Seeders\CuentaPruebaSeeder::class, '--force' => true])->assertSuccessful();
        $this->assertDatabaseCount('users', 0);

        $usuario = User::factory()->create();
        $this->actingAs($usuario)->get('/administracion')->assertForbidden();
        $this->assertSame([], config('administracion.usuarios'));
    }
}
