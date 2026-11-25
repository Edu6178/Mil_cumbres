<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Contenido\BibliotecaFotografica;
use Database\Seeders\DatosDemostracionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FotografiasEditorTest extends TestCase
{
    use RefreshDatabase;
    private string $archivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->archivo = storage_path('framework/testing/editor-fotos-'.bin2hex(random_bytes(8)).'.json');
        config(['fotografias.archivo' => $this->archivo, 'demostracion.registro' => $this->archivo.'.demo']);
    }

    protected function tearDown(): void
    {
        File::delete([$this->archivo, $this->archivo.'.lock', $this->archivo.'.demo']);
        parent::tearDown();
    }

    public function test_la_vista_y_el_guardado_son_exclusivos_del_administrador(): void
    {
        $this->get('/galeria?vista_fotografias=1')->assertForbidden();
        $this->patchJson('/administracion/fotografias/cambios', [])->assertUnauthorized();
        $this->actingAs(User::factory()->create());
        $this->get('/catalogo?vista_fotografias=1')->assertForbidden();
        $this->patchJson('/administracion/fotografias/cambios', [])->assertForbidden();
        $this->assertFileDoesNotExist($this->archivo);
        $this->autorizar();
        $this->get('/galeria?vista_fotografias=1')->assertOk()->assertSee('data-editor-fotos-vista', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->get('/galeria')->assertOk()->assertDontSee('data-editor-fotos-vista', false);
        $this->assertFileDoesNotExist($this->archivo);
    }

    public function test_aplica_un_lote_completo_de_foto_portada_servicio_y_guia_sin_tocar_la_operacion(): void
    {
        $this->autorizar();
        $this->seed(DatosDemostracionSeeder::class);
        $biblioteca = new BibliotecaFotografica;
        $servicio = DB::table('servicios')->first(); $guia = DB::table('personal')->first();
        $reservas = DB::table('reservaciones')->count(); $pagos = DB::table('pagos')->count();
        $foto = 'empresa-1795396108432';
        $lote = [...$this->lote(), 'fotos' => [$foto => ['titulo' => 'Una nueva mirada', 'foco_x' => 30]],
            'espacios' => ['inicio-portada' => $foto], 'servicios' => [$servicio->id => $foto], 'guias' => [$guia->id => $foto]];
        $this->assertNotSame('Una nueva mirada', $biblioteca->foto($foto)['titulo']);
        $respuesta = $this->patchJson('/administracion/fotografias/cambios', $lote)->assertOk();
        $respuesta->assertJsonPath('fotos.'.$foto.'.titulo', 'Una nueva mirada');
        $this->assertSame($foto, $biblioteca->espacio('inicio-portada')['id']);
        $this->assertSame($foto, $biblioteca->servicio((array) $servicio)['id']);
        $this->assertSame($foto, $biblioteca->guia($guia->id)['id']);
        $this->assertSame($servicio->precio_base, DB::table('servicios')->where('id', $servicio->id)->value('precio_base'));
        $this->assertDatabaseCount('reservaciones', $reservas); $this->assertDatabaseCount('pagos', $pagos);
        config(['demostracion.habilitada' => true]);
        $this->get('/guias/'.$guia->id)->assertOk()->assertSee('data-fotografia-id="'.$foto.'"', false);
    }

    public function test_rechaza_versiones_antiguas_sin_sobrescribir_la_otra_sesion(): void
    {
        $this->autorizar(); $viejo = $this->lote(); $foto = 'empresa-1795396108432';
        $biblioteca = new BibliotecaFotografica;
        $biblioteca->editar($foto, ['titulo' => 'Cambio de otra sesión']);
        $this->patchJson('/administracion/fotografias/cambios', [...$viejo, 'fotos' => [$foto => ['titulo' => 'No sobrescribir']]])->assertConflict();
        $this->assertSame('Cambio de otra sesión', $biblioteca->foto($foto)['titulo']);
    }

    public function test_un_lote_invalido_no_aplica_ninguno_de_sus_cambios(): void
    {
        $this->autorizar(); $foto = 'empresa-1795396108432'; $otra = 'empresa-1795396108058';
        $biblioteca = new BibliotecaFotografica;
        $biblioteca->editar($otra, ['publicada' => false]);
        $titulo = $biblioteca->foto($foto)['titulo']; $revision = $biblioteca->revision();
        $this->patchJson('/administracion/fotografias/cambios', [...$this->lote(),
            'fotos' => [$foto => ['titulo' => 'No debe guardarse']], 'espacios' => ['inicio-portada' => $otra]])->assertUnprocessable();
        $this->assertSame($revision, $biblioteca->revision());
        $this->assertSame($titulo, $biblioteca->foto($foto)['titulo']);
        $this->patchJson('/administracion/fotografias/cambios', [...$this->lote(), 'fotos' => [$foto => ['archivo' => '../.env']]])->assertUnprocessable();
        $this->patchJson('/administracion/fotografias/cambios', [...$this->lote(), 'espacios' => ['desconocido' => $foto]])->assertUnprocessable();
        $this->patchJson('/administracion/fotografias/cambios', [...$this->lote(), 'guias' => [999999 => $foto]])->assertUnprocessable();
        $this->assertSame($revision, $biblioteca->revision());
    }

    public function test_publica_un_borrador_y_lo_asigna_en_el_mismo_lote(): void
    {
        $this->autorizar(); $foto = 'empresa-1795396108432'; $biblioteca = new BibliotecaFotografica;
        $biblioteca->editar($foto, ['publicada' => false]);
        $this->get('/galeria')->assertDontSee('data-fotografia-id="'.$foto.'"', false);
        $this->patchJson('/administracion/fotografias/cambios', [...$this->lote(), 'fotos' => [$foto => ['publicada' => true]], 'espacios' => ['hero-comunidades' => $foto]])->assertOk();
        $this->get('/comunidades')->assertOk()->assertSee('data-editor-clave="hero-comunidades"', false)->assertSee('data-fotografia-id="'.$foto.'"', false);
        $this->get('/galeria')->assertSee('data-fotografia-id="'.$foto.'"', false);
    }

    public function test_el_editor_incluye_ubicaciones_legibles_y_escapa_los_datos_del_json(): void
    {
        $this->autorizar(); $biblioteca = new BibliotecaFotografica;
        $biblioteca->editar('empresa-1795396108432', ['titulo' => '</script><script>alert(1)</script>']);
        $this->get('/administracion/fotografias')->assertOk()->assertSee('Mi biblioteca')->assertSee('Galería publicada')
            ->assertSee('Páginas y portadas')->assertSee('Guardar cambios modificados')->assertSee('data-fotos-datos', false)
            ->assertDontSee('</script><script>alert(1)</script>', false);
    }

    private function lote(): array
    {
        return ['revision' => (new BibliotecaFotografica)->revision(), 'fotos' => [], 'espacios' => [], 'servicios' => [], 'guias' => []];
    }

    private function autorizar(): void
    {
        $usuario = User::factory()->create(); config(['administracion.usuarios' => [$usuario->id]]); $this->actingAs($usuario);
    }
}
