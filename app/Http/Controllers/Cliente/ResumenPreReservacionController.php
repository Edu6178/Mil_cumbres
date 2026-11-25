<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Reservacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ResumenPreReservacionController extends Controller
{
    public function ver(Request $request, int $reservacion)
    {
        $solicitud = Reservacion::with(['cliente.persona', 'estado', 'programacion.servicioRegion.servicio', 'programacion.servicioRegion.region'])
            ->findOrFail($reservacion);
        // Consultar el resumen no confirma la reserva ni cambia su estado o sus pagos.
        $pdf = URL::temporarySignedRoute('pre-reservaciones.pdf', Carbon::createFromTimestamp((int) $request->query('expires')), ['reservacion' => $solicitud->id]);

        return response()->view('publico.resumen-pre-reserva', compact('solicitud', 'pdf'))->withHeaders([
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
