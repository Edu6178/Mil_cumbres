<?php

// Compatibilidad y arranque de los roles provisionales. Los roles revisados se guardan en users.
// Solo se aceptan identificadores concretos de users; el registro público nunca modifica esta lista.
$usuarios = [];
foreach (explode(',', (string) env('ADMIN_USUARIOS', '')) as $identificador) {
    if (ctype_digit(trim($identificador)) && (int) $identificador > 0) {
        $usuarios[] = (int) $identificador;
    }
}

return ['usuarios' => array_values(array_unique($usuarios))];
