<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Programacion;
use Illuminate\Http\Request;

class ProgramacionController extends Controller
{
    /**
     * RF-10: Crear o modificar programaciones y cupos
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'servicio_reg_id' => 'required|exists:servicio_regiones,id',
            'cupo_maximo' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $programacion = Programacion::create(array_merge($validated, ['estado' => 'activo']));

        return response()->json([
            'message' => 'Programación creada exitosamente.',
            'data' => $programacion
        ], 201);
    }

    /**
     * RF-10: Bloquear periodos de calendario
     */
    public function bloquearPeriodo(Request $request, $id)
    {
        $programacion = Programacion::findOrFail($id);
        $programacion->update(['estado' => 'bloqueado']);

        return response()->json([
            'message' => 'Periodo bloqueado correctamente.',
            'data' => $programacion
        ]);
    }
}