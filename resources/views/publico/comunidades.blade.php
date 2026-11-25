@extends('layouts.publico')
@section('titulo', 'Territorio y comunidades')
@section('descripcion', 'El lugar de las comunidades y los núcleos agrarios en la propuesta de turismo regenerativo de Mil Cumbres.')

@section('contenido')
    @php
        $comunidades = collect($comunidades ?? [])->filter(fn ($comunidad) => ($comunidad['publicada'] ?? false) === true);
        $biblioteca = app(\App\Services\Contenido\BibliotecaFotografica::class);
        $miradas = [
            ['foto' => 'empresa-1795396108327', 'numero' => '01', 'titulo' => 'Mirar con otros ojos', 'texto' => 'Una caminata puede empezar por el paisaje y continuar en una conversación.', 'pie' => 'Compartir el asombro'],
            ['foto' => 'empresa-1795396108097', 'numero' => '02', 'titulo' => 'Detenerse a descubrir', 'texto' => 'En la corteza, en una hoja y en los pequeños detalles también hay algo que aprender.', 'pie' => 'Aprender de lo cercano'],
            ['foto' => 'empresa-1795396108572', 'numero' => '03', 'titulo' => 'Llevarse una conexión', 'texto' => 'El tiempo compartido da otro sentido al camino y a lo que recordamos de él.', 'pie' => 'Crear recuerdos juntos'],
        ];
    @endphp
    <x-publico.hero actual="Territorio y comunidad" antetitulo="El paisaje también tiene una historia" titulo="Detrás de cada lugar, hay personas que lo hacen suyo." descripcion="Conocer un territorio también significa acercarse a sus saberes, reconocer a quienes lo habitan y escuchar sus distintas formas de relacionarse con el entorno." imagen="bosque-comunidades.webp" sello="Escuchar antes de proponer" />
    <section class="contenedor publico-manifiesto"><p class="antetitulo"><span></span> El valor de construir juntos</p><div><h2>El territorio es<br>una historia compartida.</h2><p>Las comunidades y los núcleos agrarios tienen historias, conocimientos y acuerdos propios. La propuesta de Mil Cumbres reconoce su lugar en el diseño de experiencias y proyectos vinculados con la naturaleza.</p></div></section>
    <section class="territorio-relato contenedor" aria-labelledby="titulo-territorio-relato">
        <div class="territorio-mosaico" data-revelar-bosque>
            <figure class="territorio-foto-principal" data-profundidad><x-fotografia :foto="$biblioteca->espacio('comunidades-relato')" espacio="comunidades-relato" sizes="(max-width: 700px) 90vw, 40vw" /><figcaption>Un camino. Muchas formas de descubrirlo.</figcaption></figure>
            <figure class="territorio-foto-detalle"><x-fotografia :foto="$biblioteca->espacio('comunidades-detalle')" espacio="comunidades-detalle" sizes="(max-width: 700px) 40vw, 20vw" /></figure>
            <span class="territorio-sello" aria-hidden="true"><x-icono nombre="hoja" /> El valor<br>de lo cercano</span>
        </div>
        <div class="territorio-relato-texto" data-revelar-bosque><p class="antetitulo"><span></span> Un encuentro con el lugar</p><h2 id="titulo-territorio-relato">El bosque se conoce<br><em>paso a paso.</em></h2><p>Hay mucho más que un paisaje frente a nosotros. Hay formas de habitarlo, historias por escuchar y una vida que merece nuestra atención.</p><p>Por eso, proponemos acercarnos con curiosidad, caminar con respeto y dar espacio a las personas que hacen parte del territorio.</p><a class="enlace-texto" href="#miradas-del-territorio">Recorre estas miradas <x-icono nombre="flecha" /></a></div>
    </section>
    <section class="territorio-miradas" id="miradas-del-territorio" aria-labelledby="titulo-miradas">
        <div class="contenedor"><div class="territorio-encabezado" data-revelar-bosque><div><p class="antetitulo"><span></span> Pequeños encuentros, grandes recuerdos</p><h2 id="titulo-miradas">Lo que ocurre<br><em>cuando bajamos el ritmo.</em></h2></div><p>Fotografías de los recorridos de Mil Cumbres.<br>Momentos que invitan a observar, aprender y compartir.</p></div>
            <div class="territorio-tarjetas">
                @foreach($miradas as $mirada)
                    <article class="territorio-mirada" data-revelar-bosque><figure><x-fotografia :foto="$biblioteca->espacio('comunidades-mirada-'.($loop->index + 1))" :espacio="'comunidades-mirada-'.($loop->index + 1)" sizes="(max-width: 700px) 90vw, 30vw" /><figcaption>{{ $mirada['pie'] }}</figcaption><span aria-hidden="true">{{ $mirada['numero'] }}</span></figure><h3>{{ $mirada['titulo'] }}</h3><p>{{ $mirada['texto'] }}</p></article>
                @endforeach
            </div>
            <a class="enlace-texto territorio-galeria" href="{{ route('publico.galeria') }}">Más momentos en la galería <x-icono nombre="flecha" /></a>
        </div>
    </section>
    <section class="contenedor publico-valores territorio-acuerdos" aria-label="Nuestra forma de acercarnos"><div class="publico-tres-columnas"><article><span class="publico-numero">01</span><x-icono nombre="personas" /><h3>Escuchar</h3><p>Acercarnos al contexto y reconocer las voces del lugar antes de plantear una propuesta.</p></article><article><span class="publico-numero">02</span><x-icono nombre="hoja" /><h3>Aprender</h3><p>Valorar los saberes y las formas de cuidado que dan sentido al territorio.</p></article><article><span class="publico-numero">03</span><x-icono nombre="ruta" /><h3>Colaborar</h3><p>Buscar una relación que considere a las personas y los acuerdos de cada comunidad.</p></article></div></section>
    <section class="contenedor publico-comunidades"><div class="publico-titulo-linea"><div><p class="antetitulo"><span></span> Historias por compartir</p><h2>Los lugares de este camino.</h2></div></div>
        @if($comunidades->isEmpty())<x-publico.vacio titulo="Cada historia se comparte a su tiempo." descripcion="Todavía no hay comunidades publicadas. Aquí presentaremos sus historias e información cuando su publicación esté autorizada." icono="paisaje" />
        @else<div class="publico-tres-columnas">@foreach($comunidades as $comunidad)<article><x-icono nombre="paisaje" /><h3>{{ $comunidad['nombre_comunidad'] }}</h3><p>{{ $comunidad['descripcion'] ?? 'Información por publicar.' }}</p>@if(!empty($comunidad['nombre_region']))<span class="numero-seccion">{{ $comunidad['nombre_region'] }}</span>@endif</article>@endforeach</div>@endif
    </section>
    <section class="publico-franja"><div class="contenedor"><div><p class="antetitulo"><span></span> La mirada que nos orienta</p><h2>La conexión empieza por reconocer al otro.</h2></div><a class="boton boton-bosque" href="{{ route('publico.metodologia') }}">Turismo regenerativo <x-icono nombre="flecha" /></a></div></section>
@endsection
