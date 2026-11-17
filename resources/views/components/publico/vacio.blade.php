@props(['titulo', 'descripcion', 'icono' => 'paisaje'])
<div {{ $attributes->class(['publico-vacio']) }}><span class="publico-vacio-icono" aria-hidden="true"><x-icono :nombre="$icono" /></span><p class="numero-seccion">Un camino que sigue tomando forma</p><h2 data-vacio-titulo>{{ $titulo }}</h2><p data-vacio-descripcion>{{ $descripcion }}</p>{{ $slot }}</div>
