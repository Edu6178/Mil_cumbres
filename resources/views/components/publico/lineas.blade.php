@props(['lineas' => null, 'activa' => ''])
@php
    // Solo senderismo está confirmado. El backend podrá reemplazar este arreglo con los nombres oficiales.
    $lineas = $lineas ?? [
        ['clave' => 'senderismo', 'nombre' => 'Senderismo', 'publicada' => true],
        ['clave' => 'linea-2', 'nombre' => 'Línea 2 por confirmar', 'publicada' => false],
        ['clave' => 'linea-3', 'nombre' => 'Línea 3 por confirmar', 'publicada' => false],
        ['clave' => 'linea-4', 'nombre' => 'Línea 4 por confirmar', 'publicada' => false],
        ['clave' => 'linea-5', 'nombre' => 'Línea 5 por confirmar', 'publicada' => false],
    ];
@endphp
<fieldset class="publico-lineas"><legend>Líneas de servicio</legend><div>
    <button type="submit" name="linea" value="" data-filtro-linea="" aria-pressed="{{ $activa === '' ? 'true' : 'false' }}">Todas las experiencias</button>
    @foreach($lineas as $linea)<button type="submit" name="linea" value="{{ $linea['clave'] }}" data-filtro-linea="{{ $linea['clave'] }}" aria-pressed="{{ $activa === $linea['clave'] ? 'true' : 'false' }}" @disabled(!($linea['publicada'] ?? false)) @if(!($linea['publicada'] ?? false)) aria-describedby="nota-lineas" @endif>@if($linea['clave'] === 'senderismo')<x-icono nombre="ruta" />@endif{{ $linea['nombre'] }}@if(!($linea['publicada'] ?? false))<span>Pendiente</span>@endif</button>@endforeach
</div><p id="nota-lineas">Conservamos las cinco líneas de Mil Cumbres. Cuatro nombres oficiales están por confirmar.</p></fieldset>
