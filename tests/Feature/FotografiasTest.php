<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\User;
use App\Services\Contenido\BibliotecaFotografica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FotografiasTest extends TestCase
{
    use RefreshDatabase;
    private string $archivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->archivo = storage_path('framework/testing/fotos-'.bin2hex(random_bytes(10)).'.json');
        config(['fotografias.archivo' => $this->archivo]);
    }

    protected function tearDown(): void
    {
        File::delete([$this->archivo, $this->archivo.'.lock']);
        parent::tearDown();
    }

    public function test_el_lote_incluye_todos_los_archivos_y_las_imagenes_existen(): void
    {
        $fotos = app(BibliotecaFotografica::class)->todas();
        $this->assertCount(42, $fotos);
        $this->assertSame(43, array_sum(array_map(fn ($foto) => count($foto['originales']), $fotos)));
        foreach ($fotos as $foto) {
            $this->assertFileExists(public_path($foto['archivo']));
            $this->assertFileExists(public_path($foto['miniatura']));
            $this->assertNotEmpty($foto['alternativo']);
        }
    }

    public function test_los_apartados_publicos_y_toda_la_galeria_no_requieren_consultas_extra(): void
    {
        DB::enableQueryLog();
        $this->get('/galeria')->assertOk()->assertSee('42</strong>', false)->assertSee('Cascada entre rocas')->assertSee('data-abrir-foto', false);
        $this->get('/galeria?album=aves&buscar=binoculares')->assertOk();
        $this->get('/galeria?album[]=x&buscar[]=x')->assertOk();
        $this->get('/nosotros')->assertOk()->assertSee('empresa-1795396108555.webp')->assertDontSee('bosque-portada-movil.webp');
        $this->get('/acceso')->assertOk()->assertSee('empresa-1795396108398.webp');
        $this->assertSame([], DB::getQueryLog());
    }

    public function test_solo_el_administrador_puede_editar_o_cargar_fotografias(): void
    {
        $this->get('/administracion/fotografias')->assertRedirect('/acceso');
        $this->post('/administracion/fotografias', [])->assertRedirect('/acceso');
        $this->actingAs(User::factory()->create());
        config(['administracion.usuarios' => []]);
        $this->get('/administracion/fotografias')->assertForbidden();
        $this->put('/administracion/fotografias/empresa-1795396108398', [])->assertForbidden();
        $this->put('/administracion/fotografias/portadas', [])->assertForbidden();
        $this->assertFileDoesNotExist($this->archivo);
    }

    public function test_edita_encuadre_orden_publicacion_y_conserva_las_otras_fotos(): void
    {
        $this->autorizar();
        $this->get('/administracion/fotografias')->assertOk()->assertSee('Cargar como borradores');
        $id = 'empresa-1795396108432';
        $datos = ['titulo' => 'La cascada del recorrido', 'alternativo' => 'Agua entre las rocas.', 'album' => 'recorridos',
            'foco_x' => 32, 'foco_y' => 63, 'posicion' => 1, 'publicada' => 1, 'destacada' => 1];
        $this->put('/administracion/fotografias/'.$id, $datos)->assertSessionHasNoErrors();
        $biblioteca = new BibliotecaFotografica;
        $this->assertSame($id, array_key_first($biblioteca->todas()));
        $this->assertSame('32% 63%', $biblioteca->foto($id)['foco']);
        $this->assertCount(42, $biblioteca->todas());
        $this->put('/administracion/fotografias/'.$id, [...$datos, 'publicada' => 0])->assertSessionHasNoErrors();
        $this->assertCount(41, $biblioteca->todas());
        $this->assertNull($biblioteca->foto($id));
        $this->assertCount(42, $biblioteca->todas(true));
        $this->put('/administracion/fotografias/'.$id, [...$datos, 'foco_x' => 101])->assertSessionHasErrors('foco_x');
    }

    public function test_cambia_las_portadas_y_la_foto_del_servicio_sin_modificar_su_precio(): void
    {
        $this->autorizar();
        $categoria = Categoria::create(['nombre_categoria' => 'Senderismo']);
        $servicio = Servicio::create(['nombre_servicio' => 'Ruta de prueba', 'precio_base' => 780, 'categoria_id' => $categoria->id, 'estado_servicio' => 'activo']);
        $foto = 'empresa-1795396108432';
        $this->put('/administracion/fotografias/portadas', ['espacios' => ['inicio-portada' => $foto], 'servicios' => [$servicio->id => $foto]])->assertSessionHasNoErrors();
        $biblioteca = new BibliotecaFotografica;
        $this->assertSame($foto, $biblioteca->espacio('inicio-portada')['id']);
        $this->assertSame($foto, $biblioteca->servicio($servicio->toArray())['id']);
        $this->assertSame('780.00', $servicio->fresh()->precio_base);
        $this->put('/administracion/fotografias/portadas', ['espacios' => ['desconocido' => $foto]])->assertSessionHasErrors('espacios');
        $this->put('/administracion/fotografias/portadas', ['espacios' => ['inicio-portada' => '../.env']])->assertSessionHasErrors('espacios.inicio-portada');
    }

    public function test_la_carga_crea_borradores_y_rechaza_formatos_no_permitidos(): void
    {
        Storage::fake('local');
        $this->autorizar();
        $foto = new UploadedFile(public_path('imagenes/empresa/empresa-1795396108432-640.webp'), 'cascada.webp', 'image/webp', null, true);
        $this->post('/administracion/fotografias', ['fotos' => [$foto], 'album' => 'recorridos'])->assertSessionHasNoErrors();
        $biblioteca = new BibliotecaFotografica;
        $this->assertCount(42, $biblioteca->todas());
        $this->assertCount(43, $biblioteca->todas(true));
        $id = array_key_first($biblioteca->estado()['fotos']);
        $nueva = $biblioteca->foto($id, true);
        $this->assertFalse($nueva['publicada']);
        $this->assertSame('Cascada', $nueva['titulo']);
        Storage::disk('local')->assertExists($nueva['archivo']);
        $this->get('/fotografias/'.$id)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        auth()->logout();
        $this->get('/fotografias/'.$id)->assertNotFound();
        $this->autorizar();
        $this->post('/administracion/fotografias', ['fotos' => [UploadedFile::fake()->create('imagen.svg', 1, 'image/svg+xml')], 'album' => 'aves'])->assertSessionHasErrors('fotos.0');
    }

    private function autorizar(): void
    {
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->actingAs($usuario);
    }
}
