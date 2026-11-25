@extends('layouts.publico')
@section('titulo', 'Momentos que nos conectan')
@section('descripcion', 'Recorridos, observación de aves, aprendizaje y momentos compartidos en la naturaleza con Mil Cumbres.')
@section('contenido')
    @php
        $fotosGaleria = app(\App\Services\Contenido\BibliotecaFotografica::class)->todas();
        $albumGaleria = is_string(request('album')) && array_key_exists(request('album'), config('fotografias.albumes')) ? request('album') : '';
        $buscarGaleria = is_string(request('buscar')) ? mb_substr(request('buscar'), 0, 100) : '';
        $visibleGaleria = fn ($foto) => (!$albumGaleria || $foto['album'] === $albumGaleria) && \App\Services\Busqueda\TextoBusqueda::coincide($foto['titulo'].' '.$foto['alternativo'], $buscarGaleria);
    @endphp
    <section class="galeria-introduccion contenedor"><x-publico.migas actual="Galería de momentos" /><p class="antetitulo"><span></span> La naturaleza se vive</p><h1>Hay momentos que<br><em>se quedan contigo.</em></h1><p>Un collage de caminos, encuentros y pequeños descubrimientos. Abre una fotografía y quédate un momento en su historia.</p></section>
    <section class="contenedor galeria-editorial" data-galeria data-galeria-filtrable>
        <form method="GET" action="{{ route('publico.galeria') }}" class="galeria-filtros" role="search"><label>¿Qué te gustaría descubrir?<input type="search" name="buscar" value="{{ $buscarGaleria }}" maxlength="100" placeholder="Aves, cascada, flores…" data-buscar-foto></label><label>Explora por momentos<select name="album" data-album-foto><option value="">Todos los momentos</option>@foreach(config('fotografias.albumes') as $clave => $nombre)<option value="{{ $clave }}" @selected($albumGaleria === $clave)>{{ $nombre }}</option>@endforeach</select></label><button class="boton boton-bosque" type="submit"><x-icono nombre="buscar" /> Explorar</button></form>
        <p class="galeria-contador" role="status" aria-live="polite"><strong data-total-fotos>{{ count(array_filter($fotosGaleria, $visibleGaleria)) }}</strong> fotografías para volver a mirar</p>
        <div class="galeria-mosaico">
            @foreach($fotosGaleria as $foto)
                <figure class="galeria-ficha" data-foto-galeria data-album="{{ $foto['album'] }}" data-texto="{{ $foto['titulo'].' '.$foto['alternativo'] }}" @if(!$visibleGaleria($foto)) hidden @endif>
                    <a href="{{ $foto['url'] }}" data-abrir-foto data-titulo="{{ $foto['titulo'] }}" data-descripcion="{{ $foto['alternativo'] }}" data-album="{{ $foto['album'] }}" data-album-nombre="{{ config('fotografias.albumes.'.$foto['album']) }}" aria-label="Ampliar: {{ $foto['titulo'] }}">
                        <x-fotografia :foto="$foto" sizes="(max-width: 600px) 90vw, (max-width: 1000px) 45vw, 40vw" />
                        <span class="galeria-ampliar" aria-hidden="true">Descubrir este momento <span>+</span></span>
                    </a>
                    <figcaption><span>{{ config('fotografias.albumes.'.$foto['album']) }}</span><h2>{{ $foto['titulo'] }}</h2><p data-foto-resumen>{{ $foto['alternativo'] }}</p></figcaption>
                </figure>
            @endforeach
        </div>
        <p class="galeria-sin-resultados" data-fotos-vacio @if(count(array_filter($fotosGaleria, $visibleGaleria))) hidden @endif>No encontramos fotografías con esa búsqueda. Prueba otra palabra o explora todos los momentos.</p>
    </section>
    <section class="publico-franja"><div class="contenedor"><div><p class="antetitulo"><span></span> Tu siguiente historia</p><h2>Ahora te toca vivirlo.</h2></div><a class="boton boton-bosque" href="{{ route('publico.catalogo') }}">Descubre las experiencias <x-icono nombre="flecha" /></a></div></section>
@endsection
