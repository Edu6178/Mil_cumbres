<?php

namespace App\Services\Contenido;

use App\Models\Reservacion;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\URL;

class CompartirPreReserva
{
    public function preparar(Reservacion $solicitud, CarbonInterface $vence): array
    {
        if (!config('whatsapp.pruebas') || !app()->environment('local', 'testing')) {
            return [];
        }

        $numero = preg_replace('/\D/', '', (string) config('whatsapp.numero_prueba'));
        if (strlen($numero) === 10) $numero = '52'.$numero;
        if (!preg_match('/^[1-9][0-9]{10,14}$/', $numero)) return [];

        $solicitud->loadMissing(['cliente.persona', 'programacion.servicioRegion.servicio']);
        $nombre = trim($solicitud->cliente->persona->nombre);
        $experiencia = $solicitud->programacion->servicioRegion->servicio->nombre_servicio;
        $resumen = URL::temporarySignedRoute('pre-reservaciones.resumen', $vence, ['reservacion' => $solicitud->id]);
        $mensaje = "🌿 Hola, {$nombre}. ¡Tu próxima experiencia empieza a tomar forma!\n\n"
            ."Recibimos tu solicitud en Mil Cumbres.\n"
            ."Folio: {$solicitud->folio}\n"
            ."Experiencia: {$experiencia}\n"
            .'Fecha: '.$solicitud->programacion->fecha_inicio->format('d/m/Y · H:i')." hrs\n"
            ."Grupo: {$solicitud->numero_personas} persona(s)\n\n"
            ."Revisa tu pre-reserva, descarga el comprobante y conoce los siguientes pasos aquí:\n{$resumen}\n\n"
            ."Tu solicitud está pendiente de confirmación por el equipo. Los medios de pago están por confirmar.\n"
            ."El enlace es personal y vence en 24 horas.\n\nMenos prisa. Más conexión. 🍃";

        // Crear el enlace no envía nada: el usuario elige el chat y pulsa Enviar en WhatsApp.
        return ['resumen' => $resumen, 'mensaje' => $mensaje, 'destino' => $numero,
            'prueba' => 'https://wa.me/'.$numero.'?text='.rawurlencode($mensaje),
            'elegir' => 'https://wa.me/?text='.rawurlencode($mensaje)];
    }
}
