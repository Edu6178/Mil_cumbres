@extends('administracion.layout')
@section('titulo', 'Resumen')

@section('contenido')
    <div class="adm-encabezado adm-revelar"><div><p class="adm-antetitulo">Una operación con propósito</p><h1>Cada gran experiencia<br>empieza <em>aquí.</em></h1><p>Organiza tu catálogo y acompaña el trabajo de quienes hacen posible cada recorrido.</p></div><a class="adm-boton adm-boton-principal" href="{{ route('administracion.servicios.crear') }}"><x-icono nombre="mas" /> Crear servicio</a></div>

    {{-- Los cuatro valores se calculan sobre las tablas existentes, sin cifras de demostración. --}}
    <div class="adm-indicadores adm-revelar">
        <a href="{{ route('administracion.servicios.indice') }}"><span class="adm-indicador-icono"><x-icono nombre="brujula" /></span><span>Servicios activos<strong>{{ $indicadores['servicios'] }}</strong><small>Experiencias de tu catálogo</small></span><x-icono nombre="flecha-diagonal" /></a>
        <a href="{{ route('administracion.categorias.indice') }}"><span class="adm-indicador-icono adm-icono-arena"><x-icono nombre="carpetas" /></span><span>Categorías<strong>{{ $indicadores['categorias'] }}</strong><small>Una propuesta organizada</small></span><x-icono nombre="flecha-diagonal" /></a>
        <a href="{{ route('administracion.guias') }}"><span class="adm-indicador-icono adm-icono-tierra"><x-icono nombre="personas" /></span><span>Guías registrados<strong>{{ $indicadores['guias'] }}</strong><small>Personas que acompañan</small></span><x-icono nombre="flecha-diagonal" /></a>
        <a href="{{ route('administracion.programaciones') }}"><span class="adm-indicador-icono"><x-icono nombre="calendario" /></span><span>Programaciones<strong>{{ $indicadores['programaciones'] }}</strong><small>Actividades en tu agenda</small></span><x-icono nombre="flecha-diagonal" /></a>
    </div>

    <div class="adm-resumen-cuadricula adm-revelar">
        <section class="adm-panel"><div class="adm-panel-titulo"><div><h2>Tu catálogo, al día</h2><p>Los últimos servicios registrados.</p></div><a class="adm-enlace" href="{{ route('administracion.servicios.indice') }}">Ver catálogo <x-icono nombre="flecha" /></a></div>
            @if($serviciosRecientes->isEmpty())
                <x-administracion.vacio titulo="Tu primera experiencia empieza aquí" descripcion="Todavía no hay servicios registrados. Prepara sus detalles, itinerario y precio base para dar forma al catálogo.">
                    <a class="adm-boton adm-boton-contorno" href="{{ route('administracion.servicios.crear') }}"><x-icono nombre="mas" /> Crear el primer servicio</a>
                </x-administracion.vacio>
            @else
                <div class="adm-lista-recientes">@foreach($serviciosRecientes as $servicio)<a href="{{ route('administracion.servicios.editar', $servicio) }}"><span class="adm-mini-icono"><x-icono nombre="ruta" /></span><div><strong>{{ $servicio->nombre_servicio }}</strong><span>{{ $servicio->categoria->nombre_categoria }} · ${{ number_format((float)$servicio->precio_base, 2) }} de precio base</span></div><span class="adm-insignia {{ $servicio->estado_servicio === 'activo' ? 'adm-insignia-verde' : '' }}">{{ $servicio->estado_servicio === 'activo' ? 'Activo' : ucfirst($servicio->estado_servicio) }}</span></a>@endforeach</div>
            @endif
        </section>
        <aside class="adm-panel adm-agenda"><div class="adm-panel-titulo"><div><h2>Próximos encuentros</h2><p>Actividades por acompañar.</p></div><x-icono nombre="calendario" /></div>
            @forelse($programacionesProximas as $actividad)
                <a class="adm-agenda-actividad" href="{{ route('administracion.asignacion.editar', $actividad->id) }}"><span class="adm-dia">{{ \Carbon\Carbon::parse($actividad->fecha_inicio)->format('d') }}<small>{{ \Carbon\Carbon::parse($actividad->fecha_inicio)->locale('es')->translatedFormat('M') }}</small></span><span><strong>{{ $actividad->nombre_servicio }}</strong><small>{{ $actividad->nombre_region }} · {{ $actividad->numero_guias }} {{ $actividad->numero_guias == 1 ? 'guía' : 'guías' }}</small></span></a>
            @empty
                <div class="adm-agenda-vacia"><x-icono nombre="sol" /><h3>La agenda está por comenzar.</h3><p>Cuando existan programaciones, aquí podrás revisar sus fechas y asignar a los guías.</p></div>
            @endforelse
            <a class="adm-enlace" href="{{ route('administracion.programaciones') }}">Consultar programaciones <x-icono nombre="flecha" /></a>
        </aside>
    </div>
    <div class="adm-guia-inicio adm-revelar"><div class="adm-guia-simbolo"><x-icono nombre="hoja" /></div><div><p class="adm-antetitulo">Pequeños pasos, mejores experiencias</p><h2>Primero organiza. Después, conecta.</h2><p>Crea tus categorías, registra los servicios y revisa quién acompañará cada actividad.</p></div><a href="{{ route('administracion.categorias.indice') }}" class="adm-enlace">Organizar categorías <x-icono nombre="flecha" /></a></div>
@endsection
