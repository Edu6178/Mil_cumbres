<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CuentaPruebaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CuentaPruebaSeederTest extends TestCase
{
    use RefreshDatabase;

    private ?string $archivoEntorno = null;

    protected function tearDown(): void
    {
        // Se elimina solo el archivo temporal de esta prueba, nunca el .env del proyecto.
        if ($this->archivoEntorno !== null && is_file($this->archivoEntorno)) {
            unlink($this->archivoEntorno);
        }

        parent::tearDown();
    }

    public function test_migrate_con_seed_prepara_una_cuenta_que_puede_entrar_al_panel(): void
    {
        $this->artisan('migrate', ['--seed' => true, '--force' => true])->assertExitCode(0);

        $usuario = User::where('email', 'admin@milcumbres.test')->firstOrFail();
        $this->assertTrue(Hash::check('MilCumbres2026!', $usuario->password));
        $this->assertTrue(Gate::forUser($usuario)->allows('administrar'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('servicios', 0);
        $this->assertDatabaseCount('clientes', 0);
        $this->post('/acceso', ['correo' => 'admin@milcumbres.test', 'contrasena' => 'MilCumbres2026!'])
            ->assertRedirect('/administracion');
        $this->get('/administracion')->assertOk();
    }

    public function test_en_desarrollo_autoriza_el_id_real_y_conserva_los_demas_datos_de_env(): void
    {
        $this->archivoEntorno = tempnam(sys_get_temp_dir(), 'milcumbres-cuenta-');
        $contenido = "APP_ENV=local\r\nDB_PASSWORD=clave-local-conservar\r\nADMIN_USUARIOS=\"9\"\r\n";
        file_put_contents($this->archivoEntorno, $contenido);
        $this->app->useEnvironmentPath(dirname($this->archivoEntorno));
        $this->app->loadEnvironmentFrom(basename($this->archivoEntorno));
        $this->app['env'] = 'local';

        // Una cuenta previa demuestra que el administrador no tiene necesariamente el ID 1.
        User::factory()->create();
        $this->seed();
        $usuario = User::where('email', 'admin@milcumbres.test')->firstOrFail();
        $this->assertNotSame(1, $usuario->id);
        $esperado = str_replace('ADMIN_USUARIOS="9"', 'ADMIN_USUARIOS=9,'.$usuario->id, $contenido);
        $this->assertSame($esperado, file_get_contents($this->archivoEntorno));
        $this->assertTrue(Gate::forUser($usuario)->allows('administrar'));
        $this->assertContains(9, config('administracion.usuarios'));
        $this->seed();
        $this->assertSame($esperado, file_get_contents($this->archivoEntorno));
        $this->assertDatabaseCount('users', 2);
    }

    public function test_repetir_el_seeder_no_duplica_ni_restablece_la_cuenta(): void
    {
        $this->seed();
        $usuario = User::where('email', 'admin@milcumbres.test')->firstOrFail();
        $usuario->update(['name' => 'Nombre de revisión', 'password' => 'ClaveCambiada123']);
        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $this->assertSame('Nombre de revisión', $usuario->fresh()->name);
        $this->assertTrue(Hash::check('ClaveCambiada123', $usuario->fresh()->password));
        $this->assertTrue(Gate::forUser($usuario->fresh())->allows('administrar'));
    }

    public function test_en_produccion_no_crea_la_cuenta_ni_agrega_permisos(): void
    {
        $this->app['env'] = 'production';
        config(['administracion.usuarios' => [42]]);
        app(CuentaPruebaSeeder::class)->run();

        $this->assertDatabaseCount('users', 0);
        $this->assertSame([42], config('administracion.usuarios'));
    }
}
