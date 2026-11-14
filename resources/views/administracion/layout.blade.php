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
    {{-- La navegación es compartida por el panel; la autorización se comprueba en todas sus rutas. --}}
    <aside class="adm-lateral" id="menu-administracion">
        <div class="adm-identidad"><x-marca /><span class="adm-contexto">Espacio de administración</span></div>
        <p class="adm-grupo">Tu operación</p>
        <nav aria-label="Administración">
            <a href="{{ route('administracion.inicio') }}" @if(request()->routeIs('administracion.inicio')) aria-current="page" @endif><x-icono nombre="panel" /> Resumen</a>
            <a href="{{ route('administracion.servicios.indice') }}" @if(request()->routeIs('administracion.servicios.*')) aria-current="page" @endif><x-icono nombre="brujula" /> Servicios y precios</a>
            <a href="{{ route('administracion.categorias.indice') }}" @if(request()->routeIs('administracion.categorias.*')) aria-current="page" @endif><x-icono nombre="carpetas" /> Categorías</a>
            <span class="adm-grupo">Personas y actividades</span>
            <a href="{{ route('administracion.guias') }}" @if(request()->routeIs('administracion.guias')) aria-current="page" @endif><x-icono nombre="personas" /> Equipo de guías</a>
            <a href="{{ route('administracion.programaciones') }}" @if(request()->routeIs('administracion.programaciones', 'administracion.asignacion.*')) aria-current="page" @endif><x-icono nombre="calendario" /> Asignación de guías</a>
            <a href="{{ route('administracion.asistencia') }}" @if(request()->routeIs('administracion.asistencia', 'administracion.participantes')) aria-current="page" @endif><x-icono nombre="lista" /> Participantes <span class="adm-punto" title="Registro de asistencia pendiente"></span></a>
        </nav>
        <div class="adm-lateral-pie">
            <div class="adm-nota-lateral"><x-icono nombre="hoja" /><p>Cuidar cada detalle también es parte del viaje.</p></div>
            <a href="{{ route('inicio') }}"><x-icono nombre="flecha-diagonal" /> Ver página pública</a>
        </div>
    </aside>
    <div class="adm-cuerpo">
        <header class="adm-cabecera">
            <div class="adm-cabecera-izquierda">
                <button type="button" class="adm-menu-boton" aria-controls="menu-administracion" aria-expanded="false" aria-label="Abrir navegación administrativa"><x-icono nombre="menu" /></button>
                <span>Mil Cumbres <span class="adm-separador">/</span> <strong>@yield('titulo')</strong></span>
            </div>
            <div class="adm-cabecera-derecha"><span class="adm-etiqueta-acceso">Acceso autorizado</span><span class="adm-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span class="adm-nombre-usuario">{{ auth()->user()->name }}</span><form method="POST" action="{{ route('salir') }}">@csrf<button type="submit" class="adm-salir" aria-label="Cerrar sesión"><x-icono nombre="salir" /></button></form></div>
        </header>
        <main class="adm-principal" id="contenido-administracion">
            @if(session('estado'))<div class="adm-aviso adm-aviso-exito" role="status"><x-icono nombre="correcto" /><span>{{ session('estado') }}</span></div>@endif
            @if($errors->any())
                <div class="adm-aviso adm-aviso-error" role="alert"><x-icono nombre="informacion" /><div><strong>Revisa la información para continuar.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
            @endif
            @yield('contenido')
        </main>
        <footer class="adm-pie"><span>Mil Cumbres · Turismo regenerativo</span><span>Conexión con el territorio, cuidado en la operación.</span></footer>
    </div>
</body>
</html>
