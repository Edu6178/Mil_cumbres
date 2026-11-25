<?php

return [
    // Solo habilita enlaces manuales en local/pruebas. No es un transporte de mensajes.
    'pruebas' => env('WHATSAPP_PRUEBAS', false),
    'numero_prueba' => env('WHATSAPP_NUMERO_PRUEBA', ''),
];
