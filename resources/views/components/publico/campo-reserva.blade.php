@props(['nombre', 'etiqueta', 'tipo' => 'text', 'obligatorio' => false, 'ayuda' => null])
@php
    // Mantiene juntos la etiqueta, el valor recuperado y el error de cada campo.
    $valor = old($nombre);
    $valor = is_scalar($valor) ? $valor : '';
@endphp
<div class="reserva-campo">
    <label for="{{ $nombre }}">{{ $etiqueta }} @unless($obligatorio)<span>Opcional</span>@endunless</label>
    <input {{ $attributes }} id="{{ $nombre }}" name="{{ $nombre }}" type="{{ $tipo }}"
        value="{{ $valor }}" @required($obligatorio)
        aria-invalid="{{ $errors->has($nombre) ? 'true' : 'false' }}"
        @if($ayuda || $errors->has($nombre)) aria-describedby="{{ $nombre }}-ayuda" @endif>
    @if($ayuda || $errors->has($nombre))
        <p id="{{ $nombre }}-ayuda" class="{{ $errors->has($nombre) ? 'reserva-error' : 'reserva-ayuda' }}">{{ $errors->first($nombre) ?: $ayuda }}</p>
    @endif
</div>
