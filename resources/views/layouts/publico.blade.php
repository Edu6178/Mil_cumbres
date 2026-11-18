<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#193e34">
    <meta name="description" content="@yield('descripcion', 'Mil Cumbres: experiencias, comunidades y una forma de viajar con propósito.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <title>@yield('titulo') · Mil Cumbres</title>
    <link rel="icon" href="{{ asset('imagenes/simbolo.svg') }}" type="image/svg+xml">
    {{-- Se reutiliza la identidad existente; los estilos nuevos se limitan a las páginas públicas. --}}
    <x-entrada-pagina />
    @vite(['resources/css/app.css', 'resources/css/publico.css', 'resources/js/app.js', 'resources/js/publico.js'])
</head>
<body class="pagina-publica">
    <a class="saltar-contenido" href="#contenido">Saltar al contenido</a>
    <header class="cabecera" id="cabecera">
        <div class="contenedor cabecera-interior">
            <x-marca />
            <button class="boton-menu" type="button" aria-expanded="false" aria-controls="navegacion-principal" aria-label="Abrir menú de navegación"><x-icono nombre="menu" /><span>Menú</span></button>
            <nav class="navegacion" id="navegacion-principal" aria-label="Navegación principal">
                <a href="{{ route('publico.catalogo') }}" @if(request()->routeIs('publico.catalogo', 'publico.experiencia*')) aria-current="page" @endif>Experiencias</a>
                <a href="{{ route('publico.nosotros') }}" @if(request()->routeIs('publico.nosotros', 'publico.metodologia', 'publico.operadora')) aria-current="page" @endif>Nuestra esencia</a>
                <a href="{{ route('publico.comunidades') }}" @if(request()->routeIs('publico.comunidades')) aria-current="page" @endif>Comunidades</a>
                <a href="{{ route('publico.guias') }}" @if(request()->routeIs('publico.guias', 'publico.guia*')) aria-current="page" @endif>Guías</a>
                <a class="enlace-acceso" href="{{ route('cuenta') }}"><x-icono nombre="usuario" /> Mi cuenta</a>
                <a class="boton boton-pequeno boton-bosque" href="{{ route('publico.consultoria') }}">Para organizaciones <x-icono nombre="flecha-diagonal" /></a>
            </nav>
        </div>
    </header>
    <main id="contenido">
        @yield('contenido')
    </main>
    <footer class="pie publico-pie">
        <div class="contenedor">
            <div class="pie-superior">
                <div><x-marca /><p>Naturaleza que inspira.<br>Conexiones que permanecen.</p><x-redes-sociales /></div>
                <nav aria-label="Descubrir experiencias"><span>Descubre</span><a href="{{ route('publico.catalogo') }}">Catálogo de experiencias</a><a href="{{ route('publico.catalogo', ['linea' => 'senderismo']) }}">Rutas de senderismo</a><a href="{{ route('publico.guias') }}">Nuestro equipo de guías</a></nav>
                <nav aria-label="Nuestra propuesta"><span>Conecta</span><a href="{{ route('publico.metodologia') }}">Turismo regenerativo</a><a href="{{ route('publico.comunidades') }}">Territorio y comunidades</a><a href="{{ route('publico.consultoria') }}">Consultoría</a></nav>
                <div class="pie-nota"><span>Dos maneras de acompañarte</span><p>Explorar el territorio y construir proyectos con sentido.</p><a class="enlace-texto" href="{{ route('publico.operadora') }}">Conoce la operadora <x-icono nombre="flecha" /></a></div>
            </div>
            <div class="pie-inferior"><p>© {{ date('Y') }} Mil Cumbres. Todos los derechos reservados.</p><a href="{{ route('publico.creditos') }}">Créditos fotográficos</a><a href="#contenido">Volver arriba <x-icono nombre="arriba" /></a></div>
        </div>
    </footer>
</body>
</html>
