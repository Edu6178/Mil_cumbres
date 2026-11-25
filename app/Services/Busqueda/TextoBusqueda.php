<?php

namespace App\Services\Busqueda;

use Illuminate\Support\Str;

class TextoBusqueda
{
    public static function normalizar(string $texto): string
    {
        return Str::lower(Str::ascii(trim($texto)));
    }

    public static function palabras(string $texto): array
    {
        return array_values(array_unique(preg_split('/\s+/u', self::normalizar($texto), -1, PREG_SPLIT_NO_EMPTY)));
    }

    public static function coincide(string $texto, string $buscar): bool
    {
        $texto = self::normalizar($texto);
        foreach (self::palabras($buscar) as $palabra) {
            if (! str_contains($texto, $palabra)) return false;
        }

        return true;
    }

    // Columnas internas, valores enlazados y comodines literales. Funciona en PostgreSQL y SQLite sin extensiones.
    public static function filtrar($consulta, array $columnas, string $buscar): void
    {
        foreach (self::palabras($buscar) as $palabra) {
            $patron = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $palabra).'%';
            $consulta->where(function ($grupo) use ($columnas, $patron) {
                foreach ($columnas as $columna) {
                    $expresion = 'COALESCE('.$grupo->getGrammar()->wrap($columna).", '')";
                    foreach (['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
                        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n'] as $de => $a) {
                        $expresion = "REPLACE($expresion, '$de', '$a')";
                    }
                    $grupo->orWhereRaw("LOWER($expresion) LIKE ? ESCAPE '!'", [$patron]);
                }
            });
        }
    }
}
