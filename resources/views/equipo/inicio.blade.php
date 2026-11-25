@extends('layouts.publico')
@section('titulo', 'Mi espacio de equipo')
@section('robots', 'noindex, nofollow')
@section('contenido')
<section class="contenedor equipo-espacio">
    <p class="antetitulo">{{ auth()->user()->nombreRol() }} · Mil Cumbres</p><h1>Hola, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}.</h1><p>Este es tu espacio de trabajo. Aquí aparecen las próximas actividades que te hayan asignado.</p>
    @if(!$personal)<div class="equipo-aviso">Tu cuenta ya está aprobada. El superadministrador puede vincularla a tu ficha laboral para mostrar tus actividades.</div>@endif
    <h2>Mi agenda</h2>
    <div class="equipo-actividades">@forelse($actividades as $actividad)<article><h3>{{ $actividad->nombre_servicio }}</h3><p>{{ $actividad->nombre_region }}</p><p>{{ \Carbon\Carbon::parse($actividad->fecha_inicio)->format('d/m/Y · H:i') }} – {{ \Carbon\Carbon::parse($actividad->fecha_fin)->format('H:i') }}</p><span>{{ ucfirst($actividad->estado) }}</span></article>@empty<p>No tienes actividades próximas asignadas.</p>@endforelse</div>
    <x-administracion.paginacion :registros="$actividades" />
    <a class="boton" href="{{ route('cuenta') }}">Mi cuenta</a>
    @can('administrar')<a class="boton boton-bosque" href="{{ route('administracion.inicio') }}">Administración</a>@endcan
    <form method="POST" action="{{ route('salir') }}">@csrf<button class="enlace-texto" type="submit">Cerrar sesión</button></form>
</section>
@endsection
