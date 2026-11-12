@extends('layouts.acceso')

@section('titulo', 'Crear cuenta')

@section('contenido')
    <p class="antetitulo"><span></span> Este puede ser el comienzo</p>
    <h2 id="titulo-formulario">Una cuenta.<br><em>Nuevos caminos.</em></h2>
    <p class="acceso-introduccion">Haz de Mil Cumbres tu próximo punto de encuentro.</p>

    <nav class="acceso-pestanas" aria-label="Acceso a tu cuenta">
        <a href="{{ route('acceso') }}">Iniciar sesión</a>
        <a href="{{ route('registro') }}" aria-current="page">Crear cuenta</a>
    </nav>

    {{-- Solo se solicitan los datos que la tabla actual de usuarios puede guardar. --}}
    <form class="formulario-cuenta" method="POST" action="{{ route('registro.guardar') }}" data-formulario-cuenta>
        @csrf
        <div class="campo">
            <label for="nombre">Nombre completo</label>
            <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" required maxlength="120" autocomplete="name" placeholder="¿Cómo te llamas?" @error('nombre') aria-invalid="true" aria-describedby="error-nombre" @enderror>
            @error('nombre')<p class="error-campo" id="error-nombre" role="alert">{{ $message }}</p>@enderror
        </div>
        <div class="campo">
            <label for="correo">Correo electrónico</label>
            <input id="correo" name="correo" type="email" value="{{ old('correo') }}" required maxlength="254" autocomplete="username" inputmode="email" spellcheck="false" placeholder="Tu correo electrónico" @error('correo') aria-invalid="true" aria-describedby="error-correo" @enderror>
            @error('correo')<p class="error-campo" id="error-correo" role="alert">{{ $message }}</p>@enderror
        </div>
        <div class="campo">
            <label for="contrasena">Contraseña</label>
            <div class="campo-contrasena">
                <input id="contrasena" name="contrasena" type="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Crea una contraseña" aria-describedby="ayuda-contrasena @error('contrasena') error-contrasena @enderror" @error('contrasena') aria-invalid="true" @enderror>
                <button type="button" class="alternar-contrasena" data-alternar-contrasena aria-controls="contrasena" aria-pressed="false" aria-label="Mostrar contraseña"><x-icono nombre="ojo" /><span>Mostrar</span></button>
            </div>
            <p class="ayuda-campo" id="ayuda-contrasena">Al menos 8 caracteres, una letra y un número.</p>
            @error('contrasena')<p class="error-campo" id="error-contrasena" role="alert">{{ $message }}</p>@enderror
        </div>
        <div class="campo">
            <label for="contrasena_confirmation">Confirmar contraseña</label>
            <div class="campo-contrasena">
                <input id="contrasena_confirmation" name="contrasena_confirmation" type="password" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Repite tu contraseña">
                <button type="button" class="alternar-contrasena" data-alternar-contrasena aria-controls="contrasena_confirmation" aria-pressed="false" aria-label="Mostrar confirmación de contraseña"><x-icono nombre="ojo" /><span>Mostrar</span></button>
            </div>
        </div>
        <button class="boton boton-bosque boton-formulario" type="submit"><span data-texto-envio>Crear mi cuenta</span><x-icono nombre="flecha" /></button>
    </form>

    <p class="acceso-cambio">¿Ya tienes una cuenta? <a href="{{ route('acceso') }}">Inicia sesión</a></p>
    <div class="acceso-nota"><x-icono nombre="hoja" /><p>Tu cuenta es personal. Puedes seguir <a href="{{ route('inicio') }}#experiencias">explorando Mil Cumbres</a> sin registrarte.</p></div>
@endsection