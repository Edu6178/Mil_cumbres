<?php

// Mensajes utilizados por los formularios de acceso y registro.
return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser un texto válido.',
    'email' => 'Escribe un correo electrónico válido.',
    'confirmed' => 'Las contraseñas no coinciden.',
    'unique' => 'Este correo ya tiene una cuenta.',
    'max' => [
        'string' => 'El campo :attribute no debe superar :max caracteres.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'array' => 'No selecciones más de :max elementos.',
    ],
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
    ],
    'integer' => 'El campo :attribute debe ser un número entero.',
    'numeric' => 'El campo :attribute debe ser un número válido.',
    'decimal' => 'El campo :attribute admite hasta dos decimales.',
    'exists' => 'El elemento seleccionado en :attribute ya no está disponible.',
    'in' => 'Selecciona una opción válida en :attribute.',
    'array' => 'La selección de :attribute no es válida.',
    'distinct' => 'No repitas elementos en :attribute.',
    'password' => [
        'letters' => 'La contraseña debe contener al menos una letra.',
        'numbers' => 'La contraseña debe contener al menos un número.',
    ],
    'attributes' => [
        'nombre' => 'nombre',
        'correo' => 'correo electrónico',
        'contrasena' => 'contraseña',
        'contrasena_confirmation' => 'confirmación de contraseña',
        'nombre_servicio' => 'nombre del servicio',
        'nombre_categoria' => 'nombre de la categoría',
        'categoria_id' => 'categoría',
        'precio_base' => 'precio base',
        'duracion_estimada' => 'duración estimada',
        'estado_servicio' => 'estado del servicio',
        'descripcion' => 'descripción',
        'itinerario' => 'itinerario',
        'guias' => 'guías',
        'guias.*' => 'guía',
    ],
];
