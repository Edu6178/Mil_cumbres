@props(['indice', 'datos' => [], 'fechaMaxima'])
<fieldset data-acompanante="{{ $indice }}">
    <legend>Acompañante {{ $indice + 1 }}</legend>
    @foreach(['nombre' => 'Nombre(s)', 'apellido_paterno' => 'Apellido paterno', 'apellido_materno' => 'Apellido materno', 'fecha_nacimiento' => 'Fecha de nacimiento'] as $campo => $etiqueta)
        <label for="acompanante-{{ $indice }}-{{ $campo }}">{{ $etiqueta }}</label>
        <input id="acompanante-{{ $indice }}-{{ $campo }}"
            name="acompanantes[{{ $indice }}][{{ $campo }}]"
            type="{{ $campo === 'fecha_nacimiento' ? 'date' : 'text' }}"
            value="{{ is_string($datos[$campo] ?? null) ? $datos[$campo] : '' }}"
            @required($campo !== 'apellido_materno')
            @if($campo === 'fecha_nacimiento') max="{{ $fechaMaxima }}" @endif>
    @endforeach
</fieldset>
