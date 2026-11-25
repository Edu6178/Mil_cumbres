<?php

namespace App\Http\Controllers;

use App\Services\Administracion\AgendaGuias;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $personal = $request->user()->personal_id;
        $actividades = AgendaGuias::programaciones()->where('fecha_fin', '>=', now())
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('asigna_guias')
                ->whereColumn('asigna_guias.programacion_id', 'programaciones.id')
                ->where('personal_id', $personal ?? 0)->where('disponibilidad', true))
            ->orderBy('fecha_inicio')->paginate(12);
        return view('equipo.inicio', compact('actividades', 'personal'));
    }
}
