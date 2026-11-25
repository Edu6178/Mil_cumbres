<?php

return [
    'habilitado' => env('WHATSAPP_API_HABILITADA', false),
    'token' => env('WHATSAPP_TOKEN', ''),
    'telefono_id' => env('WHATSAPP_TELEFONO_ID', ''),
    'version' => env('WHATSAPP_API_VERSION', 'v27.0'),
    'plantilla' => env('WHATSAPP_PLANTILLA', ''),
    'idioma' => env('WHATSAPP_IDIOMA', 'es_MX'),
];
