@extends('layouts.acceso')

@section('titulo', 'Servicio temporalmente no disponible')

@section('contenido')
    <p class="antetitulo"><span></span> Hagamos una pequeña pausa</p>
    <h2 id="titulo-formulario">Volvemos<br><em>enseguida.</em></h2>
    <p class="acceso-introduccion">En este momento no podemos completar el acceso o registro. Inténtalo más tarde; mientras tanto, puedes seguir explorando Mil Cumbres.</p>
    <a class="boton boton-bosque boton-formulario" href="{{ route('inicio') }}">Volver al inicio <x-icono nombre="flecha" /></a>
    <form method="POST" action="{{ route('salir') }}" class="formulario-salir">@csrf<button type="submit" class="enlace-salir">Reiniciar el acceso <x-icono nombre="salir" /></button></form>
@endsection
