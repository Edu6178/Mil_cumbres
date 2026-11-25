<?php

namespace Tests\Feature;

use App\Models\Programacion;
use App\Models\Reservacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppPreReservaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['whatsapp.habilitado' => false]);
        Http::preventStrayRequests();
        Bus::fake();
    }

    public function test_sin_consentimiento_no_envia_ni_ofrece_mandar_un_mensaje_manualmente(): void
    {
        $datos = $this->datos();
        $this->post('/pre-reservaciones', $datos)->assertRedirect();
        $anterior = session('ultima_pre_reserva');
        $this->post('/pre-reservaciones', $datos)->assertRedirect();
        $acuse = session('ultima_pre_reserva');
        $this->assertNotSame($anterior['folio'], $acuse['folio']);
        $this->assertSame('sin_solicitar', $acuse['whatsapp_estado']);
        $this->assertArrayNotHasKey('whatsapp', $acuse);
        $this->get('/')->assertSee('Descargar mi pre-reserva')->assertDontSee('https://wa.me/')
            ->assertDontSee('Abrir WhatsApp con mi número')->assertDontSee('Elegir destinatario');
        Http::assertNothingSent();
        Bus::assertNothingDispatched();
        $this->assertDatabaseCount('reservaciones', 2);
        $this->assertDatabaseCount('pagos', 0);
        $this->assertSame(['Pre-reserva'], Reservacion::with('estado')->get()->pluck('estado.nombre_estado')->unique()->values()->all());
    }

    public function test_resumen_personal_firmado_escapa_datos_y_no_extiende_la_vigencia(): void
    {
        $datos = $this->datos();
        $datos['nombre'] = 'Ana <script>alert(1)</script>';
        $this->post('/pre-reservaciones', $datos);
        $acuse = session('ultima_pre_reserva');
        $url = $acuse['resumen'];
        $respuesta = $this->get($url)->assertOk()->assertSee('Medios de pago por confirmar')
            ->assertSee('Ver los siguientes pasos')->assertSee('Descargar mi pre-reserva')
            ->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('DATO-PRIVADO')
            ->assertHeader('Referrer-Policy', 'no-referrer')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));
        $this->assertStringContainsString('expires='.$acuse['vence'], $respuesta->getContent());
        $this->get(str_replace('/1/resumen', '/2/resumen', $url))->assertForbidden();
        $this->get('/pre-reservaciones/1/resumen')->assertForbidden();
        $this->travel(25)->hours();
        $this->get($url)->assertForbidden()->assertDontSee('Ana');
        $this->assertDatabaseCount('pagos', 0);
    }

    public function test_los_acuses_antiguos_tampoco_reactivan_los_botones_manuales(): void
    {
        $this->app['env'] = 'production';
        $this->withSession(['ultima_pre_reserva' => [
            'folio' => 'MC-ANTERIOR', 'vence' => now()->addDay()->timestamp, 'pdf' => '/comprobante.pdf',
            'whatsapp' => ['elegir' => 'https://wa.me/?text=prueba', 'propio' => 'https://wa.me/525500000000'],
        ]])->get('/')->assertOk()->assertSee('MC-ANTERIOR')->assertDontSee('https://wa.me/')
            ->assertDontSee('debes pulsar Enviar')->assertDontSee('WhatsApp aceptó el envío');
        Http::assertNothingSent();
    }

    public function test_api_solo_envia_la_solicitud_nueva_con_consentimiento_y_no_la_repite(): void
    {
        $datos = $this->datos();
        $this->configurarApi();
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.prueba']]], 200)]);
        $this->post('/pre-reservaciones', $datos)->assertSessionHasNoErrors();
        Http::assertNothingSent();
        $anterior = Reservacion::firstOrFail();
        $this->post('/pre-reservaciones', [...$datos, 'whatsapp_acepta' => '1'])->assertSessionHasNoErrors();
        $nueva = Reservacion::latest('id')->firstOrFail();
        $this->assertSame('aceptado', $nueva->whatsapp_estado);
        $this->assertSame('sin_solicitar', $anterior->fresh()->whatsapp_estado);
        $this->assertSame('wamid.prueba', $nueva->whatsapp_mensaje_id);
        Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v27.0/123456/messages'
            && $request['to'] === '525500000000' && $request['type'] === 'template'
            && $request['template']['components'][0]['parameters'][1]['text'] === $nueva->folio
            && str_contains($request['template']['components'][1]['parameters'][0]['text'], '/resumen?'));
        $resumen = session('ultima_pre_reserva.resumen');
        app(\App\Services\Contenido\WhatsAppPreReserva::class)->enviarNueva($nueva, true, $resumen);
        $this->get('/')->assertSee('WhatsApp aceptó el envío')->assertDontSee('https://wa.me/')
            ->assertDontSee('Elegir destinatario');
        Http::assertSentCount(1);
        $this->assertDatabaseCount('pagos', 0);
    }

    public function test_error_de_api_conserva_la_reserva_y_no_filtra_secretos(): void
    {
        $datos = $this->datos();
        $this->configurarApi();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['code' => 190, 'message' => 'TOKEN-PRIVADO']], 401)]);
        $this->post('/pre-reservaciones', [...$datos, 'whatsapp_acepta' => '1'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservaciones', ['whatsapp_estado' => 'fallido', 'whatsapp_error_codigo' => '190']);
        $this->get('/')->assertSee('Tu pre-reserva está guardada')->assertDontSee('TOKEN-PRIVADO');
        Http::assertSentCount(1);
    }

    public function test_sin_credenciales_no_inventa_un_envio_y_normaliza_el_prefijo_mexicano(): void
    {
        $this->post('/pre-reservaciones', [...$this->datos(), 'whatsapp_acepta' => '1']);
        $this->assertDatabaseHas('reservaciones', ['whatsapp_estado' => 'no_configurado']);
        Http::assertNothingSent();
        $this->assertSame('525500000000', \App\Services\Contenido\NumeroWhatsApp::normalizar('+52 1 550 000 0000'));
        $this->assertSame('525500000000', \App\Services\Contenido\NumeroWhatsApp::normalizar('5500000000'));
    }

    private function configurarApi(): void
    {
        config(['whatsapp.habilitado' => true, 'whatsapp.token' => 'token-ficticio', 'whatsapp.telefono_id' => '123456',
            'whatsapp.version' => 'v27.0', 'whatsapp.plantilla' => 'pre_reserva', 'whatsapp.idioma' => 'es_MX']);
    }

    private function datos(): array
    {
        $categoria = DB::table('categorias')->insertGetId(['nombre_categoria' => 'Senderismo']);
        $servicio = DB::table('servicios')->insertGetId(['categoria_id' => $categoria, 'nombre_servicio' => 'Paseo del bosque', 'estado_servicio' => 'activo']);
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Región de prueba']);
        $relacion = DB::table('servicio_regiones')->insertGetId(['servicio_id' => $servicio, 'region_id' => $region, 'cupo_maximo' => 20]);
        $programacion = Programacion::create(['servicio_reg_id' => $relacion, 'cupo_maximo' => 20, 'estado' => 'programada', 'fecha_inicio' => now()->addDays(4), 'fecha_fin' => now()->addDays(4)->addHours(2)]);
        return ['programacion_id' => $programacion->id, 'numero_personas' => 1, 'nombre' => 'Ana', 'edad' => 25, 'telefono' => '5500000000', 'personalizacion' => 'DATO-PRIVADO'];
    }
}
