@props(['lineas' => null, 'activa' => ''])
@php
    $lineas = $lineas ?? config('propuesta.lineas');
@endphp
<fieldset class="publico-lineas"><legend>Líneas de servicio</legend><div>
    <button type="submit" name="linea" value="" data-filtro-linea="" aria-pressed="{{ $activa === '' ? 'true' : 'false' }}">Todas las experiencias</button>
    @foreach($lineas as $linea)<button type="submit" name="linea" value="{{ $linea['clave'] }}" data-filtro-linea="{{ $linea['clave'] }}" aria-pressed="{{ $activa === $linea['clave'] ? 'true' : 'false' }}"><x-icono :nombre="$linea['icono'] ?? 'hoja'" />{{ $linea['nombre'] }}</button>@endforeach
</div><p id="nota-lineas">Cinco líneas de servicio, una misma metodología de turismo regenerativo.</p>
<div class="publico-linea-formacion"><span>Formación y acompañamiento</span><button type="submit" name="linea" value="talleres-y-cursos" data-filtro-linea="talleres-y-cursos" aria-pressed="{{ $activa === 'talleres-y-cursos' ? 'true' : 'false' }}"><x-icono nombre="brujula" />Talleres y cursos de turismo</button><button type="submit" name="linea" value="consultoria-turistica" data-filtro-linea="consultoria-turistica" aria-pressed="{{ $activa === 'consultoria-turistica' ? 'true' : 'false' }}"><x-icono nombre="brujula" />Consultoría turística</button></div></fieldset>
