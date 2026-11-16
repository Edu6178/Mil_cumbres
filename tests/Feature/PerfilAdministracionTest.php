<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PerfilAdministracionTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_cuenta_administradora_entra_directamente_al_panel(): void
    {
        $usuario = User::factory()->create(['password' => 'Prueba123']);
        config(['administracion.usuarios' => [$usuario->id]]);

        $this->post('/acceso', ['correo' => $usuario->email, 'contrasena' => 'Prueba123'])
            ->assertRedirect('/administracion');
        $this->get('/acceso')->assertRedirect('/administracion');
    }

    public function test_el_perfil_y_las_fotos_requieren_autorizacion(): void
    {
        Storage::fake('local');
        $this->get('/administracion/perfil')->assertRedirect('/acceso');
        $this->get('/administracion/perfil/foto')->assertRedirect('/acceso');
        $this->actingAs(User::factory()->create());
        $this->get('/administracion/perfil')->assertForbidden();
        $this->get('/administracion/perfil/foto')->assertForbidden();
        $this->post('/administracion/perfil/foto', ['foto' => $this->foto()])->assertForbidden();
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_la_foto_es_privada_y_solo_se_entrega_al_usuario_actual(): void
    {
        Storage::fake('local');
        $propietario = User::factory()->create();
        $otraCuenta = User::factory()->create();
        config(['administracion.usuarios' => [$propietario->id, $otraCuenta->id]]);
        $this->actingAs($propietario);
        $this->get('/administracion/perfil')->assertOk()->assertSee($propietario->email)->assertSee('Elegir foto');
        $this->post('/administracion/perfil/foto', ['foto' => $this->foto()])
            ->assertRedirect('/administracion/perfil')->assertSessionHas('estado');
        Storage::disk('local')->assertExists('perfiles/'.$propietario->id.'/foto');
        $this->get('/administracion/perfil/foto')->assertOk()->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/administracion')->assertOk()->assertSee(route('administracion.perfil.foto'));
        $this->actingAs($otraCuenta)->get('/administracion/perfil/foto')->assertNotFound();
        $this->assertDatabaseCount('users', 2);
    }

    public function test_rechaza_archivos_que_no_son_fotos_y_fotos_demasiado_grandes(): void
    {
        Storage::fake('local');
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->actingAs($usuario);

        foreach ([
            UploadedFile::fake()->createWithContent('foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->createWithContent('foto.php.png', '<?php echo "no es una foto";'),
            UploadedFile::fake()->createWithContent('grande.png', $this->contenidoFoto().str_repeat('x', 2 * 1024 * 1024)),
        ] as $archivo) {
            $this->from('/administracion/perfil')->post('/administracion/perfil/foto', ['foto' => $archivo])
                ->assertRedirect('/administracion/perfil')->assertSessionHasErrors('foto');
        }
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    private function foto(): UploadedFile
    {
        // PNG mínimo para comprobar archivos reales sin depender de la extensión GD.
        return UploadedFile::fake()->createWithContent('mi foto.png', $this->contenidoFoto());
    }

    private function contenidoFoto(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWQAAAABJRU5ErkJggg==');
    }
}
