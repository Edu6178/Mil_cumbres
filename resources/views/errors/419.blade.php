@extends('layouts.acceso')

@section('titulo', 'Vuelve a abrir el formulario')

@section('contenido')
    <p class="antetitulo"><span></span> Continuemos el camino</p>
    <h2 id="titulo-formulario">Necesitamos<br><em>un nuevo comienzo.</em></h2>
    <p class="acceso-introduccion">El formulario permaneció abierto demasiado tiempo. Vuelve a abrirlo e inténtalo de nuevo.</p>
    <a class="boton boton-bosque boton-formulario" href="{{ route('acceso') }}">Volver al acceso <x-icono nombre="flecha" /></a>
@endsection
