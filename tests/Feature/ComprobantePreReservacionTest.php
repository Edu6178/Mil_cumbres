<?php

namespace Tests\Feature;

use App\Models\Programacion;
use App\Models\Reservacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ComprobantePreReservacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_la_solicitud_entrega_un_pdf_real_sin_registro_y_no_duplica_la_reserva(): void
    {
        $this->post('/pre-reservaciones', $this->datos())
            ->assertRedirect('/#pre-reservacion')->assertSessionHas('ultima_pre_reserva');
        $acuse = session('ultima_pre_reserva');
        $this->get('/')->assertSee('Descargar mi pre-reserva')->assertSee($acuse['folio']);
        // La tarjeta se conserva al actualizar; descargar no vuelve a ejecutar el envío del formulario.
        $this->get('/')->assertSee('Descargar mi pre-reserva');
        $respuesta = $this->get($acuse['pdf'])->assertOk()->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload('Pre-reserva-'.$acuse['folio'].'.pdf');
        $this->assertStringStartsWith('%PDF-', $respuesta->getContent());
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));
        $this->assertDatabaseCount('reservaciones', 1);
        $this->assertDatabaseCount('personas', 2);
        $this->assertDatabaseCount('pagos', 0);
        $this->assertSame('Pre-reserva', Reservacion::first()->estado->nombre_estado);
        $this->assertGuest();
    }

    public function test_no_muestra_descarga_si_la_solicitud_es_invalida(): void
    {
        $datos = $this->datos();
        $datos['telefono'] = '';
        $this->from('/')->post('/pre-reservaciones', $datos)
            ->assertSessionHasErrors('telefono')->assertSessionMissing('ultima_pre_reserva');
        $this->get('/')->assertDontSee('Descargar mi pre-reserva');
        $this->assertDatabaseCount('reservaciones', 0);
    }

    public function test_no_permite_descargar_por_id_ni_cambiar_la_solicitud_del_enlace(): void
    {
        $datos = $this->datos();
        $this->post('/pre-reservaciones', $datos);
        $primero = session('ultima_pre_reserva');
        $this->post('/pre-reservaciones', $datos);
        $segundo = Reservacion::latest('id')->first();
        $this->get(route('pre-reservaciones.pdf', ['reservacion' => $segundo->id]))->assertForbidden();
        $urlModificada = str_replace('/1/comprobante.pdf', '/'.$segundo->id.'/comprobante.pdf', $primero['pdf']);
        $this->get($urlModificada)->assertForbidden()->assertSee('No pudimos validar el enlace');
    }

    public function test_un_enlace_vencido_no_entrega_datos_y_la_tarjeta_deja_de_mostrarse(): void
    {
        $this->post('/pre-reservaciones', $this->datos());
        $url = session('ultima_pre_reserva.pdf');
        $this->travel(25)->hours();
        $this->get($url)->assertForbidden()->assertSee('vigencia de 24 horas')->assertDontSee('Álvarez');
        $this->get('/')->assertDontSee('Descargar mi pre-reserva');
    }

    public function test_el_acuse_reproduce_las_respuestas_escapadas_y_excluye_notas_internas(): void
    {
        $datos = $this->datos();
        $datos['proposito_visita'] = 'Aprender del bosque <script>alert(1)</script>';
        $this->post('/pre-reservaciones', $datos);
        DB::table('asigna_reservacion_invitados')->update(['notas_medicas' => 'NOTA-INTERNA-NO-PUBLICAR']);
        $solicitud = Reservacion::with(['cliente.persona', 'acompanantes', 'estado', 'programacion.servicioRegion.servicio', 'programacion.servicioRegion.region'])->first();
        $html = view('pdf.pre-reservacion', ['solicitud' => $solicitud, 'simbolo' => ''])->render();
        foreach (['Álvarez', 'Lucía', 'Familiar', 'Sendero del bosque', 'Compartir una caminata', 'Correo electrónico (opcional)', 'No proporcionado', 'Propósito de la visita', 'Comentarios para el recorrido'] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('NOTA-INTERNA-NO-PUBLICAR', $html);
        $this->assertStringContainsString('no es un comprobante de pago', $html);
    }

    public function test_una_solicitud_eliminada_no_genera_un_comprobante_vacio(): void
    {
        $url = URL::temporarySignedRoute('pre-reservaciones.pdf', now()->addHour(), ['reservacion' => 999]);
        $this->get($url)->assertNotFound();
    }

    private function datos(): array
    {
        // Las pruebas se ejecutan con SQLite en memoria, sin modificar la base de desarrollo.
        $categoria = DB::table('categorias')->insertGetId(['nombre_categoria' => 'Senderismo']);
        $servicio = DB::table('servicios')->insertGetId([
            'categoria_id' => $categoria, 'nombre_servicio' => 'Sendero del bosque',
            'precio_base' => 450, 'estado_servicio' => 'activo',
        ]);
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Bosque de los Encinos']);
        $relacion = DB::table('servicio_regiones')->insertGetId(['servicio_id' => $servicio, 'region_id' => $region, 'cupo_maximo' => 20]);
        $programacion = Programacion::create([
            'servicio_reg_id' => $relacion, 'cupo_maximo' => 20, 'estado' => 'programada',
            'fecha_inicio' => now()->addDays(3), 'fecha_fin' => now()->addDays(3)->addHours(3),
        ]);

        return ['programacion_id' => $programacion->id, 'numero_personas' => 2, 'tipo_grupo' => 'Familiar',
            'nombre' => 'Mariana', 'apellido_paterno' => 'Álvarez', 'apellido_materno' => 'Ríos',
            'telefono' => '5512345678', 'fecha_nacimiento' => '1998-02-15',
            'proposito_visita' => 'Conocer el bosque y observar aves.', 'personalizacion' => 'Compartir una caminata en familia.',
            'acompanantes' => [['nombre' => 'Lucía', 'apellido_paterno' => 'Álvarez', 'fecha_nacimiento' => '2015-03-20']]];
    }
}
