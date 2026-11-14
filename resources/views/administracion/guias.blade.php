@extends('administracion.layout')
@section('titulo', 'Equipo de guías')

@section('contenido')
    <div class="adm-encabezado"><div><p class="adm-antetitulo">Las personas detrás del recorrido</p><h1>Un equipo que<br><em>abre caminos.</em></h1><p>Consulta al personal registrado como guía, su formación y sus talentos.</p></div><a class="adm-boton adm-boton-contorno" href="{{ route('administracion.programaciones') }}"><x-icono nombre="calendario" /> Asignar a una actividad</a></div>
    @if($guias->isEmpty())
        <section class="adm-panel"><x-administracion.vacio titulo="El camino también se construye en equipo" descripcion="Todavía no hay personal registrado como guía. Cuando se incorpore, aquí podrás consultar sus perfiles y asignarlo a las actividades." icono="personas" /></section>
    @else
        <div class="adm-tarjetas-guias">@foreach($guias as $guia)<article class="adm-panel adm-tarjeta-guia adm-revelar"><div class="adm-guia-cabecera"><span class="adm-avatar adm-avatar-grande">{{ mb_strtoupper(mb_substr($guia->nombre, 0, 1)) }}</span><span class="adm-insignia">{{ $guia->estado_laboral ?: 'Estado sin especificar' }}</span></div><h2>{{ $guia->nombre }} {{ $guia->apellido_paterno }} {{ $guia->apellido_materno }}</h2><span class="adm-antetitulo">Guía de experiencias</span><dl><dt>Formación</dt><dd>{{ $guia->formacion ?: 'Aún no registrada.' }}</dd><dt>Talentos</dt><dd>{{ $guia->talentos ?: 'Aún no registrados.' }}</dd></dl></article>@endforeach</div>
        <x-administracion.paginacion :registros="$guias" />
    @endif
@endsection
