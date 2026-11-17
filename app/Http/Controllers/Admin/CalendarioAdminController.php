<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\EstadoReservacion;
use App\Models\Persona;
use App\Models\Programacion;
use App\Models\Region;
use App\Models\Reservacion;
use App\Models\ServicioRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarioAdminController extends Controller
{
    public function index(Request $request)
    {
        $programaciones = Programacion::with(['servicioRegion.servicio', 'servicioRegion.region', 'reservaciones.estado'])
            ->orderByDesc('fecha_inicio')
            ->paginate(12);

        $programaciones->getCollection()->transform(function ($prog) {
            $ocupados = $prog->reservaciones
                ->filter(fn ($r) => in_array($r->estado?->nombre_estado, ['Pre-reserva', 'Confirmada']))
                ->sum('numero_personas');
            $prog->cupos_ocupados = $ocupados;
            $prog->cupos_disponibles = max(0, $prog->cupo_maximo - $ocupados);
            return $prog;
        });

        $serviciosRegiones = ServicioRegion::with(['servicio', 'region'])
            ->whereHas('servicio', fn ($q) => $q->where('estado_servicio', 'activo'))
            ->get();

        $regiones = Region::orderBy('nombre_region')->get();
        $programacionEdicion = new Programacion();

        return view('administracion.calendario.index', compact(
            'programaciones',
            'serviciosRegiones',
            'regiones',
            'programacionEdicion'
        ));
    }

    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'servicio_reg_id' => ['required', 'exists:servicio_regiones,id'],
            'cupo_maximo' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
        ]);

        // Validar que no exceda el cupo máximo configurado para ese servicio en esa región
        $servicioRegion = ServicioRegion::findOrFail($datos['servicio_reg_id']);
        if ($datos['cupo_maximo'] > $servicioRegion->cupo_maximo) {
            return back()
                ->withInput()
                ->withErrors(['cupo_maximo' => "El cupo no puede superar el límite territorial ({$servicioRegion->cupo_maximo} personas)."]);
        }

        Programacion::create([
            'servicio_reg_id' => $datos['servicio_reg_id'],
            'cupo_maximo' => $datos['cupo_maximo'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'estado' => 'programada',
        ]);

        return redirect()->route('administracion.calendario.indice')
            ->with('correcto', 'Programación registrada en el calendario.');
    }

    public function editar(Programacion $programacion)
    {
        $programaciones = Programacion::with(['servicioRegion.servicio', 'servicioRegion.region', 'reservaciones.estado'])
            ->orderByDesc('fecha_inicio')
            ->paginate(12);

        $programaciones->getCollection()->transform(function ($prog) {
            $ocupados = $prog->reservaciones
                ->filter(fn ($r) => in_array($r->estado?->nombre_estado, ['Pre-reserva', 'Confirmada']))
                ->sum('numero_personas');
            $prog->cupos_ocupados = $ocupados;
            $prog->cupos_disponibles = max(0, $prog->cupo_maximo - $ocupados);
            return $prog;
        });

        $serviciosRegiones = ServicioRegion::with(['servicio', 'region'])->get();
        $regiones = Region::orderBy('nombre_region')->get();
        $programacionEdicion = $programacion;

        return view('administracion.calendario.index', compact(
            'programaciones',
            'serviciosRegiones',
            'regiones',
            'programacionEdicion'
        ));
    }

    public function actualizar(Request $request, Programacion $programacion)
    {
        $datos = $request->validate([
            'servicio_reg_id' => ['required', 'exists:servicio_regiones,id'],
            'cupo_maximo' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'estado' => ['required', 'in:programada,bloqueada,cancelada'],
        ]);

        $programacion->update($datos);

        return redirect()->route('administracion.calendario.indice')
            ->with('correcto', 'Programación actualizada correctamente.');
    }

    /**
     * RF-10: Bloquear o desbloquear una fecha/programación puntual
     */
    public function alternarBloqueo(Programacion $programacion)
    {
        $nuevoEstado = $programacion->estado === 'bloqueada' ? 'programada' : 'bloqueada';
        $programacion->update(['estado' => $nuevoEstado]);

        return redirect()->route('administracion.calendario.indice')
            ->with('correcto', $nuevoEstado === 'bloqueada'
                ? 'Fecha bloqueada: no se admitirán nuevas solicitudes en este horario.'
                : 'Fecha habilitada nuevamente en el calendario.');
    }

    /**
     * RF-10: Bloquear o habilitar un territorio/región completa
     */
    public function alternarBloqueoRegion(Region $region)
    {
        $region->update(['disponibilidad' => !$region->disponibilidad]);

        return redirect()->route('administracion.calendario.indice')
            ->with('correcto', $region->disponibilidad
                ? "Territorio '{$region->nombre_region}' habilitado."
                : "Territorio '{$region->nombre_region}' bloqueado temporalmente.");
    }

    /**
     * RF-15: Vista de sincronización e ingreso de reservas de canales externos (Booking, TripAdvisor)
     */
    public function canalesExternos()
    {
        $programacionesDisponibles = Programacion::with(['servicioRegion.servicio', 'servicioRegion.region', 'reservaciones.estado'])
            ->where('estado', 'programada')
            ->whereHas('servicioRegion.region', fn ($q) => $q->where('disponibilidad', true))
            ->whereDate('fecha_inicio', '>=', today())
            ->orderBy('fecha_inicio')
            ->get()
            ->map(function ($prog) {
                $ocupados = $prog->reservaciones
                    ->filter(fn ($r) => in_array($r->estado?->nombre_estado, ['Pre-reserva', 'Confirmada']))
                    ->sum('numero_personas');
                $prog->cupos_disponibles = max(0, $prog->cupo_maximo - $ocupados);
                return $prog;
            })
            ->filter(fn ($p) => $p->cupos_disponibles > 0);

        $reservasExternas = Reservacion::with(['cliente.persona', 'programacion.servicioRegion.servicio', 'estado'])
            ->whereNotNull('personalizacion')
            ->where('personalizacion', 'like', 'CANAL_EXTERNO:%')
            ->latest()
            ->paginate(10);

        return view('administracion.calendario.canales', compact('programacionesDisponibles', 'reservasExternas'));
    }

    /**
     * RF-15: Guardar reserva externa validando cupo transaccional y evitando duplicidades por referencia
     */
    public function registrarExterna(Request $request)
    {
        $datos = $request->validate([
            'canal_origen' => ['required', 'in:Booking,TripAdvisor,Airbnb,Otro'],
            'referencia_externa' => ['required', 'string', 'max:100'],
            'programacion_id' => ['required', 'exists:programaciones,id'],
            'numero_personas' => ['required', 'integer', 'min:1'],
            'nombre' => ['required', 'string', 'max:120'],
            'apellido_paterno' => ['required', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'monto_total' => ['nullable', 'numeric', 'min:0'],
        ]);

        $marcaReferencia = "CANAL_EXTERNO:{$datos['canal_origen']}|REF:{$datos['referencia_externa']}";

        // Evitar duplicidades de la misma referencia externa
        $yaExiste = Reservacion::where('personalizacion', 'like', "%|REF:{$datos['referencia_externa']}%")->exists();
        if ($yaExiste) {
            return back()->withInput()->withErrors([
                'referencia_externa' => "La referencia {$datos['referencia_externa']} ya fue incorporada anteriormente.",
            ]);
        }

        DB::transaction(function () use ($datos, $marcaReferencia) {
            $programacion = Programacion::whereKey($datos['programacion_id'])->lockForUpdate()->firstOrFail();

            if ($programacion->estado !== 'programada' || !($programacion->servicioRegion?->region?->disponibilidad ?? true)) {
                throw ValidationException::withMessages([
                    'programacion_id' => 'La fecha o territorio seleccionado se encuentra bloqueado.',
                ]);
            }

            $ocupados = Reservacion::where('programacion_id', $programacion->id)
                ->whereHas('estado', fn ($q) => $q->whereIn('nombre_estado', ['Pre-reserva', 'Confirmada']))
                ->sum('numero_personas');

            if (($programacion->cupo_maximo - $ocupados) < $datos['numero_personas']) {
                throw ValidationException::withMessages([
                    'numero_personas' => 'No hay cupos globales suficientes para incorporar esta reserva externa.',
                ]);
            }

            $persona = Persona::create([
                'nombre' => $datos['nombre'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'telefono' => $datos['telefono'] ?? null,
            ]);

            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'fecha_registro' => now()->toDateString(),
            ]);

            $estado = EstadoReservacion::firstOrCreate(['nombre_estado' => 'Confirmada']);

            $reservacion = Reservacion::create([
                'cliente_id' => $cliente->id,
                'programacion_id' => $programacion->id,
                'estado_reservacion_id' => $estado->id,
                'fecha_solicitud' => now(),
                'numero_personas' => $datos['numero_personas'],
                'tipo_grupo' => 'Canal Externo (' . $datos['canal_origen'] . ')',
                'personalizacion' => $marcaReferencia,
                'monto_total' => $datos['monto_total'] ?? null,
            ]);

            DB::table('bitacora_organizacion')->insert([
                'reservacion_id' => $reservacion->id,
                'fecha_sincronizacion' => now(),
                'dispositivo_origen' => 'Sincronización ' . $datos['canal_origen'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('administracion.canales.indice')
            ->with('correcto', 'Reserva externa incorporada y cupo actualizado correctamente.');
    }
}