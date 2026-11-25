@props(['venta'])
@php
    $fechaVisita = $venta->programacion?->fecha_inicio ?? $venta->fecha_solicitud;
    $responsable = $venta->cliente?->persona;
    $edadAnterior = fn ($persona) => $persona?->fecha_nacimiento ? (int) $persona->fecha_nacimiento->diffInYears($fechaVisita) : null;
    $edadResponsable = $venta->edad_responsable ?? $edadAnterior($responsable);
@endphp
<section class="adm-panel">
    <div class="adm-panel-titulo"><div><h2>El grupo y sus edades</h2><p>Información para orientar la experiencia al día de la visita.</p></div><x-icono nombre="personas" /></div>
    <dl class="fin-datos"><div><dt>Persona responsable</dt><dd>{{ $responsable?->nombre }} {{ $responsable?->apellido_paterno }} · {{ $edadResponsable !== null ? $edadResponsable.' años' : 'Edad no registrada' }}</dd></div>
        @foreach($venta->acompanantes as $persona)
            @php($edad = $persona->pivot->edad ?? $edadAnterior($persona))
            <div><dt>Acompañante {{ $loop->iteration }}</dt><dd>{{ trim($persona->nombre.' '.$persona->apellido_paterno.' '.$persona->apellido_materno) }} · {{ $edad !== null ? $edad.' años' : 'Edad no registrada' }}</dd></div>
        @endforeach
        @if($venta->personalizacion)<div><dt>Comentarios para el recorrido</dt><dd>{{ $venta->personalizacion }}</dd></div>@endif
    </dl>
</section>
