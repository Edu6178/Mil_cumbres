@extends('layouts.acceso')

@section('titulo', 'Acceso restringido')

@section('contenido')
    <p class="antetitulo"><span></span> Espacio de administración</p>
    <h2 id="titulo-formulario">Este camino necesita<br><em>una autorización.</em></h2>
    <p class="acceso-introduccion">Tu cuenta no tiene acceso a este apartado. Solicita la autorización a la persona responsable de Mil Cumbres.</p>
    <a class="boton boton-bosque boton-formulario" href="{{ route('cuenta') }}">Volver a mi cuenta <x-icono nombre="flecha" /></a>
@endsection
