<?php

$configuracion = json_decode(file_get_contents(__DIR__.'/fotografias-empresa.json'), true, 512, JSON_THROW_ON_ERROR);
$configuracion['espacios'] += [
    'comunidades-relato' => ['nombre' => 'Comunidades · Fotografía del camino', 'foto' => 'empresa-1795396108398'],
    'comunidades-detalle' => ['nombre' => 'Comunidades · Detalle junto al camino', 'foto' => 'empresa-1795396108621'],
    'comunidades-mirada-1' => ['nombre' => 'Comunidades · Mirar con otros ojos', 'foto' => 'empresa-1795396108327'],
    'comunidades-mirada-2' => ['nombre' => 'Comunidades · Detenerse a descubrir', 'foto' => 'empresa-1795396108097'],
    'comunidades-mirada-3' => ['nombre' => 'Comunidades · Llevarse una conexión', 'foto' => 'empresa-1795396108572'],
];
return $configuracion;
