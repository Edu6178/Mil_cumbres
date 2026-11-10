<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Programacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarioController extends Controller
{
    /**
     * RF-02: Consultar fechas y disponibilidad de cupos
     */
    public function consultarDisponibilidad(Request $request)
    {
        $servicioRegionId = $request->query('servicio_reg_id');

        $programaciones = Programacion::with('servicioRegion')
            ->where('estado', 'activo')
            ->when($servicioRegionId, function ($query, $servicioRegionId) {
                return $query->where('servicio_reg_id', $servicioRegionId);
            })
            ->get()
            ->map(function ($programacion) {
                return [
                    'id' => $programacion->id,
                    'fecha_inicio' => $programacion->fecha_inicio,
                    'fecha_fin' => $programacion->fecha_fin,
                    'cupo_maximo' => $programacion->cupo_maximo,
                    'cupos_disponibles' => $programacion->cupos_disponibles,
                    'disponible' => $programacion->cupos_disponibles > 0,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $programaciones
        ]);
    }

    /**
     * RF-02: Validación transaccional de cupo (Evita Overselling)
     */
    public function validarCupoTransaccional($programacionId, $cantidadPersonas)
    {
        return DB::transaction(function () use ($programacionId, $cantidadPersonas) {
            // Bloqueo pesimista de la fila para evitar que dos usuarios reserven al mismo tiempo
            $programacion = Programacion::where('id', $programacionId)
                ->lockForUpdate()
                ->first();

            if (!$programacion || $programacion->estado !== 'activo') {
                return false;
            }

            return $programacion->cupos_disponibles >= $cantidadPersonas;
        });
    }
}