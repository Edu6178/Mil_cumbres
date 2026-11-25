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
        config(['whatsapp.pruebas' => true, 'whatsapp.numero_prueba' => '5500000000']);
        Http::preventStrayRequests();
        Bus::fake();
    }

    public function test_prepara_solo_la_nueva_solicitud_sin_enviar_mensajes_ni_confirmar(): void
    {
        $datos = $this->datos();
        config(['whatsapp.pruebas' => false]);
        $this->post('/pre-reservaciones', $datos)->assertRedirect();
        $anterior = session('ultima_pre_reserva');
        $this->assertSame([], $anterior['whatsapp']);
        config(['whatsapp.pruebas' => true]);
        $this->post('/pre-reservaciones', $datos)->assertRedirect();
        $acuse = session('ultima_pre_reserva');
        $enlaces = $acuse['whatsapp'];
        $this->assertStringStartsWith('https://wa.me/525500000000?text=', $enlaces['prueba']);
        $this->assertStringStartsWith('https://wa.me/?text=', $enlaces['elegir']);
        $this->assertStringContainsString('Hola, Ana', $enlaces['mensaje']);
        $this->assertStringContainsString('Paseo del bosque', $enlaces['mensaje']);
        $this->assertStringContainsString($acuse['folio'], $enlaces['mensaje']);
        $this->assertStringNotContainsString($anterior['folio'], $enlaces['mensaje']);
        $this->get('/')->assertSee('Vista previa del mensaje')->assertSee('Elegir destinatario');
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
        $url = $acuse['whatsapp']['resumen'];
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

    public function test_no_habilita_el_numero_personal_fuera_de_local_o_con_numero_invalido(): void
    {
        $datos = $this->datos();
        config(['whatsapp.numero_prueba' => 'incorrecto']);
        $this->post('/pre-reservaciones', $datos)->assertRedirect();
        $this->assertSame([], session('ultima_pre_reserva.whatsapp'));
        config(['whatsapp.numero_prueba' => '5500000000']);
        $this->app['env'] = 'production';
        $this->assertSame([], app(\App\Services\Contenido\CompartirPreReserva::class)->preparar(Reservacion::first(), now()->addDay()));
        $this->get('/')->assertDontSee('https://wa.me/');
        Http::assertNothingSent();
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
