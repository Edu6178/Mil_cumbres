<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Services\Administracion\AgendaGuias;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View
    {
        $estadosReservacion = DB::table('estado_reservaciones as estados')
            ->leftJoin('reservaciones', 'reservaciones.estado_reservacion_id', '=', 'estados.id')
            ->select('estados.id', 'estados.nombre_estado')->selectRaw('COUNT(reservaciones.id) as total')
            ->groupBy('estados.id', 'estados.nombre_estado')->orderBy('estados.id')->get();

        // Los indicadores provienen de registros reales; una falla de conexión no se presenta como cero.
        return view('administracion.inicio', [
            'indicadores' => [
                'servicios' => Servicio::where('estado_servicio', 'activo')->count(),
                'categorias' => Categoria::count(),
                'guias' => AgendaGuias::guias()->count(),
                'programaciones' => DB::table('programaciones')->count(),
                'reservaciones' => DB::table('reservaciones')->count(),
            ],
            'estadosReservacion' => $estadosReservacion,
            'serviciosOcultos' => Servicio::where('estado_servicio', 'oculto')->count(),
            'serviciosRecientes' => Servicio::with('categoria')->latest()->limit(5)->get(),
            'programacionesProximas' => AgendaGuias::programaciones()
                ->where('fecha_fin', '>=', now())->orderBy('fecha_inicio')->limit(4)->get(),
        ]);
    }

    public function asistencia(): View
    {
        // Las tablas actuales no tienen un campo para persistir asistencia individual.
        return view('administracion.asistencia', [
            'programaciones' => AgendaGuias::programaciones()->orderByDesc('fecha_inicio')->paginate(12),
        ]);
    }

    public function participantes(int $programacion): View
    {
        $actividad = AgendaGuias::programaciones()->where('programaciones.id', $programacion)->firstOrFail();

        // No se muestran notas médicas ni información ajena a la lista operativa.
        $participantes = DB::table('asigna_reservacion_invitados as invitados')
            ->join('reservaciones', 'reservaciones.id', '=', 'invitados.reservacion_id')
            ->join('personas', 'personas.id', '=', 'invitados.persona_id')
            ->where('reservaciones.programacion_id', $programacion)
            ->select('invitados.id', 'reservaciones.id as reservacion', 'personas.nombre', 'personas.apellido_paterno', 'personas.apellido_materno')
            ->orderBy('personas.apellido_paterno')->paginate(30);

        return view('administracion.participantes', compact('actividad', 'participantes'));
    }
}
