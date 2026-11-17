@extends('layouts.publico')
@section('titulo', 'Experiencias y senderismo')
@section('descripcion', 'Explora las experiencias de Mil Cumbres: rutas, itinerarios, duración, precios y fechas de cada servicio publicado.')

@section('contenido')
    @php
        // Se reciben únicamente datos públicos. No hay consultas ni registros de demostración en esta vista.
        $servicios = collect($servicios ?? [])->filter(fn ($servicio) => ($servicio['estado_servicio'] ?? '') === 'activo')->values();
        $buscar = is_string(request('buscar')) ? request('buscar') : '';
        $lineaActiva = is_string(request('linea')) ? request('linea') : '';
        $duracionActiva = in_array(request('duracion'), ['corta', 'media', 'larga'], true) ? request('duracion') : '';
        $normalizar = fn ($valor) => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $valor));
        $coincide = function ($servicio) use ($buscar, $lineaActiva, $duracionActiva, $normalizar) {
            $texto = ($servicio['nombre_servicio'] ?? '').' '.($servicio['descripcion'] ?? '').' '.($servicio['nombre_region'] ?? '');
            $minutos = $servicio['duracion_estimada'] ?? null;
            $duracion = $duracionActiva === '' || ($minutos !== null && match ($duracionActiva) {
                'corta' => $minutos <= 180, 'media' => $minutos > 180 && $minutos <= 360, 'larga' => $minutos > 360,
            });
            return ($buscar === '' || str_contains($normalizar($texto), $normalizar(trim($buscar))))
                && ($lineaActiva === '' || ($servicio['linea_clave'] ?? '') === $lineaActiva) && $duracion;
        };
        $totalVisible = $servicios->filter($coincide)->count();
    @endphp
    <x-publico.hero actual="Experiencias" antetitulo="Afuera hay algo que te espera" titulo="Encuentra tu próxima historia, al aire libre." descripcion="Recorridos para descubrir el territorio con calma, conocer otras miradas y conectar con lo que importa." imagen="senderismo.webp" alternativo="Un grupo caminando por un sendero de montaña" sello="Cada camino tiene algo que compartir">
        <a class="enlace-texto" href="#explorar-catalogo">Explorar experiencias <x-icono nombre="abajo" /></a>
    </x-publico.hero>

    <section class="publico-catalogo contenedor" id="explorar-catalogo" data-catalogo-publico>
        <form action="{{ route('publico.catalogo') }}" method="GET" data-filtros-catalogo>
            <input type="hidden" name="linea" value="{{ $lineaActiva }}" data-linea-actual>
            <x-publico.lineas :lineas="$lineas ?? null" :activa="$lineaActiva" />
            <div class="publico-barra-filtros">
                <div class="publico-buscar"><label for="buscar-experiencia">¿Qué te gustaría descubrir?</label><div><x-icono nombre="buscar" /><input id="buscar-experiencia" type="search" name="buscar" value="{{ $buscar }}" maxlength="100" placeholder="Busca una experiencia o un territorio" data-buscar-servicio></div></div>
                <div class="publico-select"><label for="duracion-experiencia">Tiempo para explorar</label><select name="duracion" id="duracion-experiencia" data-duracion-servicio><option value="">Cualquier duración</option><option value="corta" @selected($duracionActiva === 'corta')>Hasta 3 horas</option><option value="media" @selected($duracionActiva === 'media')>Más de 3 y hasta 6 horas</option><option value="larga" @selected($duracionActiva === 'larga')>Más de 6 horas</option></select></div>
                <button class="boton boton-bosque" type="submit"><x-icono nombre="buscar" /> Buscar</button>
            </div>
        </form>
        <div class="publico-resultados-cabecera"><p role="status" aria-live="polite"><strong data-total-servicios>{{ $totalVisible }}</strong> <span data-etiqueta-resultados>{{ $totalVisible === 1 ? 'experiencia publicada' : 'experiencias publicadas' }}</span></p><div><a href="{{ route('publico.catalogo') }}" class="publico-limpiar" data-limpiar-catalogo>Limpiar filtros</a><div class="publico-vistas" aria-label="Presentación de resultados"><button type="button" aria-label="Ver en cuadrícula" aria-pressed="true" data-vista-catalogo="cuadricula"><x-icono nombre="panel" /></button><button type="button" aria-label="Ver en lista" aria-pressed="false" data-vista-catalogo="lista"><x-icono nombre="lista" /></button></div></div></div>
        <div class="publico-servicios-cuadricula" data-lista-servicios>@foreach($servicios as $servicio)<x-publico.servicio :servicio="$servicio" :oculto="!$coincide($servicio)" />@endforeach</div>
        <x-publico.vacio data-vacio-catalogo :hidden="$totalVisible > 0" :titulo="$servicios->isEmpty() ? 'Las mejores historias necesitan un buen comienzo.' : 'No encontramos experiencias con esos filtros.'" :descripcion="$servicios->isEmpty() ? 'Estamos preparando el catálogo. Aquí encontrarás cada ruta, su itinerario, duración, precio y las fechas publicadas.' : 'Prueba con otro nombre, una duración distinta o todas las líneas de servicio.'">
            <a class="boton publico-boton-contorno" href="{{ route('publico.metodologia') }}">Conoce nuestra forma de viajar <x-icono nombre="flecha" /></a>
        </x-publico.vacio>
        <div class="publico-aviso"><x-icono nombre="informacion" /><p>Las fechas y los cupos se mostrarán en cada experiencia cuando se publiquen sus programaciones. La disponibilidad se confirma al solicitar una reservación.</p></div>
    </section>
    <section class="publico-franja"><div class="contenedor"><div><p class="antetitulo"><span></span> Otra manera de encontrarse</p><h2>¿Viajan en grupo?</h2><p>Conoce la propuesta para agencias, empresas y organizaciones.</p></div><a class="boton boton-bosque" href="{{ route('publico.consultoria') }}">Para organizaciones <x-icono nombre="flecha-diagonal" /></a></div></section>
@endsection
