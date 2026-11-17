@props(['servicio', 'oculto' => false])
@php
    // Datos de presentación: campos de servicios y, cuando se autoricen, fotografía, unidad y disponibilidad.
    $nombre = $servicio['nombre_servicio'];
    $duracion = $servicio['duracion_estimada'] ?? null;
    $linea = $servicio['linea_clave'] ?? '';
    $busqueda = $nombre.' '.($servicio['descripcion'] ?? '').' '.($servicio['nombre_region'] ?? '');
    $url = $servicio['url_detalle'] ?? route('publico.experiencia');
@endphp
<article class="publico-servicio" data-servicio data-linea="{{ $linea }}" data-busqueda="{{ $busqueda }}" data-duracion="{{ $duracion }}" @if($oculto) hidden @endif>
    <a class="publico-servicio-foto" href="{{ $url }}" tabindex="-1" aria-hidden="true">@if(!empty($servicio['fotografia']))<img src="{{ $servicio['fotografia'] }}" alt="" width="640" height="440" loading="lazy" decoding="async">@else<span class="publico-foto-pendiente"><x-icono nombre="montana" /><span>Fotografía por publicar</span></span>@endif<span class="publico-etiqueta">{{ $servicio['linea_nombre'] ?? 'Experiencia' }}</span></a>
    <div class="publico-servicio-cuerpo"><p class="publico-region"><x-icono nombre="paisaje" />{{ $servicio['nombre_region'] ?? 'Territorio por confirmar' }}</p><h2><a href="{{ $url }}">{{ $nombre }}</a></h2><p class="publico-servicio-descripcion">{{ $servicio['descripcion'] ?? 'Los detalles de esta experiencia se publicarán próximamente.' }}</p>
        <div class="publico-datos-breves"><span><x-icono nombre="sol" />{{ $duracion ? ($duracion >= 60 ? intdiv($duracion, 60).' h'.($duracion % 60 ? ' '.($duracion % 60).' min' : '') : $duracion.' min') : 'Duración por confirmar' }}</span><span><x-icono nombre="calendario" />{{ $servicio['disponibilidad'] ?? 'Fechas por publicar' }}</span></div>
        <div class="publico-servicio-pie"><div>@if(isset($servicio['precio_base']))<span>Precio base</span><strong>${{ number_format((float) $servicio['precio_base'], 2) }}</strong><small>{{ $servicio['unidad_cobro'] ?? 'Unidad de cobro por confirmar' }}</small>@else<strong>Precio por confirmar</strong>@endif</div><a class="publico-enlace-redondo" href="{{ $url }}" aria-label="Ver detalles de {{ $nombre }}"><x-icono nombre="flecha-diagonal" /></a></div>
    </div>
</article>
