@extends('administracion.layout')
@section('titulo', 'Mi perfil')

@section('contenido')
    <div class="adm-encabezado"><div><p class="adm-antetitulo">Tu cuenta</p><h1>Mi perfil</h1><p>Consulta tu información y personaliza tu foto de perfil.</p></div><a class="adm-boton adm-boton-contorno" href="{{ route('administracion.inicio') }}"><x-icono nombre="atras" /> Volver al resumen</a></div>
    <div class="adm-perfil-cuadricula">
        <section class="adm-panel adm-perfil-identidad">
            <div class="adm-perfil-portada"></div>
            <x-administracion.avatar class="adm-foto-grande" />
            <h2>{{ $usuario->name }}</h2>
            <span class="adm-insignia adm-insignia-verde"><span class="adm-punto-estado"></span> {{ $usuario->nombreRol() }}</span>
            <p>{{ $usuario->email }}</p>
            <span class="adm-perfil-alta">Cuenta creada el {{ $usuario->created_at?->format('d/m/Y') ?: 'Fecha no disponible' }}</span>
        </section>
        <div class="adm-perfil-detalles">
            <section class="adm-panel"><div class="adm-panel-titulo"><div><h2>Información de la cuenta</h2><p>Datos asociados a tu acceso.</p></div><x-icono nombre="usuario" /></div>
                <dl class="adm-datos-perfil"><div><dt>Nombre</dt><dd>{{ $usuario->name }}</dd></div><div><dt>Correo electrónico</dt><dd>{{ $usuario->email }}</dd></div><div><dt>Acceso</dt><dd>{{ $usuario->nombreRol() }}</dd></div><div><dt>Fecha de alta</dt><dd>{{ $usuario->created_at?->format('d/m/Y') ?: 'Sin fecha registrada' }}</dd></div></dl>
            </section>
            <section class="adm-panel"><div class="adm-panel-titulo"><div><h2>Foto de perfil</h2><p>Aparecerá en la cabecera, el menú lateral y tu perfil.</p></div><x-icono nombre="camara" /></div>
                <form class="adm-formulario-foto" method="POST" action="{{ route('administracion.perfil.guardar-foto') }}" enctype="multipart/form-data" data-formulario-administracion>
                    @csrf
                    <div class="adm-foto-editor"><x-administracion.avatar data-vista-foto /><div><label class="adm-boton adm-boton-contorno" for="foto"><x-icono nombre="camara" /> Elegir foto</label><p data-nombre-foto>Ningún archivo seleccionado</p></div></div>
                    <input class="adm-archivo-foto" id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" required aria-describedby="ayuda-foto" data-archivo-foto @error('foto') aria-invalid="true" @enderror>
                    <p class="adm-texto-ayuda" id="ayuda-foto">JPG, PNG o WebP. Máximo 2 MB y 2000 × 2000 píxeles.</p>
                    <button class="adm-boton adm-boton-principal" type="submit"><x-icono nombre="correcto" /><span>Guardar foto</span></button>
                </form>
            </section>
        </div>
    </div>
@endsection
