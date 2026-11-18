<?php

return [
    // Escenario académico: no describe la oferta ni al personal real de Mil Cumbres.
    'servicios' => [
        'pinos' => [
            'nombre_servicio' => 'Sendero del bosque de pinos', 'precio_base' => '450.00', 'duracion_estimada' => 120,
            'descripcion' => 'Una caminata tranquila entre pinos, claros de bosque y pequeños miradores. Pensada para disfrutar en familia y descubrir el paisaje a un ritmo accesible.',
            'itinerario' => "08:00 · Encuentro y bienvenida.\n08:15 · Revisión del recorrido y recomendaciones.\n08:30 · Caminata por el bosque y observación del entorno.\n09:30 · Descanso en el mirador.\n10:00 · Regreso y cierre.",
            'fotografia' => 'bosque-pinos', 'region' => 0, 'cupo' => 16, 'hora' => 8,
        ],
        'amanecer' => [
            'nombre_servicio' => 'Miradores al amanecer', 'precio_base' => '780.00', 'duracion_estimada' => 240,
            'descripcion' => 'Una salida temprana para caminar entre neblina y contemplar las primeras luces desde un mirador natural. Incluye pausas para fotografía y reconocimiento del paisaje.',
            'itinerario' => "06:00 · Bienvenida y preparación.\n06:20 · Ascenso acompañado.\n07:30 · Amanecer y pausa para fotografía.\n08:30 · Recorrido de interpretación del paisaje.\n10:00 · Regreso al punto de encuentro.",
            'fotografia' => 'amanecer', 'region' => 1, 'cupo' => 12, 'hora' => 6,
        ],
        'montana' => [
            'nombre_servicio' => 'Travesía de alta montaña', 'precio_base' => '1150.00', 'duracion_estimada' => 420,
            'descripcion' => 'Una jornada de mayor exigencia física con ascensos progresivos y vistas abiertas de la sierra. Recomendada para personas acostumbradas a caminar varias horas.',
            'itinerario' => "07:00 · Encuentro y revisión del equipo.\n07:30 · Inicio de la travesía.\n10:00 · Descanso e hidratación.\n11:00 · Mirador de la sierra.\n12:00 · Descenso por el circuito.\n14:00 · Cierre de la actividad.",
            'fotografia' => 'alta-montana', 'region' => 1, 'cupo' => 10, 'hora' => 7,
        ],
        'encinos' => [
            'nombre_servicio' => 'Caminata interpretativa entre encinos', 'precio_base' => '590.00', 'duracion_estimada' => 180,
            'descripcion' => 'Un recorrido para observar detalles que suelen pasar desapercibidos: hojas, sonidos, huellas y cambios del bosque. Una propuesta cercana para grupos escolares y familias.',
            'itinerario' => "09:00 · Presentación del grupo.\n09:20 · Caminata y observación del bosque.\n10:15 · Actividad de interpretación ambiental.\n11:00 · Descanso y conversación sobre el territorio.\n12:00 · Regreso.",
            'fotografia' => 'bosque-encinos', 'region' => 0, 'cupo' => 18, 'hora' => 9,
        ],
        'cascadas' => [
            'nombre_servicio' => 'Ruta de las cascadas', 'precio_base' => '950.00', 'duracion_estimada' => 360,
            'descripcion' => 'Una caminata entre vegetación y pequeños cauces hasta un paisaje de cascadas. Combina observación, descansos y tiempo para disfrutar del entorno.',
            'itinerario' => "08:00 · Recepción y recomendaciones.\n08:30 · Inicio del sendero.\n10:30 · Llegada al mirador de la cascada.\n11:30 · Descanso y refrigerio del grupo.\n12:00 · Recorrido de regreso.\n14:00 · Despedida.",
            'fotografia' => 'cascada', 'region' => 2, 'cupo' => 14, 'hora' => 8,
        ],
    ],
    'regiones' => ['Bosques de Valle de Bravo', 'Sierra del Mirador', 'Cuenca de los Encinos'],
    'descripciones_regiones' => [
        'Bosques de pino y encino, senderos de pendiente suave y claros para la observación del paisaje.',
        'Relieve montañoso con ascensos progresivos y miradores abiertos hacia la sierra.',
        'Senderos junto a cauces de agua, vegetación de ribera y miradores de cascadas.',
    ],
    'guias' => [
        'maria' => [
            'nombre' => 'María Elena', 'apellido_paterno' => 'Castillo', 'apellido_materno' => 'Rojas', 'fecha_nacimiento' => '1967-04-18',
            'formacion' => 'Interpretación ambiental, conducción de grupos y acompañamiento de caminatas de bajo impacto.',
            'talentos' => 'Observación de flora, narración de historias del paisaje y actividades para familias.',
            'virtudes' => 'Escucha paciente, trato cercano y atención a los distintos ritmos del grupo.',
            'especialidad' => 'Bosques e interpretación ambiental', 'fotografia' => 'guia-maria',
            'presentacion' => 'Disfruta compartir las pequeñas historias del bosque y acompañar a quienes descubren el senderismo por primera vez.',
        ],
        'luis' => [
            'nombre' => 'Luis Alberto', 'apellido_paterno' => 'Andrade', 'apellido_materno' => 'Soto', 'fecha_nacimiento' => '1988-09-07',
            'formacion' => 'Orientación en campo, planeación de recorridos y primeros auxilios para actividades al aire libre.',
            'talentos' => 'Lectura del terreno, fotografía de paisaje y organización de travesías.',
            'virtudes' => 'Comunicación clara, serenidad y atención al cuidado del grupo.',
            'especialidad' => 'Senderismo de montaña', 'fotografia' => 'guia-luis',
            'presentacion' => 'Le gusta construir recorridos con pausas para observar, aprender a leer el terreno y disfrutar de una buena conversación.',
        ],
        'sofia' => [
            'nombre' => 'Sofía', 'apellido_paterno' => 'Mendoza', 'apellido_materno' => 'Vargas', 'fecha_nacimiento' => '1995-02-24',
            'formacion' => 'Educación ambiental, facilitación de grupos y diseño de actividades de observación.',
            'talentos' => 'Dinámicas para grupos escolares, observación de aves y dibujo de naturaleza.',
            'virtudes' => 'Curiosidad, empatía y facilidad para involucrar a todo el grupo.',
            'especialidad' => 'Experiencias educativas', 'fotografia' => 'guia-sofia',
            'presentacion' => 'Propone mirar el territorio con curiosidad y convertir una caminata en un espacio para aprender en conjunto.',
        ],
    ],
    'comunidades' => [
        'encinal' => ['nombre_comunidad' => 'El Encinal', 'region' => 0, 'descripcion' => 'Entre bosques de pino y encino, los recorridos invitan a conocer los saberes del territorio y las prácticas de cuidado que se comparten de generación en generación.'],
        'san-miguel' => ['nombre_comunidad' => 'San Miguel del Bosque', 'region' => 2, 'descripcion' => 'Una comunidad cercana a los cauces de la cuenca, donde caminar se acompaña de conversaciones sobre el agua, el paisaje y los acuerdos para cuidar el entorno.'],
    ],
    // Créditos y licencias de las fotografías descargadas; no representan a los guías ni los lugares reales.
    'fotografias' => [
        'bosque-pinos' => ['archivo' => 'bosque-pinos.jpg', 'autor' => 'Annie Spratt', 'fuente' => 'https://unsplash.com/photos/a-path-in-the-middle-of-a-pine-forest-4sQOBPXVSws', 'licencia' => 'https://unsplash.com/license'],
        'amanecer' => ['archivo' => 'amanecer.jpg', 'autor' => 'Eric Goverde', 'fuente' => 'https://www.pexels.com/photo/sun-setting-over-the-mountains-4045714/', 'licencia' => 'https://www.pexels.com/license/'],
        'alta-montana' => ['archivo' => 'alta-montana.jpg', 'autor' => 'Masi', 'fuente' => 'https://www.pexels.com/photo/backpackers-hiking-in-mountains-15766937/', 'licencia' => 'https://www.pexels.com/license/'],
        'bosque-encinos' => ['archivo' => 'bosque-encinos.jpg', 'autor' => 'Damir Korotaj', 'fuente' => 'https://unsplash.com/photos/a-dirt-path-winds-through-a-sunlit-pine-forest-oc4J9koEdv8', 'licencia' => 'https://unsplash.com/license'],
        'cascada' => ['archivo' => 'cascada.jpg', 'autor' => 'Andreas Berget', 'fuente' => 'https://www.pexels.com/photo/tranquil-forest-waterfall-in-lush-landscape-39900402/', 'licencia' => 'https://www.pexels.com/license/'],
        'guia-maria' => ['archivo' => 'guia-maria.jpg', 'autor' => 'Kampus Production', 'fuente' => 'https://www.pexels.com/photo/a-portrait-of-an-elderly-woman-hiking-7787502/', 'licencia' => 'https://www.pexels.com/license/'],
        'guia-luis' => ['archivo' => 'guia-luis.jpg', 'autor' => 'Omar Tapia', 'fuente' => 'https://www.pexels.com/photo/man-hiking-in-the-scenic-mountains-of-mexico-36262095/', 'licencia' => 'https://www.pexels.com/license/'],
        'guia-sofia' => ['archivo' => 'guia-sofia.jpg', 'autor' => 'Pixabay', 'fuente' => 'https://www.pexels.com/photo/woman-wearing-black-spaghetti-strap-top-415829/', 'licencia' => 'https://www.pexels.com/license/'],
    ],
];
