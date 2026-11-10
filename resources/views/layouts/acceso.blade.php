<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#193e34">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo') · Mil Cumbres</title>
    <link rel="icon" href="{{ asset('imagenes/simbolo.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pagina-acceso">
    <a class="saltar-contenido" href="#formulario-principal">Saltar al contenido</a>
    <main class="acceso-distribucion">
        {{-- Esta imagen y el texto conservan la identidad de la portada, sin consultar el catálogo. --}}
        <aside class="acceso-paisaje" aria-label="Mil Cumbres, naturaleza y conexión">
            <picture>
                <source media="(max-width: 650px)" srcset="{{ asset('imagenes/bosque-portada-movil.webp') }}">
                <img src="{{ asset('imagenes/bosque-portada.webp') }}" alt="" width="1600" height="1066">
            </picture>
            <div class="acceso-paisaje-sombra"></div>
            <x-marca class="marca-clara" />
            <div class="acceso-relato" data-entrada>
                <p class="antetitulo antetitulo-claro"><span></span> Un lugar para volver a conectar</p>
                <h1>Hay caminos<br>que empiezan<br><em>con un encuentro.</em></h1>
                <p>La naturaleza nos inspira.<br>Las personas le dan sentido al viaje.</p>
            </div>
            <p class="acceso-paisaje-pie"><x-icono nombre="hoja" /> Menos prisa. Más conexión.</p>
        </aside>

        <section class="acceso-contenido" id="formulario-principal" aria-labelledby="titulo-formulario">
            <a class="acceso-volver" href="{{ route('inicio') }}"><x-icono nombre="flecha" /> Volver a Mil Cumbres</a>
            <div class="acceso-formulario" data-entrada>
                @yield('contenido')
            </div>
            <p class="acceso-pie">Naturaleza. Comunidad. Propósito.</p>
        </section>
    </main>
</body>
</html>
