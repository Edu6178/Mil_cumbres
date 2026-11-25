@extends('administracion.layout')
@section('titulo', 'Resumen')

@section('contenido')
    @php
        $ahora = now('America/Mexico_City')->locale('es');
        $totalReservaciones = (int) $estadosReservacion->sum('total');
        $colores = ['#193e34', '#b56d49', '#8caa83', '#c6ae78', '#7f969b', '#9c8296'];
        $segmentos = [];
        $acumulado = 0;
        // El gráfico solo representa reservaciones guardadas; con cero registros queda neutral.
        foreach ($estadosReservacion as $indice => $estado) {
            if ($totalReservaciones > 0 && $estado->total > 0) {
                $fin = $acumulado + $estado->total / $totalReservaciones * 100;
                $segmentos[] = $colores[$indice % count($colores)].' '.round($acumulado, 4).'% '.round($fin, 4).'%';
                $acumulado = $fin;
            }
        }
    @endphp
    <div class="adm-encabezado adm-revelar">
        <div><p class="adm-antetitulo">Panel de control</p><h1>Resumen general</h1><p>Hola, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}. Así va la operación de Mil Cumbres.</p></div>
        <div class="adm-encabezado-acciones"><time datetime="{{ $ahora->toDateString() }}"><x-icono nombre="calendario" /> {{ ucfirst($ahora->translatedFormat('D, d M Y')) }}</time><a class="adm-boton adm-boton-principal" href="{{ route('administracion.servicios.crear') }}"><x-icono nombre="mas" /> Nuevo servicio</a></div>
    </div>

    <div class="adm-indicadores adm-revelar">
        <a href="#reservaciones-estado"><span class="adm-indicador-etiqueta">Reservaciones <x-icono nombre="flecha-diagonal" /></span><div><strong>{{ $indicadores['reservaciones'] }}</strong><span class="adm-indicador-icono adm-icono-tierra"><x-icono nombre="lista" /></span></div><small>Total de solicitudes registradas</small></a>
        <a href="{{ route('administracion.servicios.indice') }}"><span class="adm-indicador-etiqueta">Servicios activos <x-icono nombre="flecha-diagonal" /></span><div><strong>{{ $indicadores['servicios'] }}</strong><span class="adm-indicador-icono"><x-icono nombre="brujula" /></span></div><small>Disponibles en tu catálogo</small></a>
        <a href="{{ route('administracion.programaciones') }}"><span class="adm-indicador-etiqueta">Programaciones <x-icono nombre="flecha-diagonal" /></span><div><strong>{{ $indicadores['programaciones'] }}</strong><span class="adm-indicador-icono adm-icono-arena"><x-icono nombre="calendario" /></span></div><small>Actividades registradas</small></a>
        <a href="{{ route('administracion.guias') }}"><span class="adm-indicador-etiqueta">Equipo de guías <x-icono nombre="flecha-diagonal" /></span><div><strong>{{ $indicadores['guias'] }}</strong><span class="adm-indicador-icono"><x-icono nombre="personas" /></span></div><small>Personas registradas como guías</small></a>
    </div>

    <div class="adm-dashboard-cuadricula">
        <div class="adm-dashboard-central">
            <section class="adm-panel adm-revelar" id="reservaciones-estado">
                <div class="adm-panel-titulo"><div><h2>Reservaciones por estado</h2><p>Distribución de las solicitudes registradas.</p></div><span class="adm-insignia">{{ $totalReservaciones }} en total</span></div>
                <div class="adm-distribucion">
                    <div class="adm-grafico-reservas" role="img" aria-label="{{ $totalReservaciones }} reservaciones en total; desglose en la lista de estados." @if(count($segmentos)) style="background: conic-gradient({{ implode(', ', $segmentos) }})" @endif><div><strong>{{ $totalReservaciones }}</strong><span>Reservaciones</span></div></div>
                    <div class="adm-estados-lista">
                        @forelse($estadosReservacion as $estado)
                            <div class="adm-estado-fila"><div><span class="adm-color-estado" style="background: {{ $colores[$loop->index % count($colores)] }}"></span><span>{{ $estado->nombre_estado }}</span><strong>{{ $estado->total }}</strong></div><div class="adm-barra-estado"><span style="width: {{ $totalReservaciones ? round($estado->total / $totalReservaciones * 100, 2) : 0 }}%; background: {{ $colores[$loop->index % count($colores)] }}"></span></div></div>
                        @empty
                            <div class="adm-sin-datos"><span class="adm-mini-icono"><x-icono nombre="lista" /></span><h3>Aún no hay reservaciones</h3><p>Los estados y su distribución aparecerán aquí cuando se registren solicitudes.</p></div>
                        @endforelse
                    </div>
                </div>
                <div class="adm-panel-nota"><span class="adm-punto-estado"></span> Actualizado a las {{ $ahora->format('H:i') }} h <span>Datos del sistema</span></div>
            </section>

            <section class="adm-panel adm-revelar">
                <div class="adm-panel-titulo"><div><h2>Servicios recientes</h2><p>{{ $indicadores['categorias'] }} categorías · {{ $serviciosOcultos }} servicios ocultos</p></div><a class="adm-enlace" href="{{ route('administracion.servicios.indice') }}">Ver todos <x-icono nombre="flecha" /></a></div>
                @if($serviciosRecientes->isEmpty())
                    <div class="adm-catalogo-vacio"><span class="adm-mini-icono"><x-icono nombre="brujula" /></span><div><h3>Tu catálogo está vacío</h3><p>Agrega una categoría y registra tu primer servicio.</p></div><a class="adm-boton adm-boton-contorno" href="{{ route('administracion.categorias.indice') }}">Crear categoría <x-icono nombre="flecha" /></a></div>
                @else
                    <div class="adm-tabla-contenedor"><table class="adm-tabla adm-tabla-resumen"><thead><tr><th>Servicio</th><th>Categoría</th><th>Precio base</th><th>Estado</th></tr></thead><tbody>
                    @foreach($serviciosRecientes as $servicio)<tr><td><a class="adm-servicio-nombre" href="{{ route('administracion.servicios.editar', $servicio) }}">{{ $servicio->nombre_servicio }}</a></td><td>{{ $servicio->categoria->nombre_categoria }}</td><td class="adm-precio">{{ $servicio->precio_base !== null ? ($servicio->tipo_precio === 'desde' ? 'Desde ' : '').'$'.number_format((float) $servicio->precio_base, 2).' '.$servicio->moneda : 'Por confirmar' }}@if($servicio->precio_base !== null)<small>{{ \App\Services\Finanzas\TarifaReserva::UNIDADES[$servicio->unidad_cobro] ?? 'Unidad por confirmar' }}</small>@endif</td><td><span class="adm-insignia {{ $servicio->estado_servicio === 'activo' ? 'adm-insignia-verde' : '' }}">{{ ucfirst($servicio->estado_servicio) }}</span></td></tr>@endforeach
                    </tbody></table></div>
                @endif
            </section>

            <section class="adm-panel adm-revelar">
                <div class="adm-panel-titulo"><div><h2>Accesos rápidos</h2><p>Continúa con las tareas de tu operación.</p></div><x-icono nombre="panel" /></div>
                <div class="adm-accesos"><a href="{{ route('administracion.servicios.crear') }}"><span class="adm-mini-icono"><x-icono nombre="mas" /></span><span><strong>Nuevo servicio</strong><small>Experiencias y precios</small></span><x-icono nombre="flecha" /></a><a href="{{ route('administracion.programaciones') }}"><span class="adm-mini-icono adm-icono-arena"><x-icono nombre="calendario" /></span><span><strong>Asignar guías</strong><small>Revisar programaciones</small></span><x-icono nombre="flecha" /></a><a href="{{ route('administracion.categorias.indice') }}"><span class="adm-mini-icono adm-icono-tierra"><x-icono nombre="carpetas" /></span><span><strong>Categorías</strong><small>Organizar el catálogo</small></span><x-icono nombre="flecha" /></a><a href="{{ route('administracion.asistencia') }}"><span class="adm-mini-icono"><x-icono nombre="personas" /></span><span><strong>Participantes</strong><small>Consultar las listas</small></span><x-icono nombre="flecha" /></a></div>
            </section>
        </div>
        <div class="adm-dashboard-derecha">
            <section class="adm-panel adm-perfil-tarjeta adm-revelar">
                <div class="adm-perfil-franja"><span>Mi perfil</span><x-icono nombre="hoja" /></div>
                <x-administracion.avatar class="adm-foto-grande" />
                <h2>{{ auth()->user()->name }}</h2><p>{{ auth()->user()->email }}</p>
                <span class="adm-insignia adm-insignia-verde"><span class="adm-punto-estado"></span> Administración</span>
                <dl><div><dt>Fecha de alta</dt><dd>{{ auth()->user()->created_at?->format('d/m/Y') ?: 'Sin fecha' }}</dd></div><div><dt>Permisos</dt><dd>Gestión administrativa</dd></div></dl>
                <a class="adm-boton adm-boton-contorno" href="{{ route('administracion.perfil') }}"><x-icono nombre="usuario" /> Ver mi perfil <x-icono nombre="flecha" /></a>
            </section>
            <section class="adm-panel adm-agenda adm-revelar">
                <div class="adm-panel-titulo"><div><h2>Próximas actividades</h2><p>Tu agenda de operación.</p></div><x-icono nombre="calendario" /></div>
                @forelse($programacionesProximas as $actividad)
                    <a class="adm-agenda-actividad" href="{{ route('administracion.asignacion.editar', $actividad->id) }}"><span class="adm-dia">{{ \Carbon\Carbon::parse($actividad->fecha_inicio)->format('d') }}<small>{{ \Carbon\Carbon::parse($actividad->fecha_inicio)->locale('es')->translatedFormat('M') }}</small></span><span><strong>{{ $actividad->nombre_servicio }}</strong><small>{{ \Carbon\Carbon::parse($actividad->fecha_inicio)->format('H:i') }} h · {{ $actividad->numero_guias }} {{ $actividad->numero_guias == 1 ? 'guía' : 'guías' }}</small></span><x-icono nombre="flecha" /></a>
                @empty
                    <div class="adm-agenda-vacia"><span class="adm-agenda-icono"><x-icono nombre="calendario" /></span><h3>Sin actividades próximas</h3><p>Las siguientes programaciones aparecerán en este espacio.</p></div>
                @endforelse
                <a class="adm-agenda-pie" href="{{ route('administracion.programaciones') }}">Consultar programaciones <x-icono nombre="flecha" /></a>
            </section>
        </div>
    </div>
@endsection
