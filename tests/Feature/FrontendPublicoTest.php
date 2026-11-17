<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FrontendPublicoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_las_nueve_vistas_funcionan_sin_consultar_la_base(): void
    {
        config(['database.connections.sqlite.database' => storage_path('base-inexistente-para-esta-prueba.sqlite')]);
        DB::enableQueryLog();
        foreach (['/catalogo', '/guias', '/guias/perfil', '/experiencias/detalle', '/nosotros', '/turismo-regenerativo', '/operadora', '/consultoria', '/comunidades'] as $ruta) {
            $this->get($ruta)->assertOk()->assertSee('<html lang="es">', false)->assertSee('Navegación principal');
        }
        $this->assertSame([], DB::getQueryLog());
    }

    public function test_las_cinco_lineas_conservan_cuatro_nombres_pendientes(): void
    {
        $respuesta = $this->get('/catalogo')->assertOk()->assertSee('Senderismo')->assertSee('Línea 5 por confirmar');
        $this->assertSame(4, preg_match_all('/<button\b[^>]*\bdisabled\b/', $respuesta->getContent()));
        $respuesta->assertSee('data-total-servicios>0</strong>', false)->assertDontSee('Ruta de prueba');
        $this->get('/catalogo?buscar[]=texto&linea[]=valor&duracion[]=corta')->assertOk();
    }

    public function test_los_filtros_get_distinguen_rutas_y_no_publican_servicios_ocultos(): void
    {
        // Estos datos existen únicamente en la prueba, nunca se insertan en la base del proyecto.
        $this->app['view']->share('servicios', [
            $this->servicio(),
            [...$this->servicio(), 'nombre_servicio' => 'Recorrido largo', 'duracion_estimada' => 420],
            [...$this->servicio(), 'nombre_servicio' => 'Servicio oculto', 'estado_servicio' => 'oculto'],
        ]);
        $respuesta = $this->get('/catalogo?buscar=BOSQUE&linea=senderismo&duracion=corta')->assertOk();
        $respuesta->assertSee('data-total-servicios>1</strong>', false)->assertDontSee('Servicio oculto');
        $documento = new \DOMDocument();
        @$documento->loadHTML('<?xml encoding="utf-8" ?>'.$respuesta->getContent());
        $titulos = (new \DOMXPath($documento))->query('//article[@data-servicio and not(@hidden)]//h2');
        $this->assertCount(1, $titulos);
        $this->assertSame('Ruta del bosque', trim($titulos->item(0)->textContent));
    }

    public function test_el_detalle_muestra_itinerario_precio_y_unidad_sin_inventar_fechas(): void
    {
        $this->app['view']->share('servicio', $this->servicio());
        $this->get('/experiencias/detalle')->assertOk()->assertSee('Ruta del bosque')->assertSee('Encuentro y recorrido')
            ->assertSee('$950.50')->assertSee('por persona')->assertSee('Aún no hay fechas publicadas');
    }

    public function test_el_perfil_muestra_solo_informacion_publica_autorizada(): void
    {
        $guia = ['nombre' => 'Guía de prueba', 'publicado' => false, 'formacion' => 'Formación autorizada',
            'talentos' => 'Observación e interpretación', 'especialidad' => 'Senderismo',
            'telefono' => 'TELEFONO-NO-PUBLICAR', 'notas_medicas' => 'DATO-PRIVADO-NO-PUBLICAR'];
        $this->app['view']->share('guia', $guia);
        $this->get('/guias/perfil')->assertOk()->assertDontSee('Guía de prueba')->assertSee('Perfil de guía');
        $this->app['view']->share('guia', [...$guia, 'publicado' => true]);
        $this->get('/guias/perfil')->assertOk()->assertSee('Guía de prueba')->assertSee('Formación autorizada')
            ->assertSee('Observación e interpretación')->assertDontSee('TELEFONO-NO-PUBLICAR')->assertDontSee('DATO-PRIVADO-NO-PUBLICAR');
        $this->app['view']->share('guias', [$guia, [...$guia, 'nombre' => 'Guía autorizado', 'publicado' => true]]);
        $this->get('/guias')->assertOk()->assertSee('Guía autorizado')->assertDontSee('Guía de prueba');
    }

    public function test_las_descripciones_se_escapan_y_las_vistas_no_simulan_reservaciones(): void
    {
        $this->app['view']->share('servicio', [...$this->servicio(), 'descripcion' => '<script>alert("prueba")</script>']);
        $this->get('/experiencias/detalle')->assertOk()->assertDontSee('<script>alert("prueba")</script>', false)
            ->assertSee('&lt;script&gt;', false);
        $this->post('/catalogo', [])->assertStatus(405);
        $this->post('/guias/perfil', [])->assertStatus(405);
    }

    private function servicio(): array
    {
        return ['nombre_servicio' => 'Ruta del bosque', 'estado_servicio' => 'activo', 'linea_clave' => 'senderismo',
            'linea_nombre' => 'Senderismo', 'duracion_estimada' => 120, 'precio_base' => '950.50',
            'unidad_cobro' => 'por persona', 'descripcion' => 'Recorrido de prueba por el bosque.', 'itinerario' => 'Encuentro y recorrido'];
    }
}
