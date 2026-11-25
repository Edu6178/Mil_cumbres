@props(['actual', 'antetitulo', 'titulo', 'descripcion', 'imagen' => 'bosque-portada-movil.webp', 'alternativo' => 'Vegetación de un bosque', 'sello' => 'Un viaje que empieza con otra mirada'])
@php
    $claveFoto = match (request()->route()?->getName()) {
        'publico.catalogo' => 'hero-catalogo', 'publico.guias' => 'hero-guias', 'publico.nosotros' => 'hero-nosotros',
        'publico.metodologia' => 'hero-metodologia', 'publico.consultoria' => 'hero-consultoria', 'publico.operadora' => 'hero-operadora',
        'publico.comunidades' => 'hero-comunidades', default => 'inicio-portada',
    };
    $fotoHero = app(\App\Services\Contenido\BibliotecaFotografica::class)->espacio($claveFoto);
@endphp
<section class="publico-hero">
    <div class="contenedor"><x-publico.migas :actual="$actual" />
        <div class="publico-hero-cuadricula">
            <div class="publico-hero-texto" data-entrada><p class="antetitulo"><span></span>{{ $antetitulo }}</p><h1>{{ $titulo }}</h1><p>{{ $descripcion }}</p>{{ $slot }}</div>
            <figure class="publico-hero-foto" data-profundidad><x-fotografia :foto="$fotoHero" :espacio="$claveFoto" loading="eager" sizes="(max-width: 800px) 90vw, 44vw" fetchpriority="high" /><span class="publico-sello"><x-icono nombre="hoja" />{{ $sello }}</span></figure>
        </div>
    </div>
</section>
