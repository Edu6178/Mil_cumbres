@extends('layouts.publico')
@section('titulo', 'Nuestro equipo de guías')
@section('descripcion', 'Conoce la formación, especialidades y talentos de los guías autorizados de Mil Cumbres.')

@section('contenido')
    @php
        // La publicación se decide en el backend. No se extraen datos de personal ni personas desde la vista.
        $guias = collect($guias ?? [])->filter(fn ($guia) => ($guia['publicado'] ?? false) === true)->values();
        $buscar = is_string(request('buscar')) ? request('buscar') : '';
        $coincide = fn ($guia) => \App\Services\Busqueda\TextoBusqueda::coincide($guia['nombre'].' '.($guia['especialidad'] ?? '').' '.($guia['talentos'] ?? '').' '.($guia['formacion'] ?? ''), $buscar);
    @endphp
    <x-publico.hero actual="Guías" antetitulo="Personas que hacen diferente el camino" titulo="El territorio se conoce mejor cuando alguien te lo comparte." descripcion="Detrás de cada recorrido hay una mirada, una historia y una forma de acompañar. Conoce a quienes compartirán contigo la experiencia." imagen="bosque-comunidades.webp" alternativo="Vegetación y árboles de un bosque" sello="Compartir también es una forma de aprender" />
    <section class="contenedor publico-equipo" data-equipo-publico>
        <div class="publico-titulo-linea"><div><p class="antetitulo"><span></span> Nuestro equipo</p><h2>Encuentra otras miradas.</h2></div><form class="publico-buscar-guia" action="{{ route('publico.guias') }}" method="GET"><label for="buscar-guia">Busca un nombre, talento o especialidad</label><div><x-icono nombre="buscar" /><input type="search" id="buscar-guia" name="buscar" value="{{ $buscar }}" maxlength="100" placeholder="¿Qué te gustaría aprender?" data-buscar-guia><button type="submit" aria-label="Buscar guía"><x-icono nombre="flecha" /></button></div></form></div>
        <p class="publico-contador" role="status" aria-live="polite"><strong data-total-guias>{{ $guias->filter($coincide)->count() }}</strong> perfiles publicados</p>
        <p class="reserva-ayuda">Mil Cumbres asigna a la persona que acompañará cada experiencia según su disponibilidad y las características del grupo.</p>
        <x-publico.carrusel-guias :guias="$guias" identificador="guias-equipo" :buscar="$buscar" />
        <x-publico.vacio data-vacio-guias :hidden="$guias->filter($coincide)->isNotEmpty()" titulo="Las personas detrás de la experiencia." descripcion="Todavía no hay perfiles publicados. Aquí podrás conocer su formación, especialidad y talentos cuando su información esté autorizada." icono="personas"><a class="enlace-texto" href="{{ route('publico.metodologia') }}">Conoce lo que nos mueve <x-icono nombre="flecha" /></a></x-publico.vacio>
    </section>
    <section class="publico-franja"><div class="contenedor"><div><p class="antetitulo"><span></span> Conexiones con sentido</p><h2>Una experiencia, muchas formas de mirar.</h2><p>Caminar juntos también es escuchar, aprender y reconocer el lugar que visitamos.</p></div><a class="boton boton-bosque" href="{{ route('publico.catalogo') }}">Explorar experiencias <x-icono nombre="flecha" /></a></div></section>
@endsection
