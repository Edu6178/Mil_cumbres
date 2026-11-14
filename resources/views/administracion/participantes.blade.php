@extends('administracion.layout')
@section('titulo', 'Lista de participantes')

@section('contenido')
    <a class="adm-enlace adm-volver" href="{{ route('administracion.asistencia') }}"><x-icono nombre="atras" /> Volver a las actividades</a>
    <div class="adm-encabezado"><div><p class="adm-antetitulo">Personas que compartirán el camino</p><h1>Lista <em>de participantes.</em></h1><p>{{ $actividad->nombre_servicio }} · {{ $actividad->nombre_region }} · {{ \Carbon\Carbon::parse($actividad->fecha_inicio)->format('d/m/Y H:i') }}</p></div><span class="adm-insignia">Solo consulta</span></div>
    <section class="adm-panel">
        @if($participantes->isEmpty())<x-administracion.vacio titulo="Aún no hay participantes individuales" descripcion="Las personas registradas como invitadas en las reservaciones de esta actividad aparecerán aquí." icono="personas" />
        @else<div class="adm-tabla-contenedor"><table class="adm-tabla"><thead><tr><th>Participante</th><th>Reservación</th><th>Asistencia</th></tr></thead><tbody>@foreach($participantes as $participante)<tr><td><strong>{{ $participante->nombre }} {{ $participante->apellido_paterno }} {{ $participante->apellido_materno }}</strong></td><td>#{{ $participante->reservacion }}</td><td><span class="adm-insignia">Registro pendiente de habilitar</span></td></tr>@endforeach</tbody></table></div><x-administracion.paginacion :registros="$participantes" />@endif
    </section>
@endsection
