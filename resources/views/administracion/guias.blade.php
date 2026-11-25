@extends('administracion.layout')
@section('titulo', 'Equipo de guías')

@section('contenido')
    <div class="adm-encabezado"><div><p class="adm-antetitulo">Las personas detrás del recorrido</p><h1>Un equipo que<br><em>abre caminos.</em></h1><p>Consulta al personal registrado como guía, su formación y sus talentos.</p></div><a class="adm-boton adm-boton-contorno" href="{{ route('administracion.programaciones') }}"><x-icono nombre="calendario" /> Asignar a una actividad</a></div>
    <form class="adm-panel adm-filtros adm-filtro-guias" method="GET" action="{{ route('administracion.guias') }}" role="search" aria-label="Buscar en el equipo de guías">
        <div class="adm-campo adm-busqueda"><label for="buscar-guia-admin">Nombre, formación o talento</label><input type="search" id="buscar-guia-admin" name="buscar" value="{{ request('buscar') }}" maxlength="100" placeholder="Por ejemplo, observación de aves…" data-buscador-remoto="guias" data-url-sugerencias="{{ route('administracion.buscar') }}"></div><button type="submit" class="adm-boton adm-boton-principal"><x-icono nombre="buscar" /> Buscar</button>@if(request()->filled('buscar'))<a class="adm-enlace" href="{{ route('administracion.guias') }}">Limpiar</a>@endif
    </form>
    @if($guias->isEmpty())
        <section class="adm-panel"><x-administracion.vacio :titulo="request()->filled('buscar') ? 'No encontramos guías con esa búsqueda' : 'El camino también se construye en equipo'" :descripcion="request()->filled('buscar') ? 'Prueba con parte del nombre, la formación o un talento del guía.' : 'Todavía no hay personal registrado como guía. Cuando se incorpore, aquí podrás consultar sus perfiles y asignarlo a las actividades.'" icono="personas" /></section>
    @else
        <div class="adm-tarjetas-guias">@foreach($guias as $guia)<article id="guia-{{ $guia->id }}" class="adm-panel adm-tarjeta-guia adm-revelar"><div class="adm-guia-cabecera"><span class="adm-avatar adm-avatar-grande">{{ mb_strtoupper(mb_substr($guia->nombre, 0, 1)) }}</span><span class="adm-insignia">{{ $guia->estado_laboral ?: 'Estado sin especificar' }}</span></div><h2>{{ $guia->nombre }} {{ $guia->apellido_paterno }} {{ $guia->apellido_materno }}</h2><span class="adm-antetitulo">Guía de experiencias</span><dl><dt>Formación</dt><dd>{{ $guia->formacion ?: 'Aún no registrada.' }}</dd><dt>Talentos</dt><dd>{{ $guia->talentos ?: 'Aún no registrados.' }}</dd></dl><a class="adm-boton adm-boton-contorno" href="{{ route('administracion.fotografias', ['vista' => 'paginas', 'pagina' => 'guias', 'seleccionar' => 'guias:'.$guia->id]) }}"><x-icono nombre="paisaje" /> Cambiar fotografía</a></article>@endforeach</div>
        <x-administracion.paginacion :registros="$guias" />
    @endif
@endsection
