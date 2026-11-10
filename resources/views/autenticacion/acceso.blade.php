@extends('layouts.acceso')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <p class="antetitulo"><span></span> Vuelve al camino</p>
    <h2 id="titulo-formulario">Qué gusto verte<br><em>de nuevo.</em></h2>
    <p class="acceso-introduccion">Entra a tu cuenta y sigue conectando con Mil Cumbres.</p>

    <nav class="acceso-pestanas" aria-label="Acceso a tu cuenta">
        <a href="{{ route('acceso') }}" aria-current="page">Iniciar sesión</a>
        <a href="{{ route('registro') }}">Crear cuenta</a>
    </nav>

    @if (session('estado'))
        <p class="aviso-formulario aviso-correcto" role="status"><x-icono nombre="hoja" /> {{ session('estado') }}</p>
    @endif

    {{-- Laravel verifica el formulario y el token antes de intentar iniciar la sesión. --}}
    <form class="formulario-cuenta" method="POST" action="{{ route('acceso.entrar') }}" data-formulario-cuenta>
        @csrf
        <div class="campo">
            <label for="correo">Correo electrónico</label>
            <input id="correo" name="correo" type="email" value="{{ old('correo') }}" required maxlength="254" autocomplete="username" inputmode="email" spellcheck="false" placeholder="Tu correo electrónico" @error('correo') aria-invalid="true" aria-describedby="error-correo" @enderror>
            @error('correo')<p class="error-campo" id="error-correo" role="alert">{{ $message }}</p>@enderror
        </div>
        <div class="campo">
            <label for="contrasena">Contraseña</label>
            <div class="campo-contrasena">
                <input id="contrasena" name="contrasena" type="password" required maxlength="72" autocomplete="current-password" placeholder="Tu contraseña" @error('contrasena') aria-invalid="true" aria-describedby="error-contrasena" @enderror>
                <button type="button" class="alternar-contrasena" data-alternar-contrasena aria-controls="contrasena" aria-pressed="false" aria-label="Mostrar contraseña"><x-icono nombre="ojo" /><span>Mostrar</span></button>
            </div>
            @error('contrasena')<p class="error-campo" id="error-contrasena" role="alert">{{ $message }}</p>@enderror
        </div>
        <button class="boton boton-bosque boton-formulario" type="submit"><span data-texto-envio>Entrar a mi cuenta</span><x-icono nombre="flecha" /></button>
    </form>

    <p class="acceso-cambio">¿Es tu primera visita? <a href="{{ route('registro') }}">Crea tu cuenta</a></p>
    <div class="acceso-nota"><x-icono nombre="brujula" /><p>También puedes <a href="{{ route('inicio') }}#experiencias">explorar nuestra propuesta</a> sin iniciar sesión.</p></div>
@endsection