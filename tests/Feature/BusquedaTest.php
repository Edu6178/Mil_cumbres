<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\EstadoReservacion;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Programacion;
use App\Models\Reservacion;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusquedaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_solo_administradores_pueden_consultar_las_sugerencias_y_sus_resultados(): void
    {
        $this->get('/administracion/buscar?buscar=con')->assertRedirect('/acceso');
        $this->getJson('/administracion/buscar?buscar=con')->assertUnauthorized();
        $this->actingAs(User::factory()->create());
        config(['administracion.usuarios' => []]);
        $this->get('/administracion/buscar?buscar=con')->assertForbidden();
        $this->getJson('/administracion/buscar?buscar=con')->assertForbidden();
    }

    public function test_una_letra_muestra_apartados_con_resumen_y_funciona_sin_javascript(): void
    {
        $this->autorizar();
        $respuesta = $this->getJson('/administracion/buscar?buscar=R')->assertOk();
        $titulos = collect($respuesta->json('resultados'))->pluck('titulo');
        $this->assertTrue($titulos->contains('Resumen general'));
        $this->assertTrue($titulos->contains('Resumen financiero'));
        $finanzas = collect($respuesta->json('resultados'))->firstWhere('titulo', 'Resumen financiero');
        $this->assertStringContainsString('/administracion/finanzas', $finanzas['url']);
        $this->assertStringContainsString('saldos', $finanzas['resumen']);
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));
        $this->get('/administracion/buscar?buscar=finanzas')->assertOk()->assertSee('Resumen financiero')->assertSee('Coincidencias destacadas');
    }

    public function test_busca_servicios_por_prefijo_palabras_sin_acentos_y_respeta_los_filtros(): void
    {
        $this->autorizar();
        $categoria = Categoria::create(['nombre_categoria' => 'Consultoría']);
        $servicio = Servicio::create(['nombre_servicio' => 'CONSULTORÍA turística', 'descripcion' => 'Planeación para núcleos agrarios', 'categoria_id' => $categoria->id, 'estado_servicio' => 'activo']);
        Servicio::create(['nombre_servicio' => 'Consultoría oculta', 'categoria_id' => $categoria->id, 'estado_servicio' => 'oculto']);
        $respuesta = $this->getJson('/administracion/buscar?buscar=con&ambito=servicios&estado=activo')->assertOk()->assertJsonCount(1, 'resultados');
        $this->assertSame($servicio->nombre_servicio, $respuesta->json('resultados.0.titulo'));
        $this->get('/administracion/servicios?buscar=turis%20consul')->assertOk()->assertSee($servicio->nombre_servicio)->assertDontSee('Consultoría oculta');
        $this->getJson('/administracion/buscar?buscar=planeacion&ambito=servicios')->assertOk()->assertJsonCount(1, 'resultados');
        $this->getJson('/administracion/buscar?buscar=%25&ambito=servicios')->assertOk()->assertJsonCount(0, 'resultados');
        $this->getJson('/administracion/buscar?buscar=_&ambito=servicios')->assertOk()->assertJsonCount(0, 'resultados');
    }

    public function test_encuentra_cliente_por_apellidos_experiencia_o_folio_sin_perder_el_estado(): void
    {
        $this->autorizar();
        $venta = $this->venta();
        $consulta = http_build_query(['buscar' => 'val ruiz turis', 'ambito' => 'ventas', 'estado' => $venta->estado_reservacion_id]);
        $respuesta = $this->getJson('/administracion/buscar?'.$consulta)->assertOk()->assertJsonCount(1, 'resultados');
        $this->assertSame('Valentina Ruiz Ortega', $respuesta->json('resultados.0.titulo'));
        $this->assertSame($venta->folio, $respuesta->json('resultados.0.valor'));
        $this->assertStringEndsWith('/administracion/ventas/'.$venta->id, $respuesta->json('resultados.0.url'));
        $this->get('/administracion/ventas?buscar=ort%20VAL')->assertOk()->assertViewHas('ventas', fn ($ventas) => $ventas->count() === 1);
        $this->get('/administracion/ventas?buscar='.$venta->folio)->assertOk()->assertViewHas('ventas', fn ($ventas) => $ventas->first()->id === $venta->id);
        $cancelada = EstadoReservacion::create(['nombre_estado' => 'Cancelada']);
        $this->getJson('/administracion/buscar?ambito=ventas&buscar=val&estado='.$cancelada->id)->assertOk()->assertJsonCount(0, 'resultados');
        $this->get('/administracion/ventas?buscar=val&estado='.$cancelada->id)->assertOk()->assertViewHas('ventas', fn ($ventas) => $ventas->isEmpty());
    }

    public function test_guia_se_busca_por_formacion_talento_y_nombre_sin_incluir_otro_personal(): void
    {
        $this->autorizar();
        $persona = Persona::create(['nombre' => 'MARÍA', 'apellido_paterno' => 'Núñez', 'apellido_materno' => 'Ruiz']);
        DB::table('personal')->insert(['persona_id' => $persona->id, 'tipo_rol' => 'Guía', 'formacion' => 'Educación ambiental', 'talentos' => 'Observación de aves', 'estado_laboral' => 'Activo']);
        $otro = Persona::create(['nombre' => 'Otra persona', 'apellido_paterno' => 'Núñez']);
        DB::table('personal')->insert(['persona_id' => $otro->id, 'tipo_rol' => 'Contabilidad', 'talentos' => 'Observación de aves']);
        $this->getJson('/administracion/buscar?ambito=guias&buscar=maria%20nunez')->assertOk()->assertJsonCount(1, 'resultados');
        $this->getJson('/administracion/buscar?ambito=guias&buscar=aves')->assertOk()->assertJsonCount(1, 'resultados');
        $this->get('/administracion/guias?buscar=ambiental')->assertOk()->assertSee('MARÍA')->assertDontSee('Otra persona');
        $this->get('/administracion/guias?buscar=nada-coincide')->assertOk()->assertSee('No encontramos guías');
    }

    public function test_el_buscador_general_incluye_pagos_categorias_y_programaciones_sin_exponer_contactos(): void
    {
        $this->autorizar();
        $venta = $this->venta();
        $pago = Pago::create(['reservacion_id' => $venta->id, 'metodo_pago_id' => MetodoPago::create(['metodo' => 'Enlace'])->id,
            'monto' => '200.00', 'moneda' => 'MXN', 'estado' => 'pendiente', 'fecha_pago' => now(), 'referencia_comprobante' => 'CONSULTA-ABC', 'url_pago' => 'https://example.test/secreto']);
        $resultados = $this->getJson('/administracion/buscar?buscar=con')->assertOk()->json('resultados');
        foreach (['Servicio', 'Categoría', 'Programación', 'Pago'] as $tipo) $this->assertContains($tipo, array_column($resultados, 'tipo'));
        $respuesta = $this->getJson('/administracion/buscar?buscar=val')->assertOk();
        $this->assertContains('Pago', array_column($respuesta->json('resultados'), 'tipo'));
        $respuesta->assertDontSee('privado@example.test')->assertDontSee('5551234567')->assertDontSee('https://example.test/secreto');
        $this->getJson('/administracion/buscar?buscar=Pago%20%23'.$pago->id)->assertOk()->assertJsonFragment(['tipo' => 'Pago']);
    }

    public function test_limita_las_sugerencias_y_valida_consultas_incorrectas(): void
    {
        $this->autorizar();
        $categoria = Categoria::create(['nombre_categoria' => 'Rutas']);
        for ($n = 0; $n < 25; $n++) Servicio::create(['nombre_servicio' => 'Ruta '.$n, 'categoria_id' => $categoria->id, 'estado_servicio' => 'activo']);
        $this->getJson('/administracion/buscar?buscar=ruta&ambito=servicios')->assertOk()->assertJsonCount(8, 'resultados');
        $this->getJson('/administracion/buscar?buscar[]=x')->assertUnprocessable();
        $this->getJson('/administracion/buscar?buscar='.str_repeat('x', 101))->assertUnprocessable();
        $this->getJson('/administracion/buscar?ambito=usuarios')->assertUnprocessable();
        $this->getJson('/administracion/buscar?ambito=ventas&estado=activo')->assertUnprocessable();
        $this->getJson('/administracion/buscar?ambito=servicios&estado=1')->assertUnprocessable();
        $respuesta = $this->getJson('/administracion/buscar')->assertOk();
        $this->assertSame(['Apartado'], array_values(array_unique(array_column($respuesta->json('resultados'), 'tipo'))));
    }

    private function autorizar(): void
    {
        $usuario = User::factory()->create();
        config(['administracion.usuarios' => [$usuario->id]]);
        $this->actingAs($usuario);
    }

    private function venta(): Reservacion
    {
        $categoria = Categoria::create(['nombre_categoria' => 'Consultoría']);
        $servicio = Servicio::create(['categoria_id' => $categoria->id, 'nombre_servicio' => 'Consultoría turística', 'estado_servicio' => 'activo']);
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Valle', 'disponibilidad' => true]);
        $relacion = DB::table('servicio_regiones')->insertGetId(['servicio_id' => $servicio->id, 'region_id' => $region, 'cupo_maximo' => 20]);
        $programacion = Programacion::create(['servicio_reg_id' => $relacion, 'cupo_maximo' => 20, 'estado' => 'programada', 'fecha_inicio' => now()->addDays(3), 'fecha_fin' => now()->addDays(3)->addHours(3)]);
        $persona = Persona::create(['nombre' => 'Valentina', 'apellido_paterno' => 'Ruiz', 'apellido_materno' => 'Ortega', 'correo' => 'privado@example.test', 'telefono' => '5551234567']);
        $cliente = Cliente::create(['persona_id' => $persona->id, 'fecha_registro' => today()]);

        return Reservacion::create(['cliente_id' => $cliente->id, 'programacion_id' => $programacion->id,
            'estado_reservacion_id' => EstadoReservacion::create(['nombre_estado' => 'Pre-reserva'])->id,
            'numero_personas' => 2, 'fecha_solicitud' => now(), 'moneda' => 'MXN']);
    }
}
