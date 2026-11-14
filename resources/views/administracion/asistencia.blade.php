@extends('administracion.layout')
@section('titulo', 'Participantes')

@section('contenido')
    <div class="adm-encabezado"><div><p class="adm-antetitulo">El encuentro comienza con las personas</p><h1>Participantes<br><em>de cada actividad.</em></h1><p>Consulta las personas registradas individualmente en las reservaciones.</p></div></div>
    <div class="adm-aviso adm-aviso-atencion"><x-icono nombre="informacion" /><div><strong>Registro de asistencia: próxima etapa.</strong><p>Por ahora puedes consultar las listas de participantes. La toma de asistencia todavía no está habilitada.</p></div></div>
    <section class="adm-panel"><div class="adm-panel-titulo"><div><h2>Actividades y participantes</h2><p>Selecciona una programación para consultar su lista.</p></div><x-icono nombre="lista" /></div>
        @if($programaciones->isEmpty())<x-administracion.vacio titulo="Cada persona forma parte de la experiencia" descripcion="Cuando existan programaciones y participantes individuales, sus listas aparecerán aquí." icono="personas" />
        @else<div class="adm-tabla-contenedor"><table class="adm-tabla"><thead><tr><th>Actividad</th><th>Fecha de inicio</th><th>Región</th><th class="adm-alinear-derecha">Lista de participantes</th></tr></thead><tbody>@foreach($programaciones as $actividad)<tr><td><strong>{{ $actividad->nombre_servicio }}</strong></td><td>{{ \Carbon\Carbon::parse($actividad->fecha_inicio)->format('d/m/Y H:i') }}</td><td>{{ $actividad->nombre_region }}</td><td class="adm-alinear-derecha"><a class="adm-enlace" href="{{ route('administracion.participantes', $actividad->id) }}">Ver participantes <x-icono nombre="flecha" /></a></td></tr>@endforeach</tbody></table></div><x-administracion.paginacion :registros="$programaciones" />@endif
    </section>
@endsection
