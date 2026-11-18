@props(['actual', 'antetitulo', 'titulo', 'descripcion', 'imagen' => 'bosque-portada-movil.webp', 'alternativo' => 'Vegetación de un bosque', 'sello' => 'Un viaje que empieza con otra mirada'])
<section class="publico-hero">
    <div class="contenedor"><x-publico.migas :actual="$actual" />
        <div class="publico-hero-cuadricula">
            <div class="publico-hero-texto" data-entrada><p class="antetitulo"><span></span>{{ $antetitulo }}</p><h1>{{ $titulo }}</h1><p>{{ $descripcion }}</p>{{ $slot }}</div>
            <figure class="publico-hero-foto"><img src="{{ asset('imagenes/'.$imagen) }}" alt="{{ $alternativo }}" width="800" height="600" fetchpriority="high" decoding="async"><span class="publico-sello"><x-icono nombre="hoja" />{{ $sello }}</span></figure>
        </div>
    </div>
</section>
