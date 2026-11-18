@props(['guia'])
{{-- El controlador deberá entregar exclusivamente perfiles autorizados para publicación, sin datos privados de personas. --}}
<article class="publico-guia-tarjeta" @if(!empty($guia['id'])) style="--retrato-guia: retrato-guia-{{ (int) $guia['id'] }}" @endif>
    <a class="publico-guia-foto" href="{{ $guia['url_perfil'] ?? route('publico.guia') }}" tabindex="-1" aria-hidden="true"><img data-retrato-guia src="{{ !empty($guia['fotografia']) ? $guia['fotografia'] : asset('imagenes/perfil.svg') }}" alt="" width="400" height="420" loading="lazy" decoding="async">@if(empty($guia['fotografia']))<span>Fotografía por publicar</span>@endif</a>
    <div><p class="numero-seccion">{{ $guia['especialidad'] ?? 'Guía de experiencias' }}</p><h2><a href="{{ $guia['url_perfil'] ?? route('publico.guia') }}">{{ $guia['nombre'] }}</a></h2><p>{{ $guia['presentacion'] ?? 'Conoce su formación y los talentos que comparte en cada recorrido.' }}</p><a class="enlace-texto" href="{{ $guia['url_perfil'] ?? route('publico.guia') }}">Conocer su perfil <x-icono nombre="flecha" /></a></div>
</article>
