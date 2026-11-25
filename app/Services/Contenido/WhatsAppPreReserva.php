<?php

namespace App\Services\Contenido;

use App\Models\Reservacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class WhatsAppPreReserva
{
    public function configurado(): bool
    {
        return config('whatsapp.habilitado') && filled(config('whatsapp.token'))
            && preg_match('/^[0-9]+$/D', (string) config('whatsapp.telefono_id'))
            && preg_match('/^v[0-9]+\.[0-9]+$/D', (string) config('whatsapp.version'))
            && preg_match('/^[a-z0-9_]+$/D', (string) config('whatsapp.plantilla'))
            && preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/D', (string) config('whatsapp.idioma'));
    }

    // Se invoca únicamente después de crear esta solicitud. No hay barrido de reservas anteriores.
    public function enviarNueva(Reservacion $solicitud, bool $consentimiento, string $resumen): string
    {
        if (! $consentimiento) return 'sin_solicitar';
        $estado = $this->configurado() ? 'enviando' : 'no_configurado';
        $tomada = DB::table('reservaciones')->where('id', $solicitud->id)->whereNull('whatsapp_intentado_at')
            ->update(['whatsapp_intentado_at' => now(), 'whatsapp_consentido_at' => now(), 'whatsapp_estado' => $estado]);
        if (! $tomada) return (string) $solicitud->fresh()->whatsapp_estado;
        if ($estado === 'no_configurado') return $estado;

        $solicitud->loadMissing(['cliente.persona', 'programacion.servicioRegion.servicio']);
        $numero = NumeroWhatsApp::normalizar($solicitud->cliente->persona->telefono ?? '');
        if (! $numero) return $this->resultado($solicitud, 'fallido', 'numero_invalido');
        $texto = fn ($valor) => Str::limit(preg_replace('/\s+/u', ' ', (string) $valor), 200, '');
        $valores = [$solicitud->cliente->persona->nombre, $solicitud->folio,
            $solicitud->programacion->servicioRegion->servicio->nombre_servicio,
            $solicitud->programacion->fecha_inicio->format('d/m/Y H:i'), (string) $solicitud->numero_personas];
        $base = rtrim(url('/'), '/').'/';
        if (! str_starts_with($resumen, $base)) return $this->resultado($solicitud, 'fallido', 'enlace_invalido');
        $cuerpo = ['messaging_product' => 'whatsapp', 'to' => $numero, 'type' => 'template',
            'template' => ['name' => config('whatsapp.plantilla'), 'language' => ['code' => config('whatsapp.idioma')],
                'components' => [
                    ['type' => 'body', 'parameters' => array_map(fn ($v) => ['type' => 'text', 'text' => $texto($v)], $valores)],
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [
                        ['type' => 'text', 'text' => substr($resumen, strlen($base))],
                    ]],
                ]]];
        try {
            // Un solo intento, sin reintentos automáticos que puedan duplicar un mensaje aceptado.
            $respuesta = Http::withToken(config('whatsapp.token'))->acceptJson()->connectTimeout(3)->timeout(8)
                ->post('https://graph.facebook.com/'.config('whatsapp.version').'/'.config('whatsapp.telefono_id').'/messages', $cuerpo);
            $id = $respuesta->json('messages.0.id');
            if ($respuesta->successful() && is_string($id) && $id !== '') {
                DB::table('reservaciones')->where('id', $solicitud->id)->update(['whatsapp_estado' => 'aceptado', 'whatsapp_mensaje_id' => substr($id, 0, 255)]);
                return 'aceptado';
            }
            $codigo = $respuesta->json('error.code');
            return $this->resultado($solicitud, 'fallido', is_int($codigo) ? (string) $codigo : 'respuesta_invalida');
        } catch (Throwable) {
            // No registra tokens, cuerpos del mensaje ni excepciones que puedan incluir datos del cliente.
            return $this->resultado($solicitud, 'fallido', 'conexion');
        }
    }

    private function resultado(Reservacion $solicitud, string $estado, string $codigo): string
    {
        DB::table('reservaciones')->where('id', $solicitud->id)->update(['whatsapp_estado' => $estado, 'whatsapp_error_codigo' => $codigo]);
        return $estado;
    }
}
