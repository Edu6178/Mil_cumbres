<?php

namespace App\Services\Finanzas;

final class Dinero
{
    public const MONEDAS = ['MXN', 'USD', 'EUR'];

    // Opera en centavos para que los abonos no acumulen errores de punto flotante.
    public static function centavos(string|int|null $importe): int
    {
        [$entero, $decimal] = array_pad(explode('.', (string) ($importe ?? '0'), 2), 2, '');

        return (int) $entero * 100 + (int) str_pad(substr($decimal, 0, 2), 2, '0');
    }

    public static function decimal(int $centavos): string
    {
        return intdiv($centavos, 100).'.'.str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function mostrar(int $centavos): string
    {
        return number_format(intdiv($centavos, 100), 0, '.', ',').'.'.str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
