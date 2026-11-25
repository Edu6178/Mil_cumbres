<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EstadoReservacion;
use App\Models\Persona;
use App\Models\Programacion;
use App\Models\Reservacion;
use App\Services\Demostracion\PresentacionDemostracion;
use App\Services\Demostracion\RegistroDemostracion;
use App\Models\Servicio;
use App\Services\Finanzas\TarifaReserva;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class PreReservacionController extends Controller
{
    /**
     * Mostrar la página pública con las fechas disponibles (RF-02).
     */
    public function inicio()
    {
        $programaciones = collect();
        $serviciosPublicos = collect();
        $guiasPublicos = collect();
        $fechasCalendario = collect();
        $disponibilidadInterrumpida = false;

        try {
            $fechas = Programacion::with([
                'servicioRegion.servicio', 'servicioRegion.region', 'reservaciones.estado',
            ])
                ->whereIn('estado', ['activo', 'programada', 'bloqueada'])
                ->whereHas('servicioRegion.servicio', fn ($query) => $query->where('estado_servicio', 'activo'))
                ->where('fecha_inicio', '>', now())
                ->orderBy('fecha_inicio')
                ->get();
            $programaciones = $fechas->filter(fn ($fecha) => in_array($fecha->estado, ['activo', 'programada'], true)
                && ($fecha->servicioRegion?->region?->disponibilidad ?? false) && $fecha->cupos_disponibles > 0)
                ->values();
            // Solo se publica disponibilidad; nunca datos de viajeros ni motivos internos del bloqueo.
            $fechasCalendario = $fechas->map(fn ($fecha) => [
                'id' => $fecha->id, 'dia' => $fecha->fecha_inicio->format('Y-m-d'),
                'fin' => $fecha->fecha_fin->format('Y-m-d'), 'hora' => $fecha->fecha_inicio->format('H:i'),
                'nombre' => $fecha->servicioRegion?->servicio?->nombre_servicio,
                'region' => $fecha->servicioRegion?->region?->nombre_region,
                'cupos' => $fecha->cupos_disponibles,
                'estado' => $fecha->estado === 'bloqueada' || !($fecha->servicioRegion?->region?->disponibilidad ?? false)
                    ? 'bloqueada' : ($fecha->cupos_disponibles > 0 ? 'disponible' : 'completa'),
            ])->values();
            $registro = app(RegistroDemostracion::class);
            if ($registro->activo()) {
                $presentacion = app(PresentacionDemostracion::class);
                $serviciosPublicos = $presentacion->servicios($registro);
                $guiasPublicos = $presentacion->perfiles($registro);
            }
        } catch (QueryException $error) {
            // La información pública permanece disponible, sin confundir una falla con un calendario vacío.
            report($error);
            $disponibilidadInterrumpida = true;
        }

        return view('welcome', compact('programaciones', 'fechasCalendario', 'disponibilidadInterrumpida', 'serviciosPublicos', 'guiasPublicos'));
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
            'nombre' => ['required', 'string', 'max:120'],
            'apellido_paterno' => ['nullable', 'string', 'max:120'],
            'apellido_materno' => ['nullable', 'string', 'max:120'],
            'telefono' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]{10,20}$/',
            ],
            'correo' => ['nullable', 'email', 'max:255'],
            'edad' => ['nullable', 'required_without:fecha_nacimiento', 'integer', 'between:0,120'],
            'fecha_nacimiento' => [
                'nullable', 'required_without:edad',
                'date',
                'before:today',
            ],
            'acompanantes' => ['array'],
            'acompanantes.*.nombre' => [
                'required',
                'string',
                'max:120',
            ],
            'acompanantes.*.apellido_paterno' => [
                'nullable',
                'string',
                'max:120',
            ],
            'acompanantes.*.apellido_materno' => [
                'nullable',
                'string',
                'max:120',
            ],
            'acompanantes.*.fecha_nacimiento' => [
                'nullable', 'required_without:acompanantes.*.edad',
                'date',
                'before:today',
            ],
            'acompanantes.*.edad' => ['nullable', 'required_without:acompanantes.*.fecha_nacimiento', 'integer', 'between:0,120'],
            'tipo_grupo' => ['nullable', 'string', 'max:100'],
            'proposito_visita' => ['nullable', 'string'],
            'personalizacion' => ['nullable', 'string'],
            'whatsapp_acepta' => ['sometimes', 'boolean'],
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

            if (!in_array($programacion->estado, ['activo', 'programada'], true)
                || !$programacion->fecha_inicio->isFuture()
                || $programacion->servicioRegion?->servicio?->estado_servicio !== 'activo'
                || !($programacion->servicioRegion?->region?->disponibilidad ?? false)) {
                throw ValidationException::withMessages([
                    'programacion_id' => 'Esta fecha o territorio no está disponible actualmente.',
                ]);
            }

            $cuposDisponibles = $programacion->cupos_disponibles;

            if ($cuposDisponibles < $datos['numero_personas']) {
                throw ValidationException::withMessages([
                    'numero_personas' =>
                        'No hay suficientes cupos disponibles para este grupo.',
                ]);
            }

            // El navegador no decide el importe. Se lee y conserva la tarifa vigente en el servidor.
            $servicio = Servicio::whereKey($programacion->servicioRegion->servicio_id)->sharedLock()->firstOrFail();
            if ($servicio->estado_servicio !== 'activo') {
                throw ValidationException::withMessages(['programacion_id' => 'Esta experiencia ya no está disponible.']);
            }
            $tarifa = TarifaReserva::calcular($servicio, $datos['numero_personas']);

            // Registrar los datos del responsable.
            $persona = Persona::create([
                'nombre' => $datos['nombre'],
                'apellido_paterno' => $datos['apellido_paterno'] ?? '',
                'apellido_materno' => $datos['apellido_materno'] ?? null,
                'telefono' => $datos['telefono'],
                'correo' => $datos['correo'] ?? null,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
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
                'edad_responsable' => $datos['edad'] ?? null,
                'tipo_grupo' => $datos['tipo_grupo'] ?? null,
                'proposito_visita' => $datos['proposito_visita'] ?? null,
                'personalizacion' => $datos['personalizacion'] ?? null,
                'monto_total' => $tarifa['importe'], 'moneda' => $tarifa['moneda'],
                'tarifa_publicada' => $tarifa['precio'], 'importe_publicado' => $tarifa['importe'],
                'unidad_cobro' => $tarifa['unidad'], 'tipo_precio' => $tarifa['tipo'], 'moneda_publicada' => $tarifa['moneda'],
            ]);

            // Registrar a los acompañantes.
            foreach ($acompanantes as $acompanante) {
                $personaInvitada = Persona::create([
                    'nombre' => $acompanante['nombre'],
                    'apellido_paterno' => $acompanante['apellido_paterno'] ?? '',
                    'apellido_materno' => $acompanante['apellido_materno'] ?? null,
                    'fecha_nacimiento' => $acompanante['fecha_nacimiento'] ?? null,
                ]);

                DB::table('asigna_reservacion_invitados')->insert([
                    'reservacion_id' => $reservacion->id,
                    'persona_id' => $personaInvitada->id,
                    'edad' => $acompanante['edad'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $reservacion;
        });

        // La descarga se habilita después de guardar y no permite consultar otros folios cambiando el enlace.
        $vencimiento = now()->addDay();
        $compartir = app(\App\Services\Contenido\CompartirPreReserva::class)->preparar($reservacion, $vencimiento);
        $resumen = URL::temporarySignedRoute('pre-reservaciones.resumen', $vencimiento, ['reservacion' => $reservacion->id]);
        $estadoWhatsApp = app(\App\Services\Contenido\WhatsAppPreReserva::class)->enviarNueva($reservacion, (bool) ($datos['whatsapp_acepta'] ?? false), $resumen);
        $request->session()->put('ultima_pre_reserva', [
            'folio' => $reservacion->folio,
            'pdf' => URL::temporarySignedRoute('pre-reservaciones.pdf', $vencimiento, ['reservacion' => $reservacion->id]),
            'vence' => $vencimiento->timestamp,
            'whatsapp' => $compartir,
            'whatsapp_estado' => $estadoWhatsApp,
        ]);

        return redirect()->to(rtrim(route('inicio'), '/').'/#pre-reservacion')->with([
            'success' => 'Tu solicitud de pre-reservación fue registrada.',
            'folio' => $reservacion->folio,
        ]);
    }
}
