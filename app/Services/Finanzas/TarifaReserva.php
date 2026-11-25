<?php

namespace App\Services\Finanzas;

use App\Models\Servicio;
use Illuminate\Validation\ValidationException;

final class TarifaReserva
{
    public const UNIDADES = ['persona' => 'por persona', 'grupo' => 'por grupo', 'servicio' => 'por servicio'];

    public static function calcular(?Servicio $servicio, int $personas): array
    {
        $precio = $servicio?->precio_base;
        $unidad = $servicio?->unidad_cobro;
        $total = null;
        if ($precio !== null && isset(self::UNIDADES[$unidad]) && $personas > 0) {
            $centavos = Dinero::centavos($precio);
            $multiplicador = $unidad === 'persona' ? $personas : 1;
            if ($centavos > intdiv(999999999999, $multiplicador)) {
                throw ValidationException::withMessages(['numero_personas' => 'El total del grupo supera el importe admitido. Contacta a Mil Cumbres para cotizarlo.']);
            }
            $total = Dinero::decimal($centavos * $multiplicador);
        }

        return ['precio' => $precio, 'importe' => $total, 'unidad' => $unidad,
            'tipo' => $servicio?->tipo_precio ?? 'fijo', 'moneda' => $servicio?->moneda ?? 'MXN'];
    }
}
