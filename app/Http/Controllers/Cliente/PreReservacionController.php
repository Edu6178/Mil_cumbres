<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EstadoReservacion;
use App\Models\Persona;
use App\Models\Programacion;
use App\Models\Reservacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreReservacionController extends Controller
{
    /**
     * Mostrar la página pública con las fechas disponibles (RF-02).
     */
    public function inicio()
    {
        $programaciones = Programacion::with([
            'servicioRegion.servicio',
            'servicioRegion.region',
            'reservaciones.estado',
        ])
            ->whereIn('estado', ['activo', 'programada'])
            ->whereHas('servicioRegion.region', fn ($q) => $q->where('disponibilidad', true))
            ->whereDate('fecha_inicio', '>=', today())
            ->orderBy('fecha_inicio')
            ->get()
            ->map(function ($programacion) {
                $ocupados = $programacion->reservaciones
                    ->filter(function ($reservacion) {
                        return in_array(
                            $reservacion->estado?->nombre_estado,
                            ['Pre-reserva', 'Confirmada']
                        );
                    })
                    ->sum('numero_personas');

                $programacion->cupos_disponibles = max(
                    0,
                    $programacion->cupo_maximo - $ocupados
                );

                return $programacion;
            })
            ->filter(fn ($programacion) => $programacion->cupos_disponibles > 0)
            ->values();

        return view('welcome', compact('programaciones'));
    }

    /**
     * Registrar una solicitud pública de pre-reservación con doble validación de cupo (RF-02 / RF-03).
     */
    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'programacion_id' => [
                'required',
                'integer',
                'exists:programaciones,id',
            ],
            'numero_personas' => [
                'required',
                'integer',
                'min:1',
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'telefono' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]{10,20}$/',
            ],
            'correo' => ['required', 'email', 'max:255'],
            'fecha_nacimiento' => [
                'required',
                'date',
                'before:today',
            ],
            'acompanantes' => ['array'],
            'acompanantes.*.nombre' => [
                'required',
                'string',
                'max:255',
            ],
            'acompanantes.*.apellido_paterno' => [
                'required',
                'string',
                'max:255',
            ],
            'acompanantes.*.apellido_materno' => [
                'nullable',
                'string',
                'max:255',
            ],
            'acompanantes.*.fecha_nacimiento' => [
                'required',
                'date',
                'before:today',
            ],
            'tipo_grupo' => ['nullable', 'string', 'max:100'],
            'proposito_visita' => ['nullable', 'string'],
            'personalizacion' => ['nullable', 'string'],
        ]);

        $acompanantes = $datos['acompanantes'] ?? [];

        if (count($acompanantes) !== $datos['numero_personas'] - 1) {
            throw ValidationException::withMessages([
                'acompanantes' =>
                    'Debes registrar los datos de cada acompañante. '
                    . 'El responsable ya está incluido en el grupo.',
            ]);
        }

        $reservacion = DB::transaction(function () use ($datos, $acompanantes) {
            $programacion = Programacion::whereKey($datos['programacion_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($programacion->estado, ['activo', 'programada']) || !($programacion->servicioRegion?->region?->disponibilidad ?? true)) {
                throw ValidationException::withMessages([
                    'programacion_id' => 'Esta fecha o territorio no está disponible actualmente.',
                ]);
            }

            $estadosQueOcupanCupo = ['Pre-reserva', 'Confirmada'];

            $cuposOcupados = Reservacion::query()
                ->where('programacion_id', $programacion->id)
                ->whereHas('estado', function ($query) use ($estadosQueOcupanCupo) {
                    $query->whereIn('nombre_estado', $estadosQueOcupanCupo);
                })
                ->sum('numero_personas');

            $cuposDisponibles = $programacion->cupo_maximo - $cuposOcupados;

            if ($cuposDisponibles < $datos['numero_personas']) {
                throw ValidationException::withMessages([
                    'numero_personas' =>
                        'No hay suficientes cupos disponibles para este grupo.',
                ]);
            }

            // Registrar los datos del responsable.
            $persona = Persona::create([
                'nombre' => $datos['nombre'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'apellido_materno' => $datos['apellido_materno'] ?? null,
                'telefono' => $datos['telefono'],
                'correo' => $datos['correo'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
            ]);

            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'fecha_registro' => now()->toDateString(),
            ]);

            // Crear el estado si todavía no existe.
            $estado = EstadoReservacion::firstOrCreate([
                'nombre_estado' => 'Pre-reserva',
            ]);

            // Registrar la pre-reserva.
            $reservacion = Reservacion::create([
                'cliente_id' => $cliente->id,
                'programacion_id' => $programacion->id,
                'estado_reservacion_id' => $estado->id,
                'fecha_solicitud' => now(),
                'numero_personas' => $datos['numero_personas'],
                'tipo_grupo' => $datos['tipo_grupo'] ?? null,
                'proposito_visita' => $datos['proposito_visita'] ?? null,
                'personalizacion' => $datos['personalizacion'] ?? null,
                'monto_total' => null,
            ]);

            // Registrar a los acompañantes.
            foreach ($acompanantes as $acompanante) {
                $personaInvitada = Persona::create([
                    'nombre' => $acompanante['nombre'],
                    'apellido_paterno' => $acompanante['apellido_paterno'],
                    'apellido_materno' => $acompanante['apellido_materno'] ?? null,
                    'fecha_nacimiento' => $acompanante['fecha_nacimiento'],
                ]);

                DB::table('asigna_reservacion_invitados')->insert([
                    'reservacion_id' => $reservacion->id,
                    'persona_id' => $personaInvitada->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $reservacion;
        });

        $folio = 'MC-' . now()->format('Y') . '-'
            . str_pad($reservacion->id, 6, '0', STR_PAD_LEFT);

        return redirect()->back()->with([
            'success' => 'Tu solicitud de pre-reservación fue registrada.',
            'folio' => $folio,
        ]);
    }
}