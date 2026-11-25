@props(['foto', 'loading' => 'lazy', 'sizes' => '100vw', 'espacio' => null, 'servicio' => null, 'guia' => null])
@if($foto)
<img {{ $attributes->merge(['style' => 'object-position: '.$foto['foco'].';']) }} data-fotografia-id="{{ $foto['id'] }}" data-editor-tipo="{{ $espacio ? 'espacios' : ($servicio ? 'servicios' : ($guia ? 'guias' : 'foto')) }}" data-editor-clave="{{ $espacio ?? $servicio ?? $guia ?? $foto['id'] }}" src="{{ $foto['url'] }}" @if(empty($foto['subida'])) srcset="{{ $foto['mini'] }} {{ min(640, $foto['ancho']) }}w, {{ $foto['url'] }} {{ min(1600, $foto['ancho']) }}w" sizes="{{ $sizes }}" @endif alt="{{ $foto['alternativo'] }}" width="{{ $foto['ancho'] }}" height="{{ $foto['alto'] }}" loading="{{ $loading }}" decoding="async">
@endif
