<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_formularios_se_muestran_en_espanol(): void
    {
        $this->get('/acceso')->assertOk()->assertSee('Iniciar sesión')->assertSee('Correo electrónico');
        $this->get('/registro')->assertOk()->assertSee('Crear cuenta')->assertSee('Confirmar contraseña');
    }

    public function test_registra_una_cuenta_personal_con_la_contrasena_protegida(): void
    {
        $this->post('/registro', [
            ...$this->datosRegistro(),
            'correo' => '  MARIA@EJEMPLO.COM  ',
            'role' => 'admin',
        ])->assertRedirect('/mi-cuenta');

        $usuario = User::firstOrFail();
        $this->assertAuthenticatedAs($usuario);
        $this->assertSame('maria@ejemplo.com', $usuario->email);
        $this->assertNotSame('Bosque123', $usuario->password);
        $this->assertTrue(Hash::check('Bosque123', $usuario->password));
        $this->assertNull($usuario->getAttribute('role'));
        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('personal', 0);
        $this->get('/mi-cuenta')->assertOk()->assertSee('María Pérez')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }

    #[DataProvider('registrosInvalidos')]
    public function test_rechaza_registros_invalidos(array $cambios, string $campo): void
    {
        $this->from('/registro')->post('/registro', [...$this->datosRegistro(), ...$cambios])
            ->assertRedirect('/registro')->assertSessionHasErrors($campo);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        // Las contraseñas tampoco deben quedar en los datos temporales del formulario.
        $this->assertNull(session()->getOldInput('contrasena'));
        $this->assertNull(session()->getOldInput('contrasena_confirmation'));
    }

    public static function registrosInvalidos(): array
    {
        return [
            'nombre vacío' => [['nombre' => ''], 'nombre'],
            'correo inválido' => [['correo' => 'correo-invalido'], 'correo'],
            'contraseña corta' => [['contrasena' => 'Sol12', 'contrasena_confirmation' => 'Sol12'], 'contrasena'],
            'sin número' => [['contrasena' => 'Senderismo', 'contrasena_confirmation' => 'Senderismo'], 'contrasena'],
            'confirmación diferente' => [['contrasena_confirmation' => 'OtraClave123'], 'contrasena'],
            'caracteres que exceden bcrypt' => [['contrasena' => str_repeat('á', 36).'1', 'contrasena_confirmation' => str_repeat('á', 36).'1'], 'contrasena'],
            'contraseña con tipo incorrecto' => [['contrasena' => ['invalida']], 'contrasena'],
            'correo con tipo incorrecto' => [['correo' => ['invalido']], 'correo'],
        ];
    }

    public function test_no_permite_reutilizar_el_correo_de_otra_cuenta(): void
    {
        User::factory()->create(['email' => 'maria@ejemplo.com']);

        $this->post('/registro', [...$this->datosRegistro(), 'correo' => 'MARIA@EJEMPLO.COM'])
            ->assertSessionHasErrors(['correo' => 'Este correo ya tiene una cuenta.']);

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_inicia_sesion_y_renueva_su_identificador(): void
    {
        $usuario = User::factory()->create(['email' => 'maria@ejemplo.com', 'password' => 'Bosque123']);
        $this->withSession(['referencia' => 'sesion-anterior']);
        $identificador = session()->getId();

        $this->post('/acceso', ['correo' => 'MARIA@EJEMPLO.COM', 'contrasena' => 'Bosque123'])
            ->assertRedirect('/mi-cuenta');

        $this->assertAuthenticatedAs($usuario);
        $this->assertNotSame($identificador, session()->getId());
    }

    public function test_conserva_los_espacios_de_la_contrasena(): void
    {
        $this->post('/registro', [
            ...$this->datosRegistro(),
            'contrasena' => ' Bosque123 ',
            'contrasena_confirmation' => ' Bosque123 ',
        ])->assertRedirect('/mi-cuenta');

        $this->assertTrue(Hash::check(' Bosque123 ', User::firstOrFail()->password));
        $this->assertFalse(Hash::check('Bosque123', User::firstOrFail()->password));
    }

    public function test_rechaza_el_acceso_incorrecto_sin_guardar_la_contrasena(): void
    {
        User::factory()->create(['email' => 'maria@ejemplo.com', 'password' => 'Bosque123']);

        $this->from('/acceso')->post('/acceso', ['correo' => 'maria@ejemplo.com', 'contrasena' => 'ClaveErronea123'])
            ->assertRedirect('/acceso')
            ->assertSessionHasErrors(['correo' => 'El correo o la contraseña no son correctos.']);

        $this->assertGuest();
        $this->assertNull(session()->getOldInput('contrasena'));
    }

    public function test_limita_los_intentos_repetidos_de_acceso(): void
    {
        for ($intento = 0; $intento < 5; $intento++) {
            $this->post('/acceso', ['correo' => 'nadie@ejemplo.com', 'contrasena' => 'ClaveErronea123'])
                ->assertSessionHasErrors(['correo' => 'El correo o la contraseña no son correctos.']);
        }

        $this->post('/acceso', ['correo' => 'nadie@ejemplo.com', 'contrasena' => 'ClaveErronea123'])
            ->assertSessionHasErrors('correo');

        $this->assertStringContainsString('varios intentos', session('errors')->first('correo'));
        $this->assertGuest();
    }

    public function test_la_cuenta_requiere_sesion_y_los_formularios_redirigen_a_quien_ya_entro(): void
    {
        $this->get('/mi-cuenta')->assertRedirect('/acceso');
        $this->actingAs(User::factory()->create());
        $this->get('/acceso')->assertRedirect('/mi-cuenta');
        $this->get('/registro')->assertRedirect('/mi-cuenta');
    }

    public function test_cierra_la_sesion_y_borra_los_datos_anteriores(): void
    {
        $this->actingAs(User::factory()->create())->withSession(['dato_anterior' => 'privado']);

        $this->post('/salir')->assertRedirect('/acceso');

        $this->assertGuest();
        $this->assertNull(session('dato_anterior'));
        $this->get('/mi-cuenta')->assertRedirect('/acceso');
    }

    public function test_una_conexion_caida_muestra_un_aviso_y_conserva_la_portada(): void
    {
        $conexionOriginal = config('database.default');
        config([
            'database.default' => 'servicio_no_disponible',
            'database.connections.servicio_no_disponible' => [
                'driver' => 'pgsql', 'host' => '127.0.0.1', 'port' => 1,
                'database' => 'no_disponible', 'username' => 'no_disponible',
                'password' => '', 'charset' => 'utf8', 'prefix' => '', 'sslmode' => 'prefer',
            ],
        ]);

        try {
            $this->get('/')->assertOk();
            $this->post('/acceso', ['correo' => 'maria@ejemplo.com', 'contrasena' => 'Bosque123'])
                ->assertStatus(503)->assertSee('Volvemos')->assertDontSee('SQLSTATE');
        } finally {
            // La transacción de pruebas debe cerrarse sobre su conexión original.
            config(['database.default' => $conexionOriginal]);
        }
    }

    private function datosRegistro(): array
    {
        return [
            'nombre' => 'María Pérez',
            'correo' => 'maria@ejemplo.com',
            'contrasena' => 'Bosque123',
            'contrasena_confirmation' => 'Bosque123',
        ];
    }
}
