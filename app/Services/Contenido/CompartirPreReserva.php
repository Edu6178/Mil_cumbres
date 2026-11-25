<?php

namespace App\Services\Contenido;

use App\Models\Reservacion;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\URL;

class CompartirPreReserva
{
    public function preparar(Reservacion $solicitud, CarbonInterface $vence): array
    {
        if (!config('whatsapp.compartir')) {
            return [];
        }

        $solicitud->loadMissing(['cliente.persona', 'programacion.servicioRegion.servicio']);
        $numero = self::normalizarNumero($solicitud->cliente->persona->telefono ?? '');
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
        $enlaces = ['resumen' => $resumen, 'mensaje' => $mensaje, 'destino' => $numero,
            'propio' => $numero ? 'https://wa.me/'.$numero.'?text='.rawurlencode($mensaje) : null,
            'elegir' => 'https://wa.me/?text='.rawurlencode($mensaje)];
        $prueba = self::normalizarNumero((string) config('whatsapp.numero_prueba'));
        if (config('whatsapp.pruebas') && app()->environment('local', 'testing') && $prueba) {
            $enlaces['prueba'] = 'https://wa.me/'.$prueba.'?text='.rawurlencode($mensaje);
        }
        return $enlaces;
    }

    public static function normalizarNumero(string $numero): ?string
    {
        $numero = preg_replace('/\D/', '', $numero);
        if (strlen($numero) === 10) $numero = '52'.$numero;
        // El prefijo mexicano actual no lleva el antiguo 1 después de 52.
        if (strlen($numero) === 13 && str_starts_with($numero, '521')) $numero = '52'.substr($numero, 3);
        return preg_match('/^[1-9][0-9]{10,14}$/D', $numero) ? $numero : null;
    }
}
