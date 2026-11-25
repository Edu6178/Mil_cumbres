<?php

return [
    'compartir' => env('WHATSAPP_COMPARTIR', true),
    // El destino de prueba nunca se publica en el VPS ni reemplaza el teléfono del visitante.
    'pruebas' => env('WHATSAPP_PRUEBAS', false),
    'numero_prueba' => env('WHATSAPP_NUMERO_PRUEBA', ''),
    'habilitado' => env('WHATSAPP_API_HABILITADA', false),
    'token' => env('WHATSAPP_TOKEN', ''),
    'telefono_id' => env('WHATSAPP_TELEFONO_ID', ''),
    'version' => env('WHATSAPP_API_VERSION', 'v27.0'),
    'plantilla' => env('WHATSAPP_PLANTILLA', ''),
    'idioma' => env('WHATSAPP_IDIOMA', 'es_MX'),
];
