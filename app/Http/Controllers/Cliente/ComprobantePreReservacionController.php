<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Reservacion;
use Barryvdh\DomPDF\Facade\Pdf;

class ComprobantePreReservacionController extends Controller
{
    public function descargar(int $reservacion)
    {
        // La firma del enlace se valida antes de consultar los datos privados de la solicitud.
        $solicitud = Reservacion::with([
            'cliente.persona', 'acompanantes', 'estado',
            'programacion.servicioRegion.servicio', 'programacion.servicioRegion.region',
        ])->findOrFail($reservacion);

        // El documento usa recursos locales y texto escapado; no ejecuta código ni descarga imágenes externas.
        return Pdf::loadView('pdf.pre-reservacion', [
            'solicitud' => $solicitud,
            'simbolo' => 'data:image/svg+xml;base64,'.base64_encode(file_get_contents(public_path('imagenes/simbolo.svg'))),
        ])->setPaper('a4')->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'isFontSubsettingEnabled' => true,
        ])->download('Pre-reserva-'.$solicitud->folio.'.pdf')->withHeaders([
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
