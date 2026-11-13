@extends('layouts.acceso')

@section('titulo', 'Mi cuenta')

@section('contenido')
    <p class="antetitulo"><span></span> Tu punto de partida</p>
    <h2 id="titulo-formulario">Ya estás<br><em>en el camino.</em></h2>
    <p class="acceso-introduccion">Qué gusto tenerte aquí, {{ auth()->user()->name }}.</p>

    @if (session('estado'))
        <p class="aviso-formulario aviso-correcto" role="status"><x-icono nombre="hoja" /> {{ session('estado') }}</p>
    @endif

    {{-- Se muestran únicamente los datos reales de la cuenta autenticada. --}}
    <div class="resumen-cuenta">
        <span class="avatar-cuenta" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <div><span class="numero-seccion">Cuenta personal</span><h3>{{ auth()->user()->name }}</h3><p>{{ auth()->user()->email }}</p></div>
    </div>
    <div class="cuenta-propuesta"><x-icono nombre="ruta" /><div><h3>Un nuevo comienzo, al aire libre.</h3><p>El catálogo sigue tomando forma. Mientras tanto, conoce nuestra propuesta y lo que nos mueve.</p></div></div>
    <a class="boton boton-bosque boton-formulario" href="{{ route('inicio') }}#experiencias">Explorar Mil Cumbres <x-icono nombre="flecha" /></a>
    <form method="POST" action="{{ route('salir') }}" class="formulario-salir">@csrf<button type="submit" class="enlace-salir">Cerrar sesión <x-icono nombre="salir" /></button></form>
@endsection