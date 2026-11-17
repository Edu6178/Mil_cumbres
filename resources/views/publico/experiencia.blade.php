@extends('layouts.publico')
@section('titulo', 'Detalle de experiencia')
@section('robots', 'noindex, follow')

@section('contenido')
    @php
        // Plantilla de RF-01. El controlador entregará el servicio activo y sus datos públicos.
        $servicio = $servicio ?? null;
        $publicado = $servicio && ($servicio['estado_servicio'] ?? '') === 'activo';
    @endphp
    <div class="contenedor publico-detalle">
        <x-publico.migas actual="Detalle de experiencia" anterior="Experiencias" :enlace="route('publico.catalogo')" />
        @if(!$publicado)
            <h1 class="publico-rotulo-estado">Detalle de experiencia</h1>
            <x-publico.vacio titulo="Una nueva experiencia está por tomar forma." descripcion="Este espacio reunirá fotografías, descripción, itinerario, duración, precio y disponibilidad cuando se publique una experiencia.">
                <a class="boton boton-bosque" href="{{ route('publico.catalogo') }}">Volver al catálogo <x-icono nombre="flecha" /></a>
            </x-publico.vacio>
        @else
            <header class="publico-detalle-titulo"><p class="antetitulo"><span></span>{{ $servicio['linea_nombre'] ?? 'Una experiencia Mil Cumbres' }}</p><h1>{{ $servicio['nombre_servicio'] }}</h1><p><x-icono nombre="paisaje" />{{ $servicio['nombre_region'] ?? 'Territorio por confirmar' }}</p></header>
            <div class="publico-detalle-foto">@if(!empty($servicio['fotografia']))<img src="{{ $servicio['fotografia'] }}" alt="{{ $servicio['nombre_servicio'] }}" width="1200" height="650" fetchpriority="high">@else<span class="publico-foto-pendiente"><x-icono nombre="paisaje" /><span>Fotografías por publicar</span></span>@endif</div>
            <div class="publico-detalle-cuadricula">
                <div><nav class="publico-secciones" aria-label="Información de la experiencia"><a href="#acerca">La experiencia</a><a href="#itinerario">Itinerario</a><a href="#disponibilidad">Fechas y cupos</a></nav>
                    <section class="publico-texto-detalle" id="acerca"><p class="numero-seccion">Conoce el recorrido</p><h2>Lo que hace especial este camino.</h2><p class="publico-texto-libre">{{ $servicio['descripcion'] ?? 'La descripción se publicará próximamente.' }}</p></section>
                    <section class="publico-texto-detalle" id="itinerario"><p class="numero-seccion">Paso a paso</p><h2>El itinerario.</h2><p class="publico-texto-libre">{{ $servicio['itinerario'] ?? 'El itinerario se publicará próximamente.' }}</p></section>
                    <section class="publico-texto-detalle" id="disponibilidad"><p class="numero-seccion">Encuentra tu momento</p><h2>Fechas y cupos.</h2>
                        @forelse($servicio['programaciones'] ?? [] as $programacion)<div class="publico-fecha"><x-icono nombre="calendario" /><span><strong>{{ $programacion['fecha'] }}</strong><small>{{ $programacion['horario'] }}</small></span><span>{{ $programacion['cupos_disponibles'] }} lugares disponibles</span></div>
                        @empty<p>Aún no hay fechas publicadas para esta experiencia.</p>@endforelse
                        <p class="publico-nota">Los cupos se comprueban de nuevo al registrar la solicitud.</p>
                    </section>
                </div>
                <aside class="publico-resumen-experiencia"><p class="numero-seccion">Prepara tu recorrido</p><h2>Un momento para salir.</h2><dl><div><dt>Duración</dt><dd>{{ isset($servicio['duracion_estimada']) ? $servicio['duracion_estimada'].' minutos' : 'Por confirmar' }}</dd></div><div><dt>Precio base</dt><dd>{{ isset($servicio['precio_base']) ? '$'.number_format((float) $servicio['precio_base'], 2) : 'Por confirmar' }}</dd></div><div><dt>Unidad de cobro</dt><dd>{{ $servicio['unidad_cobro'] ?? 'Por confirmar' }}</dd></div></dl><a class="boton boton-bosque" href="{{ route('inicio') }}#pre-reservacion">Consultar programaciones <x-icono nombre="flecha" /></a><p>La solicitud de pre-reservación se realiza en el apartado de programaciones.</p></aside>
            </div>
        @endif
    </div>
@endsection
