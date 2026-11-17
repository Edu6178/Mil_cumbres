@extends('layouts.publico')
@section('titulo', 'Perfil de guía')
@section('robots', 'noindex, follow')

@section('contenido')
    @php
        // RF-07: solo se presenta un perfil entregado expresamente como público por el controlador.
        $guia = $guia ?? null;
        $publicado = $guia && ($guia['publicado'] ?? false) === true;
    @endphp
    <section class="contenedor publico-perfil-detalle">
        <x-publico.migas actual="Perfil de guía" anterior="Nuestro equipo" :enlace="route('publico.guias')" />
        @if(!$publicado)
            <h1 class="publico-rotulo-estado">Perfil de guía</h1>
            <x-publico.vacio titulo="Un nuevo encuentro está por comenzar." descripcion="El perfil se mostrará cuando esté autorizada su publicación, con fotografía, formación, especialidad y talentos." icono="usuario"><a class="boton boton-bosque" href="{{ route('publico.guias') }}">Volver al equipo <x-icono nombre="flecha" /></a></x-publico.vacio>
        @else
            <div class="publico-perfil-cuadricula">
                <aside class="publico-perfil-identidad"><div class="publico-perfil-foto"><img src="{{ !empty($guia['fotografia']) ? $guia['fotografia'] : asset('imagenes/perfil.svg') }}" alt="Fotografía de {{ $guia['nombre'] }}" width="560" height="640" fetchpriority="high">@if(empty($guia['fotografia']))<span>Fotografía por publicar</span>@endif</div><div><p class="numero-seccion">Guía Mil Cumbres</p><h1>{{ $guia['nombre'] }}</h1><p>{{ $guia['especialidad'] ?? 'Especialidad por publicar' }}</p><a class="enlace-texto" href="{{ route('publico.catalogo') }}">Conoce las experiencias <x-icono nombre="flecha" /></a></div></aside>
                <div class="publico-perfil-historia"><p class="antetitulo"><span></span> Personas que comparten el camino</p><h2>Cada mirada abre<br>un nuevo paisaje.</h2><p class="publico-presentacion-guia">{{ $guia['presentacion'] ?? 'Conoce la formación y los talentos que comparte en sus recorridos.' }}</p><nav class="publico-secciones" aria-label="Contenido del perfil"><a href="#formacion">Formación</a><a href="#especialidad">Especialidad</a><a href="#talentos">Talentos</a></nav>
                    <section id="formacion" class="publico-bloque-perfil"><span class="publico-mini-simbolo"><x-icono nombre="hoja" /></span><div><h3>Formación</h3><p class="publico-texto-libre">{{ $guia['formacion'] ?? 'Información por publicar.' }}</p></div></section>
                    <section id="especialidad" class="publico-bloque-perfil"><span class="publico-mini-simbolo"><x-icono nombre="brujula" /></span><div><h3>Especialidad</h3><p class="publico-texto-libre">{{ $guia['especialidad'] ?? 'Información por publicar.' }}</p></div></section>
                    <section id="talentos" class="publico-bloque-perfil"><span class="publico-mini-simbolo"><x-icono nombre="sol" /></span><div><h3>Talentos que comparte</h3><p class="publico-texto-libre">{{ $guia['talentos'] ?? 'Información por publicar.' }}</p></div></section>
                    <div class="publico-cita"><x-icono nombre="montana" /><p>Compartir el territorio también es abrir un espacio para aprender de otras personas.</p></div>
                </div>
            </div>
        @endif
    </section>
@endsection
