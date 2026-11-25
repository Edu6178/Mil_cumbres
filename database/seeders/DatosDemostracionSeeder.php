<?php

namespace Database\Seeders;

use App\Services\Demostracion\RegistroDemostracion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatosDemostracionSeeder extends Seeder
{
    private array $registro = ['version' => 1, 'ids' => [], 'creados' => []];

    public function run(): void
    {
        $registro = app(RegistroDemostracion::class);
        if (!$registro->permitida()) {
            throw new RuntimeException('Los datos ficticios requieren un entorno de pruebas o DEMOSTRACION_HABILITADA=true.');
        }

        if ($registro->leer()) {
            // Completa la unidad de las cinco rutas de prueba sin cambiar precios ni acuerdos existentes.
            foreach ($registro->leer()['ids']['servicios'] ?? [] as $clave => $id) {
                DB::table('servicios')->where('id', $id)->where('nombre_servicio', config('demostracion.servicios.'.$clave.'.nombre_servicio'))
                    ->whereNull('unidad_cobro')->update(['unidad_cobro' => 'persona']);
            }
            $this->command?->info('La demostración ya está cargada; se conservaron sus datos y modificaciones.');

            return;
        }

        DB::transaction(function () use ($registro) {
            $categoria = $this->catalogo('categorias', 'senderismo', ['nombre_categoria' => 'Senderismo']);
            foreach (['Pre-reserva', 'Confirmada', 'Cancelada', 'Rechazada'] as $estado) {
                $this->catalogo('estado_reservaciones', $estado, ['nombre_estado' => $estado]);
            }
            foreach (config('demostracion.regiones') as $indice => $nombre) {
                $this->insertar('regiones', (string) $indice, ['nombre_region' => $nombre, 'descripcion' => config('demostracion.descripciones_regiones.'.$indice), 'disponibilidad' => true]);
            }
            foreach (config('demostracion.servicios') as $clave => $datos) {
                $servicio = $this->insertar('servicios', $clave, [
                    ...Arr::only($datos, ['nombre_servicio', 'descripcion', 'itinerario', 'precio_base', 'duracion_estimada']),
                    'categoria_id' => $categoria, 'estado_servicio' => 'activo', 'unidad_cobro' => 'persona', 'tipo_precio' => 'fijo', 'moneda' => 'MXN',
                ]);
                $relacion = $this->insertar('servicio_regiones', $clave, ['servicio_id' => $servicio, 'region_id' => $this->id('regiones', (string) $datos['region']), 'cupo_maximo' => $datos['cupo']]);
                foreach ([0, 1] as $semana) {
                    $dia = array_search($clave, array_keys(config('demostracion.servicios')), true) + 2 + $semana * 7;
                    $inicio = now()->startOfDay()->addDays($dia)->addHours($datos['hora']);
                    $this->insertar('programaciones', $clave.'-'.$semana, [
                        'servicio_reg_id' => $relacion, 'cupo_maximo' => $datos['cupo'], 'fecha_inicio' => $inicio,
                        'fecha_fin' => $inicio->copy()->addMinutes($datos['duracion_estimada']),
                        'estado' => $clave === 'encinos' && $semana === 1 ? 'bloqueada' : 'programada',
                    ]);
                }
            }
            foreach (config('demostracion.guias') as $clave => $datos) {
                $persona = $this->insertar('personas', 'guia-'.$clave, [...Arr::only($datos, ['nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento']), 'correo' => $clave.'@demostracion.milcumbres.test']);
                $this->insertar('personal', $clave, [...Arr::only($datos, ['formacion', 'talentos', 'virtudes']), 'persona_id' => $persona, 'tipo_rol' => 'guia', 'estado_laboral' => 'activo']);
            }
            foreach (config('demostracion.comunidades') as $clave => $datos) {
                $this->insertar('comunidades', $clave, ['nombre_comunidad' => $datos['nombre_comunidad'], 'regiones_id' => $this->id('regiones', (string) $datos['region'])]);
            }
            $guias = array_keys(config('demostracion.guias'));
            foreach (array_keys($this->registro['ids']['programaciones']) as $indice => $clave) {
                if ($clave !== 'encinos-1') {
                    $this->insertar('asigna_guias', $clave, ['programacion_id' => $this->id('programaciones', $clave), 'personal_id' => $this->id('personal', $guias[$indice % 3]), 'fecha_asignacion' => now()->subDay(), 'disponibilidad' => true]);
                }
            }

            $clientes = [
                ['Valeria', 'Torres', 'Mendoza'], ['Diego', 'Salazar', 'Vega'], ['Mariana', 'Rojas', 'León'], ['Andrés', 'Castillo', 'Núñez'],
                ['Renata', 'Lara', 'Fuentes'], ['Gabriel', 'Luna', 'Estrada'], ['Paula', 'Alonso', 'Vidal'], ['Santiago', 'Romero', 'Silva'],
            ];
            foreach ($clientes as $indice => [$nombre, $paterno, $materno]) {
                $persona = $this->insertar('personas', 'cliente-'.$indice, [
                    'nombre' => $nombre, 'apellido_paterno' => $paterno, 'apellido_materno' => $materno,
                    'telefono' => '000000'.str_pad((string) ($indice + 1), 4, '0', STR_PAD_LEFT),
                    'correo' => 'cliente'.($indice + 1).'@demostracion.milcumbres.test', 'fecha_nacimiento' => (1986 + $indice).'-05-18',
                ]);
                $this->insertar('clientes', (string) $indice, ['persona_id' => $persona, 'fecha_registro' => now()->subDays(15)->toDateString()]);
            }
            $reservas = [
                ['pinos-0', 0, 'Confirmada', 4, 'Familiar'], ['pinos-0', 1, 'Pre-reserva', 3, 'Amigos'],
                ['amanecer-0', 2, 'Confirmada', 5, 'Amigos'], ['amanecer-1', 3, 'Pre-reserva', 2, 'Familiar'],
                ['montana-0', 4, 'Confirmada', 8, 'Empresarial'], ['montana-0', 5, 'Pre-reserva', 2, 'Amigos'],
                ['montana-1', 6, 'Confirmada', 4, 'Amigos'], ['encinos-0', 7, 'Pre-reserva', 6, 'Escolar'],
                ['encinos-0', 0, 'Cancelada', 6, 'Familiar'], ['cascadas-0', 1, 'Confirmada', 6, 'Amigos'],
                ['cascadas-1', 2, 'Pre-reserva', 4, 'Familiar'], ['cascadas-1', 3, 'Rechazada', 2, 'Amigos'],
            ];
            $metodo = $this->catalogo('metodo_pago', 'demostracion', ['metodo' => 'Transferencia bancaria']);
            $participantes = [['Lucía', 'Cruz'], ['Daniel', 'Herrera'], ['Regina', 'Vargas'], ['Emiliano', 'Sánchez'], ['Isabel', 'Morales'], ['Matías', 'Pérez'], ['Camila', 'Navarro'], ['Sebastián', 'Reyes']];
            $numeroInvitado = 0;
            foreach ($reservas as $indice => [$programa, $cliente, $estado, $personas, $grupo]) {
                $claveServicio = explode('-', $programa)[0];
                $total = round((float) config('demostracion.servicios.'.$claveServicio.'.precio_base') * $personas, 2);
                $reserva = $this->insertar('reservaciones', (string) $indice, [
                    'cliente_id' => $this->id('clientes', (string) $cliente), 'programacion_id' => $this->id('programaciones', $programa),
                    'estado_reservacion_id' => $this->id('estado_reservaciones', $estado), 'fecha_solicitud' => now()->subDays(8)->addHours($indice),
                    'numero_personas' => $personas, 'tipo_grupo' => $grupo, 'proposito_visita' => $grupo === 'Escolar' ? 'Reconocimiento del bosque y observación de la naturaleza.' : 'Convivencia y recorrido acompañado en la naturaleza.',
                    'personalizacion' => 'Ritmo tranquilo y pausas para disfrutar del paisaje.', 'monto_total' => $total,
                ]);
                for ($invitado = 1; $invitado < $personas; $invitado++) {
                    [$nombre, $apellido] = $participantes[$numeroInvitado % count($participantes)];
                    $numeroInvitado++;
                    $persona = $this->insertar('personas', 'invitado-'.$numeroInvitado, ['nombre' => $nombre, 'apellido_paterno' => $apellido, 'apellido_materno' => 'Ramírez', 'fecha_nacimiento' => ($grupo === 'Escolar' ? '2014' : '1998').'-03-12']);
                    $this->insertar('asigna_reservacion_invitados', 'invitado-'.$numeroInvitado, ['reservacion_id' => $reserva, 'persona_id' => $persona]);
                }
                if ($estado === 'Confirmada') {
                    // Son importes de ejemplo; no se generan enlaces ni se contacta a un proveedor de cobros.
                    $this->insertar('pagos', (string) $indice, ['reservacion_id' => $reserva, 'metodo_pago_id' => $metodo, 'monto' => $indice % 2 === 0 ? $total : $total / 2,
                        'fecha_pago' => now()->subDays(2), 'referencia_comprobante' => 'MC-PAGO-'.str_pad((string) $indice, 3, '0', STR_PAD_LEFT), 'proveedor' => 'Registro manual']);
                }
            }

            // El registro privado conserva solamente los identificadores del lote para su retiro posterior.
            $this->registro['fecha_carga'] = now()->toIso8601String();
            $registro->guardar($this->registro);
        });
        $this->command?->info('Demostración cargada: 5 rutas, 3 guías, 10 programaciones, 8 clientes y 12 reservaciones ficticias.');
    }

    private function insertar(string $tabla, string $clave, array $datos): int
    {
        $id = DB::table($tabla)->insertGetId([...$datos, 'created_at' => now(), 'updated_at' => now()]);
        $this->registro['ids'][$tabla][$clave] = $id;
        $this->registro['creados'][] = ['tabla' => $tabla, 'id' => $id, 'creado_en' => DB::table($tabla)->where('id', $id)->value('created_at')];

        return $id;
    }

    private function catalogo(string $tabla, string $clave, array $datos): int
    {
        $existente = DB::table($tabla)->where($datos)->value('id');
        if ($existente) {
            $this->registro['ids'][$tabla][$clave] = $existente;

            return $existente;
        }

        return $this->insertar($tabla, $clave, $datos);
    }

    private function id(string $tabla, string $clave): int
    {
        return $this->registro['ids'][$tabla][$clave];
    }
}
