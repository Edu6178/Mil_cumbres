@props(['titulo', 'descripcion', 'icono' => 'paisaje'])

<div {{ $attributes->class(['adm-vacio']) }}>
    <div class="adm-vacio-ilustracion"><x-icono :nombre="$icono" /></div>
    <h3>{{ $titulo }}</h3>
    <p>{{ $descripcion }}</p>
    {{ $slot }}
</div>
