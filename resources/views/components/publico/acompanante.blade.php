@props(['indice', 'datos' => [], 'fechaMaxima'])
<fieldset class="reserva-acompanante" data-acompanante="{{ $indice }}">
    <legend>Acompañante {{ $indice + 1 }}</legend>
    @foreach(['nombre' => 'Nombre(s)', 'apellido_paterno' => 'Apellido paterno', 'apellido_materno' => 'Apellido materno', 'fecha_nacimiento' => 'Fecha de nacimiento'] as $campo => $etiqueta)
        <div class="reserva-campo">
            <label for="acompanante-{{ $indice }}-{{ $campo }}">{{ $etiqueta }} @if($campo === 'apellido_materno')<span>Opcional</span>@endif</label>
            <input id="acompanante-{{ $indice }}-{{ $campo }}"
                name="acompanantes[{{ $indice }}][{{ $campo }}]"
                type="{{ $campo === 'fecha_nacimiento' ? 'date' : 'text' }}"
                value="{{ is_string($datos[$campo] ?? null) ? $datos[$campo] : '' }}"
                aria-invalid="{{ $errors->has('acompanantes.'.$indice.'.'.$campo) ? 'true' : 'false' }}"
                @if($errors->has('acompanantes.'.$indice.'.'.$campo)) aria-describedby="acompanante-{{ $indice }}-{{ $campo }}-error" @endif
                @required($campo !== 'apellido_materno')
                @if($campo === 'fecha_nacimiento') max="{{ $fechaMaxima }}" @else maxlength="255" @endif>
            @error('acompanantes.'.$indice.'.'.$campo)<p class="reserva-error" id="acompanante-{{ $indice }}-{{ $campo }}-error">{{ $message }}</p>@enderror
        </div>
    @endforeach
</fieldset>
