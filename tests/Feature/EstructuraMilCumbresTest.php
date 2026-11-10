<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EstructuraMilCumbresTest extends TestCase
{
    // Las pruebas usan la base de pruebas y revierten sus registros al terminar.
    use RefreshDatabase;

    public function test_registra_una_reservacion_con_invitados_y_pagos(): void
    {
        $programacion = $this->crearProgramacion();
        $persona = DB::table('personas')->insertGetId([
            'nombre' => 'María', 'apellido_paterno' => 'López',
        ]);
        $cliente = DB::table('clientes')->insertGetId([
            'persona_id' => $persona, 'fecha_registro' => '2026-11-10',
        ]);
        $estado = DB::table('estado_reservaciones')->insertGetId(['nombre_estado' => 'Pendiente']);
        $reservacion = DB::table('reservaciones')->insertGetId([
            'cliente_id' => $cliente,
            'programacion_id' => $programacion,
            'estado_reservacion_id' => $estado,
            'fecha_solicitud' => '2026-11-10 10:00:00',
            'numero_personas' => 1,
            'monto_total' => '1250.50',
        ]);
        DB::table('asigna_reservacion_invitados')->insert([
            'reservacion_id' => $reservacion, 'persona_id' => $persona,
        ]);
        $metodo = DB::table('metodo_pago')->insertGetId(['metodo' => 'Transferencia']);
        DB::table('pagos')->insert([
            'reservacion_id' => $reservacion,
            'metodo_pago_id' => $metodo,
            'monto' => '1250.50',
            'fecha_pago' => '2026-11-10 11:00:00',
            'referencia_comprobante' => 'COMPROBANTE-001',
        ]);

        // Comprueba el recorrido completo desde la programación hasta el pago.
        $this->assertDatabaseHas('reservaciones', ['id' => $reservacion, 'cliente_id' => $cliente]);
        $this->assertDatabaseHas('asigna_reservacion_invitados', [
            'reservacion_id' => $reservacion, 'persona_id' => $persona,
        ]);
        $this->assertDatabaseHas('pagos', ['reservacion_id' => $reservacion, 'monto' => '1250.50']);
    }

    public function test_no_permite_repetir_un_servicio_en_la_misma_region(): void
    {
        $this->crearProgramacion();
        $relacion = DB::table('servicio_regiones')->first();

        $this->expectException(QueryException::class);

        DB::table('servicio_regiones')->insert([
            'servicio_id' => $relacion->servicio_id,
            'region_id' => $relacion->region_id,
            'cupo_maximo' => 20,
        ]);
    }

    public function test_impide_eliminar_una_region_que_tiene_servicios(): void
    {
        $this->crearProgramacion();
        $region = DB::table('servicio_regiones')->value('region_id');

        $this->expectException(QueryException::class);

        DB::table('regiones')->where('id', $region)->delete();
    }

    public function test_rechaza_una_programacion_sin_servicio_regional_existente(): void
    {
        $this->expectException(QueryException::class);

        DB::table('programaciones')->insert([
            'servicio_reg_id' => 999999,
            'cupo_maximo' => 20,
            'fecha_inicio' => '2026-11-20 09:00:00',
            'fecha_fin' => '2026-11-20 12:00:00',
        ]);
    }

    public function test_conserva_el_cliente_cuando_se_elimina_su_organizacion(): void
    {
        $tipo = DB::table('tipo_organizacion')->insertGetId(['descripcion' => 'Escuela']);
        $organizacion = DB::table('organizacion')->insertGetId([
            'nombre_organizacion' => 'Escuela de prueba', 'tipo_organizacion_id' => $tipo,
        ]);
        $persona = DB::table('personas')->insertGetId([
            'nombre' => 'José', 'apellido_paterno' => 'Pérez',
        ]);
        $cliente = DB::table('clientes')->insertGetId([
            'persona_id' => $persona,
            'organizacion_id' => $organizacion,
            'fecha_registro' => '2026-11-10',
        ]);

        DB::table('organizacion')->where('id', $organizacion)->delete();

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente, 'persona_id' => $persona, 'organizacion_id' => null,
        ]);
    }

    // Prepara un servicio regional con fechas para probar sus relaciones.
    private function crearProgramacion(): int
    {
        $categoria = DB::table('categorias')->insertGetId(['nombre_categoria' => 'Senderismo']);
        $servicio = DB::table('servicios')->insertGetId([
            'categoria_id' => $categoria,
            'nombre_servicio' => 'Recorrido por el bosque',
            'precio_base' => '1250.50',
        ]);
        $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Mil Cumbres']);
        $servicioRegional = DB::table('servicio_regiones')->insertGetId([
            'servicio_id' => $servicio, 'region_id' => $region, 'cupo_maximo' => 20,
        ]);

        return DB::table('programaciones')->insertGetId([
            'servicio_reg_id' => $servicioRegional,
            'cupo_maximo' => 20,
            'fecha_inicio' => '2026-11-20 09:00:00',
            'fecha_fin' => '2026-11-20 12:00:00',
        ]);
    }
}
