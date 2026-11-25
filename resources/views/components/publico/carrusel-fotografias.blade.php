@php
    $fotosCarrusel = array_slice(array_values(array_filter(app(\App\Services\Contenido\BibliotecaFotografica::class)->todas(), fn ($foto) => $foto['destacada'])), 0, 12);
@endphp
@if(count($fotosCarrusel))
<section class="seccion momentos" aria-labelledby="titulo-momentos" data-carrusel-fotos data-galeria>
    <div class="contenedor momentos-cabecera"><div><p class="antetitulo"><span></span> El viaje, en primera persona</p><h2 id="titulo-momentos">Momentos que<br><em>nos conectan.</em></h2></div><div><p>Pequeños encuentros. Nuevas miradas.<br>El recuerdo de haber estado ahí.</p><a class="enlace-texto" href="{{ route('publico.galeria') }}">Explorar toda la galería <x-icono nombre="flecha" /></a></div></div>
    <div class="momentos-ventana" data-fotos-ventana tabindex="0" aria-label="Fotografías de experiencias. Usa las flechas para recorrerlas.">
        <div class="momentos-pista" data-fotos-pista>@foreach($fotosCarrusel as $foto)
            <figure class="momento-foto" data-fotos-ficha><a href="{{ $foto['url'] }}" data-abrir-foto data-titulo="{{ $foto['titulo'] }}" data-descripcion="{{ $foto['alternativo'] }}" data-album="{{ $foto['album'] }}" data-album-nombre="{{ config('fotografias.albumes.'.$foto['album']) }}" aria-label="Ampliar: {{ $foto['titulo'] }}"><x-fotografia :foto="$foto" sizes="(max-width: 650px) 88vw, 60vw" /><span class="momento-ampliar" aria-hidden="true">↗</span></a><figcaption><span>{{ config('fotografias.albumes.'.$foto['album']) }}</span><h3>{{ $foto['titulo'] }}</h3></figcaption></figure>
        @endforeach</div>
    </div>
    <div class="contenedor momentos-pie"><p><strong data-fotos-actual>01</strong><span> / {{ str_pad((string)count($fotosCarrusel), 2, '0', STR_PAD_LEFT) }}</span></p><div class="momentos-progreso" aria-hidden="true"><span data-fotos-progreso></span></div><div class="momentos-controles"><button type="button" data-fotos-anterior aria-label="Fotografía anterior"><x-icono nombre="atras" /></button><button type="button" data-fotos-pausa aria-label="Reproducir carrusel" aria-pressed="true"><span data-fotos-estado>Reproducir</span></button><button type="button" data-fotos-siguiente aria-label="Siguiente fotografía"><x-icono nombre="flecha" /></button></div></div>
    <span class="solo-lectores" data-fotos-aviso role="status" aria-live="polite"></span>
</section>
@endif
