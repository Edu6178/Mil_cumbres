<?php

// Mensajes utilizados por los formularios de acceso y registro.
return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser un texto válido.',
    'email' => 'Escribe un correo electrónico válido.',
    'confirmed' => 'Las contraseñas no coinciden.',
    'unique' => 'Este correo ya tiene una cuenta.',
    'max' => ['string' => 'El campo :attribute no debe superar :max caracteres.'],
    'min' => ['string' => 'El campo :attribute debe tener al menos :min caracteres.'],
    'password' => [
        'letters' => 'La contraseña debe contener al menos una letra.',
        'numbers' => 'La contraseña debe contener al menos un número.',
    ],
    'attributes' => [
        'nombre' => 'nombre',
        'correo' => 'correo electrónico',
        'contrasena' => 'contraseña',
        'contrasena_confirmation' => 'confirmación de contraseña',
    ],
];
