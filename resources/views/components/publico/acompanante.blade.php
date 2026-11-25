@props(['indice', 'datos' => []])
<fieldset class="reserva-acompanante" data-acompanante="{{ $indice }}">
    <legend>Acompañante {{ $indice + 1 }}</legend>
    @foreach(['nombre' => 'Nombre completo', 'edad' => 'Edad al realizar la visita'] as $campo => $etiqueta)
        <div class="reserva-campo">
            <label for="acompanante-{{ $indice }}-{{ $campo }}">{{ $etiqueta }} @if($campo === 'apellido_materno')<span>Opcional</span>@endif</label>
            <input id="acompanante-{{ $indice }}-{{ $campo }}"
                name="acompanantes[{{ $indice }}][{{ $campo }}]"
                type="{{ $campo === 'edad' ? 'number' : 'text' }}"
                value="{{ is_scalar($datos[$campo] ?? null) ? $datos[$campo] : '' }}"
                aria-invalid="{{ $errors->has('acompanantes.'.$indice.'.'.$campo) ? 'true' : 'false' }}"
                @if($errors->has('acompanantes.'.$indice.'.'.$campo)) aria-describedby="acompanante-{{ $indice }}-{{ $campo }}-error" @endif
                @required($campo !== 'apellido_materno')
                @if($campo === 'edad') min="0" max="120" step="1" inputmode="numeric" placeholder="Años cumplidos" @else maxlength="120" placeholder="Nombre y apellidos" @endif>
            @error('acompanantes.'.$indice.'.'.$campo)<p class="reserva-error" id="acompanante-{{ $indice }}-{{ $campo }}-error">{{ $message }}</p>@enderror
        </div>
    @endforeach
</fieldset>
