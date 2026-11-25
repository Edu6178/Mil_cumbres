<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Busqueda\BuscadorAdministrativo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusquedaController extends Controller
{
    public function index(Request $request, BuscadorAdministrativo $buscador)
    {
        $datos = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'ambito' => ['nullable', Rule::in(['todo', 'servicios', 'ventas', 'guias'])],
            'categoria' => ['nullable', 'integer', 'min:1'],
            'estado' => ['nullable', 'string', 'max:30'],
        ]);
        if (($datos['ambito'] ?? '') === 'ventas') {
            $request->validate(['estado' => ['nullable', 'integer', 'exists:estado_reservaciones,id']]);
        } elseif (($datos['ambito'] ?? '') === 'servicios') {
            $request->validate(['estado' => ['nullable', Rule::in(['activo', 'oculto'])]]);
        }
        $buscar = trim($datos['buscar'] ?? '');
        $resultados = $buscador->buscar($buscar, $datos['ambito'] ?? 'todo', $datos);

        if ($request->expectsJson()) return response()->json(['resultados' => $resultados]);

        return view('administracion.busqueda', compact('buscar', 'resultados'));
    }
}
