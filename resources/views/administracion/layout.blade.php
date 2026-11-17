<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#193e34">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo') · Administración Mil Cumbres</title>
    <link rel="icon" href="{{ asset('imagenes/simbolo.svg') }}" type="image/svg+xml">
    @vite(['resources/css/administracion.css', 'resources/js/administracion.js'])
</head>
<body class="pagina-administracion">
    <a class="adm-saltar" href="#contenido-administracion">Saltar al contenido</a>
    {{-- El menú conserva la misma posición y los permisos se verifican en el servidor. --}}
    <aside class="adm-lateral" id="menu-administracion" aria-label="Menú lateral">
        <div class="adm-identidad"><x-marca :destino="route('administracion.inicio')" /><span class="adm-contexto"><span></span> Administración</span></div>
        <nav aria-label="Administración">
            <p class="adm-grupo">Principal</p>
            <a href="{{ route('administracion.inicio') }}" @if(request()->routeIs('administracion.inicio')) aria-current="page" @endif><x-icono nombre="panel" /><span>Resumen general</span></a>
            <a href="{{ route('administracion.servicios.indice') }}" @if(request()->routeIs('administracion.servicios.*')) aria-current="page" @endif><x-icono nombre="brujula" /><span>Servicios y precios</span></a>
            <a href="{{ route('administracion.categorias.indice') }}" @if(request()->routeIs('administracion.categorias.*')) aria-current="page" @endif><x-icono nombre="carpetas" /><span>Categorías</span></a>
            <p class="adm-grupo">Operación</p>
            <a href="{{ route('administracion.calendario.indice') }}" @if(request()->routeIs('administracion.calendario.*')) aria-current="page" @endif><x-icono nombre="calendario" /><span>Calendario y cupos</span></a>
            <a href="{{ route('administracion.canales.indice') }}" @if(request()->routeIs('administracion.canales.*')) aria-current="page" @endif><x-icono nombre="brujula" /><span>Canales externos</span></a>
            <a href="{{ route('administracion.guias') }}" @if(request()->routeIs('administracion.guias')) aria-current="page" @endif><x-icono nombre="personas" /><span>Equipo de guías</span></a>
            <a href="{{ route('administracion.programaciones') }}" @if(request()->routeIs('administracion.programaciones', 'administracion.asignacion.*')) aria-current="page" @endif><x-icono nombre="calendario" /><span>Asignación de guías</span></a>
            <a href="{{ route('administracion.asistencia') }}" @if(request()->routeIs('administracion.asistencia', 'administracion.participantes')) aria-current="page" @endif><x-icono nombre="lista" /><span>Participantes</span><span class="adm-punto" title="Registro de asistencia pendiente"></span></a>
            <p class="adm-grupo">Mi espacio</p>
            <a href="{{ route('administracion.perfil') }}" @if(request()->routeIs('administracion.perfil*')) aria-current="page" @endif><x-icono nombre="usuario" /><span>Mi perfil</span></a>
            <a href="{{ route('inicio') }}"><x-icono nombre="flecha-diagonal" /><span>Ver página pública</span></a>
            
        </nav>
        <div class="adm-lateral-pie">
            <a class="adm-usuario-lateral" href="{{ route('administracion.perfil') }}"><x-administracion.avatar /><span><strong>{{ auth()->user()->name }}</strong><small>Administración</small></span><x-icono nombre="chevron" /></a>
            <form method="POST" action="{{ route('salir') }}">@csrf<button type="submit" class="adm-cerrar-lateral"><x-icono nombre="salir" /> Cerrar sesión</button></form>
        </div>
    </aside>
    <div class="adm-cuerpo">
        <header class="adm-cabecera">
            <div class="adm-cabecera-izquierda">
                <button type="button" class="adm-menu-boton" aria-controls="menu-administracion" aria-expanded="false" aria-label="Abrir navegación administrativa"><x-icono nombre="menu" /></button>
                <div class="adm-ruta"><span>Administración</span><x-icono nombre="chevron" /><strong>@yield('titulo')</strong></div>
            </div>
            <div class="adm-cabecera-derecha">
                <form class="adm-buscar-cabecera" method="GET" action="{{ route('administracion.servicios.indice') }}" role="search"><x-icono nombre="buscar" /><input type="search" name="buscar" maxlength="100" aria-label="Buscar servicios" placeholder="Buscar un servicio…"><button type="submit" aria-label="Realizar búsqueda"><x-icono nombre="flecha" /></button></form>
                <details class="adm-menu-perfil">
                    <summary aria-label="Abrir opciones de mi cuenta"><x-administracion.avatar /><span><strong>{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</strong><small>Administración</small></span><x-icono nombre="chevron" /></summary>
                    <div class="adm-opciones-perfil"><div><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></div><a href="{{ route('administracion.perfil') }}"><x-icono nombre="usuario" /> Mi perfil</a><a href="{{ route('cuenta') }}"><x-icono nombre="ojo" /> Mi cuenta personal</a><form method="POST" action="{{ route('salir') }}">@csrf<button type="submit"><x-icono nombre="salir" /> Cerrar sesión</button></form></div>
                </details>
            </div>
        </header>
        <main class="adm-principal" id="contenido-administracion">
            @if(session('estado'))<div class="adm-aviso adm-aviso-exito" role="status"><x-icono nombre="correcto" /><span>{{ session('estado') }}</span></div>@endif
            @if($errors->any())
                <div class="adm-aviso adm-aviso-error" role="alert"><x-icono nombre="informacion" /><div><strong>Revisa la información para continuar.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
            @endif
            @yield('contenido')
        </main>
        <footer class="adm-pie"><span>Mil Cumbres · Administración</span><span>Turismo regenerativo</span></footer>
    </div>
</body>
</html>
