<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\EstadoReservacion;
use App\Models\Reservacion;
use App\Models\Servicio;
use App\Services\Demostracion\RegistroDemostracion;
use App\Services\Finanzas\Dinero;
use App\Services\Finanzas\TarifaReserva;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class EscenarioPropuestaSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new RuntimeException('El escenario de la propuesta solo se carga en local o pruebas.');
        }
        $ruta = app(RegistroDemostracion::class)->ruta().'.propuesta.json';
        if (File::exists($ruta)) {
            $this->command?->info('El escenario ya existe; se conservaron sus cambios.');
            return;
        }
        $this->call(CatalogoClienteSeeder::class);

        DB::transaction(function () use ($ruta) {
            // Datos exclusivamente ficticios para la propuesta, separados de las tarifas publicadas en Wix.
            $escenarios = [
                ['Avistamiento de aves', 'Experiencias turísticas', '650.00', 'persona', 180, 16, 3, 'Pre-reserva', 'Camila Fuentes Nava', 29],
                ['Terapia de naturaleza', 'Terapia de naturaleza', '900.00', 'persona', 120, 8, 2, 'Pre-reserva', 'Rodrigo Paredes Luna', 34],
                ['Actividades para equipos', 'Regeneración organizacional', '7500.00', 'grupo', 240, 18, 8, 'Confirmada', 'Daniela Cortés Salinas', 31],
                ['Pedagogía de naturaleza', 'Pedagogía de naturaleza', '420.00', 'persona', 180, 20, 4, 'Confirmada', 'Javier Morales Rivas', 38],
                ['Ecovoluntariado', 'Ecovoluntariado', null, null, 240, 10, 2, 'Pre-reserva', 'Valentina Ruiz Ortega', 26],
            ];
            $lote = ['fecha_carga' => now()->toIso8601String(), 'reservaciones' => [], 'programaciones' => [], 'tarifas_anteriores' => []];
            $region = DB::table('regiones')->insertGetId(['nombre_region' => 'Bosques de La Cumbre', 'descripcion' => 'Senderos y claros de bosque para encuentros al aire libre.', 'disponibilidad' => true, 'created_at' => now(), 'updated_at' => now()]);
            $lote['region'] = $region;
            $usuario = DB::table('users')->whereIn('id', config('administracion.usuarios', []))->value('id');
            $metodo = DB::table('metodo_pago')->where('metodo', 'Transferencia bancaria')->value('id')
                ?? DB::table('metodo_pago')->insertGetId(['metodo' => 'Transferencia bancaria', 'created_at' => now(), 'updated_at' => now()]);
            foreach ($escenarios as $indice => [$nombre, $categoria, $precio, $unidad, $duracion, $cupo, $personas, $estado, $cliente, $edad]) {
                $servicio = Servicio::where('categoria_id', Categoria::where('nombre_categoria', $categoria)->value('id'))->where('nombre_servicio', $nombre)->firstOrFail();
                $lote['tarifas_anteriores'][$servicio->id] = $servicio->only(['precio_base', 'unidad_cobro', 'tipo_precio', 'duracion_estimada']);
                if ($servicio->precio_base === null) {
                    $servicio->update(['precio_base' => $precio, 'unidad_cobro' => $unidad, 'tipo_precio' => 'fijo', 'moneda' => 'MXN', 'duracion_estimada' => $duracion]);
                }
                $tarifa = TarifaReserva::calcular($servicio, $personas);
                $relacion = DB::table('servicio_regiones')->insertGetId(['servicio_id' => $servicio->id, 'region_id' => $region, 'cupo_maximo' => $cupo, 'created_at' => now(), 'updated_at' => now()]);
                $inicio = now()->startOfDay()->addDays(17 + $indice)->addHours(9);
                $programacion = DB::table('programaciones')->insertGetId(['servicio_reg_id' => $relacion, 'cupo_maximo' => $cupo, 'fecha_inicio' => $inicio, 'fecha_fin' => $inicio->copy()->addMinutes($duracion), 'estado' => 'programada', 'created_at' => now(), 'updated_at' => now()]);
                $lote['programaciones'][] = $programacion;
                $persona = DB::table('personas')->insertGetId(['nombre' => $cliente, 'apellido_paterno' => '', 'telefono' => '00000002'.str_pad((string) $indice, 2, '0', STR_PAD_LEFT), 'created_at' => now(), 'updated_at' => now()]);
                $clienteId = DB::table('clientes')->insertGetId(['persona_id' => $persona, 'fecha_registro' => now()->subDays(3)->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
                $confirmada = $estado === 'Confirmada' && $tarifa['importe'] !== null;
                $reserva = Reservacion::create([
                    'cliente_id' => $clienteId, 'programacion_id' => $programacion, 'numero_personas' => $personas, 'edad_responsable' => $edad,
                    'estado_reservacion_id' => EstadoReservacion::firstOrCreate(['nombre_estado' => $confirmada ? 'Confirmada' : 'Pre-reserva'])->id,
                    'tipo_grupo' => $indice === 2 ? 'Empresarial' : 'Amigos', 'proposito_visita' => 'Compartir una experiencia y conocer el entorno natural.',
                    'fecha_solicitud' => now()->subDays(3), 'moneda' => $tarifa['moneda'], 'monto_total' => $tarifa['importe'],
                    'tarifa_publicada' => $tarifa['precio'], 'importe_publicado' => $tarifa['importe'], 'tipo_precio' => $tarifa['tipo'],
                    'unidad_cobro' => $tarifa['unidad'], 'moneda_publicada' => $tarifa['moneda'],
                    'fecha_confirmacion' => $confirmada ? now()->subDays(2) : null, 'confirmada_por' => $confirmada ? $usuario : null,
                ]);
                $lote['reservaciones'][] = $reserva->id;
                $nombres = ['Lucía Herrera', 'Emiliano Soto', 'Renata Vega', 'Gabriel Campos', 'Isabel Duarte', 'Mateo Lozano', 'Sofía Navarro'];
                for ($i = 0; $i < $personas - 1; $i++) {
                    $invitado = DB::table('personas')->insertGetId(['nombre' => $nombres[$i], 'apellido_paterno' => '', 'created_at' => now(), 'updated_at' => now()]);
                    DB::table('asigna_reservacion_invitados')->insert(['reservacion_id' => $reserva->id, 'persona_id' => $invitado, 'edad' => 21 + $i, 'created_at' => now(), 'updated_at' => now()]);
                }
                if ($confirmada) {
                    $cobro = $indice === 2 ? Dinero::decimal(intdiv(Dinero::centavos($tarifa['importe']), 2)) : $tarifa['importe'];
                    DB::table('pagos')->insert(['reservacion_id' => $reserva->id, 'metodo_pago_id' => $metodo, 'monto' => $cobro, 'moneda' => $tarifa['moneda'], 'estado' => 'confirmado', 'fecha_pago' => now()->subDay(), 'fecha_confirmacion' => now()->subDay(), 'confirmado_por' => $usuario, 'proveedor' => 'Registro manual', 'referencia_comprobante' => 'MC-PROPUESTA-'.$reserva->id, 'created_at' => now(), 'updated_at' => now()]);
                }
                if ($indice === 0) {
                    $lote['programaciones'][] = DB::table('programaciones')->insertGetId(['servicio_reg_id' => $relacion, 'cupo_maximo' => $cupo, 'fecha_inicio' => $inicio->copy()->addDays(7), 'fecha_fin' => $inicio->copy()->addDays(7)->addMinutes($duracion), 'estado' => 'bloqueada', 'created_at' => now(), 'updated_at' => now()]);
                }
            }
            File::ensureDirectoryExists(dirname($ruta));
            if (File::put($ruta, json_encode($lote, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), true) === false) {
                throw new RuntimeException('No se pudo registrar el escenario local.');
            }
        });
    }
}
