@props(['nombre', 'etiqueta', 'tipo' => 'text', 'valor' => '', 'opcional' => false])
@php($anterior = old($nombre, $valor))
<div class="adm-campo"><label for="{{ $nombre }}">{{ $etiqueta }}@if($opcional)<small> · Opcional</small>@endif</label>
    <input {{ $attributes }} id="{{ $nombre }}" name="{{ $nombre }}" type="{{ $tipo }}" value="{{ is_scalar($anterior) ? $anterior : '' }}" @required(!$opcional) aria-invalid="{{ $errors->has($nombre) ? 'true' : 'false' }}" @error($nombre) aria-describedby="{{ $nombre }}-error" @enderror>
    @error($nombre)<small id="{{ $nombre }}-error" class="fin-error">{{ $message }}</small>@enderror
</div>
