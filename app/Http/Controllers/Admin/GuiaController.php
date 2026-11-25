<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Administracion\AgendaGuias;
use App\Services\Busqueda\TextoBusqueda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuiaController extends Controller
{
    public function index(Request $request): View
    {
        $datos = $request->validate(['buscar' => ['nullable', 'string', 'max:100']]);
        $consulta = AgendaGuias::guias();
        TextoBusqueda::filtrar($consulta, ['personas.nombre', 'personas.apellido_paterno', 'personas.apellido_materno', 'personal.formacion', 'personal.talentos', 'personal.tipo_rol'], $datos['buscar'] ?? '');

        return view('administracion.guias', ['guias' => $consulta->orderBy('personas.nombre')->paginate(12)->withQueryString()]);
    }

    public function programaciones(): View
    {
        return view('administracion.programaciones', ['programaciones' => AgendaGuias::programaciones()->orderByDesc('fecha_inicio')->paginate(12)]);
    }

    public function editar(int $programacion): View
    {
        return view('administracion.asignacion', [
            'actividad' => AgendaGuias::programaciones()->where('programaciones.id', $programacion)->firstOrFail(),
            'guias' => AgendaGuias::guias()->orderBy('personas.nombre')->get(),
            'seleccionados' => DB::table('asigna_guias')->where('programacion_id', $programacion)
                ->where('disponibilidad', true)->pluck('personal_id')->all(),
        ]);
    }

    public function guardar(Request $request, int $programacion, AgendaGuias $agenda): RedirectResponse
    {
        $datos = $request->validate([
            'guias' => ['sometimes', 'array', 'max:100'],
            'guias.*' => ['required', 'integer', 'distinct', 'exists:personal,id'],
        ]);
        $agenda->asignar($programacion, $datos['guias'] ?? []);

        return redirect()->route('administracion.programaciones')->with('estado', 'La asignación de guías se guardó correctamente.');
    }
}
