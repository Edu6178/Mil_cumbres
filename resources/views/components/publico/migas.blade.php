@props(['actual', 'anterior' => null, 'enlace' => null])
<nav class="publico-migas" aria-label="Ruta de navegación"><a href="{{ route('inicio') }}">Inicio</a><span aria-hidden="true">/</span>@if($anterior && $enlace)<a href="{{ $enlace }}">{{ $anterior }}</a><span aria-hidden="true">/</span>@endif<span aria-current="page">{{ $actual }}</span></nav>
