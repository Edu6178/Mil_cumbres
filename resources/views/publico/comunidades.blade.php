@extends('layouts.publico')
@section('titulo', 'Territorio y comunidades')
@section('descripcion', 'El lugar de las comunidades y los núcleos agrarios en la propuesta de turismo regenerativo de Mil Cumbres.')

@section('contenido')
    @php($comunidades = collect($comunidades ?? [])->filter(fn ($comunidad) => ($comunidad['publicada'] ?? false) === true))
    <x-publico.hero actual="Territorio y comunidad" antetitulo="El paisaje también tiene una historia" titulo="Detrás de cada lugar, hay personas que lo hacen suyo." descripcion="Conocer un territorio también significa acercarse a sus saberes, reconocer a quienes lo habitan y escuchar sus distintas formas de relacionarse con el entorno." imagen="bosque-comunidades.webp" sello="Escuchar antes de proponer" />
    <section class="contenedor publico-manifiesto"><p class="antetitulo"><span></span> El valor de construir juntos</p><div><h2>El territorio es<br>una historia compartida.</h2><p>Las comunidades y los núcleos agrarios tienen historias, conocimientos y acuerdos propios. La propuesta de Mil Cumbres reconoce su lugar en el diseño de experiencias y proyectos vinculados con la naturaleza.</p></div></section>
    <section class="contenedor publico-valores"><div class="publico-tres-columnas"><article><span class="publico-numero">01</span><h3>Escuchar</h3><p>Acercarnos al contexto y reconocer las voces del lugar antes de plantear una propuesta.</p></article><article><span class="publico-numero">02</span><h3>Aprender</h3><p>Valorar los saberes y las formas de cuidado que dan sentido al territorio.</p></article><article><span class="publico-numero">03</span><h3>Colaborar</h3><p>Buscar una relación que considere a las personas y los acuerdos de cada comunidad.</p></article></div></section>
    <section class="contenedor publico-comunidades"><div class="publico-titulo-linea"><div><p class="antetitulo"><span></span> Historias por compartir</p><h2>Los lugares de este camino.</h2></div></div>
        @if($comunidades->isEmpty())<x-publico.vacio titulo="Cada historia se comparte a su tiempo." descripcion="Todavía no hay comunidades publicadas. Aquí presentaremos sus historias e información cuando su publicación esté autorizada." icono="paisaje" />
        @else<div class="publico-tres-columnas">@foreach($comunidades as $comunidad)<article><x-icono nombre="paisaje" /><h3>{{ $comunidad['nombre_comunidad'] }}</h3><p>{{ $comunidad['descripcion'] ?? 'Información por publicar.' }}</p>@if(!empty($comunidad['nombre_region']))<span class="numero-seccion">{{ $comunidad['nombre_region'] }}</span>@endif</article>@endforeach</div>@endif
    </section>
    <section class="publico-franja"><div class="contenedor"><div><p class="antetitulo"><span></span> La mirada que nos orienta</p><h2>La conexión empieza por reconocer al otro.</h2></div><a class="boton boton-bosque" href="{{ route('publico.metodologia') }}">Turismo regenerativo <x-icono nombre="flecha" /></a></div></section>
@endsection
