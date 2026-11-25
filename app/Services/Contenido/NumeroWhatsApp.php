<?php

namespace App\Services\Contenido;

class NumeroWhatsApp
{
    public static function normalizar(string $numero): ?string
    {
        $numero = preg_replace('/\D/', '', $numero);
        if (strlen($numero) === 10) $numero = '52'.$numero;
        // El prefijo mexicano actual no lleva el antiguo 1 después de 52.
        if (strlen($numero) === 13 && str_starts_with($numero, '521')) $numero = '52'.substr($numero, 3);
        return preg_match('/^[1-9][0-9]{10,14}$/D', $numero) ? $numero : null;
    }
}
