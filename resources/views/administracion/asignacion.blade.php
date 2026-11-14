@extends('administracion.layout')
@section('titulo', 'Asignar guías')

@section('contenido')
    <a class="adm-enlace adm-volver" href="{{ route('administracion.programaciones') }}"><x-icono nombre="atras" /> Volver a programaciones</a>
    <div class="adm-encabezado"><div><p class="adm-antetitulo">Un buen equipo hace la diferencia</p><h1>¿Quién acompañará<br><em>este recorrido?</em></h1><p>Selecciona los guías de esta programación. Al guardar se comprueba la compatibilidad de horarios.</p></div></div>
    <div class="adm-actividad-resumen"><span class="adm-mini-icono"><x-icono nombre="ruta" /></span><div><strong>{{ $actividad->nombre_servicio }}</strong><p>{{ $actividad->nombre_region }} · {{ \Carbon\Carbon::parse($actividad->fecha_inicio)->format('d/m/Y H:i') }} a {{ \Carbon\Carbon::parse($actividad->fecha_fin)->format('d/m/Y H:i') }}</p></div><span class="adm-insignia">{{ ucfirst($actividad->estado) }}</span></div>
    <form method="POST" action="{{ route('administracion.asignacion.guardar', $actividad->id) }}" data-formulario-administracion>
        @csrf @method('PUT')
        <section class="adm-panel"><div class="adm-panel-titulo"><div><h2>Guías del equipo</h2><p>Puedes elegir más de una persona.</p></div><x-icono nombre="personas" /></div>
            @if($guias->isEmpty())<x-administracion.vacio titulo="Aún falta reunir al equipo" descripcion="No hay guías registrados para asignar a esta actividad." icono="personas" />
            @else<fieldset class="adm-seleccion-guias"><legend class="adm-solo-lector">Selecciona los guías para la programación</legend>@foreach($guias as $guia)<label><input type="checkbox" name="guias[]" value="{{ $guia->id }}" @checked(in_array($guia->id, array_map('intval', (array) old('guias', $seleccionados))))><span class="adm-avatar">{{ mb_strtoupper(mb_substr($guia->nombre, 0, 1)) }}</span><span><strong>{{ $guia->nombre }} {{ $guia->apellido_paterno }} {{ $guia->apellido_materno }}</strong><small>{{ $guia->estado_laboral ?: 'Estado laboral sin especificar' }}</small></span><x-icono nombre="correcto" /></label>@endforeach</fieldset>@endif
        </section>
        <div class="adm-acciones-formulario"><p><x-icono nombre="informacion" /> Desmarcar una persona retira su asignación activa y conserva el historial.</p><button type="submit" class="adm-boton adm-boton-principal" @disabled($guias->isEmpty() && empty($seleccionados))><x-icono nombre="correcto" /><span>Guardar asignación</span></button></div>
    </form>
@endsection
