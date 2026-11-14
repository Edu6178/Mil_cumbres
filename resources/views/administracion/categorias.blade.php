@extends('administracion.layout')
@section('titulo', 'Categorías')

@section('contenido')
    <div class="adm-encabezado"><div><p class="adm-antetitulo">Una propuesta bien organizada</p><h1>Cada experiencia,<br><em>en su lugar.</em></h1><p>Agrupa tus servicios para mantener un catálogo fácil de recorrer.</p></div></div>
    <div class="adm-dos-columnas">
        <section class="adm-panel adm-revelar"><div class="adm-panel-titulo"><div><h2>Categorías del catálogo</h2><p>{{ $categorias->total() }} {{ $categorias->total() == 1 ? 'categoría registrada' : 'categorías registradas' }}</p></div><x-icono nombre="carpetas" /></div>
            @if($categorias->isEmpty())<x-administracion.vacio titulo="Todo comienza con un poco de orden" descripcion="Crea tu primera categoría y después úsala al registrar tus servicios." icono="carpetas" />
            @else<div class="adm-tabla-contenedor"><table class="adm-tabla"><thead><tr><th>Categoría</th><th>Servicios</th><th class="adm-alinear-derecha">Acciones</th></tr></thead><tbody>@foreach($categorias as $categoria)<tr><td><strong>{{ $categoria->nombre_categoria }}</strong>@if($categoria->created_at?->gt(now()->subMinutes(10)))<small class="adm-reciente">Recientemente creada</small>@endif</td><td>{{ $categoria->servicios_count }}</td><td class="adm-alinear-derecha"><a class="adm-enlace" href="{{ route('administracion.categorias.editar', $categoria) }}">Editar</a></td></tr>@endforeach</tbody></table></div><x-administracion.paginacion :registros="$categorias" />@endif
        </section>
        <aside><section class="adm-panel adm-panel-formulario"><div class="adm-panel-titulo"><div><h2>{{ $categoriaEdicion->exists ? 'Editar categoría' : 'Nueva categoría' }}</h2><p>Un nombre claro para tus experiencias.</p></div></div>
            <form method="POST" action="{{ $categoriaEdicion->exists ? route('administracion.categorias.actualizar', $categoriaEdicion) : route('administracion.categorias.guardar') }}" class="adm-campos" data-formulario-administracion>
                @csrf @if($categoriaEdicion->exists) @method('PUT') @endif
                <div class="adm-campo"><label for="nombre_categoria">Nombre de la categoría <span>*</span></label><input id="nombre_categoria" name="nombre_categoria" value="{{ old('nombre_categoria', $categoriaEdicion->nombre_categoria) }}" maxlength="150" required placeholder="Nombre de la categoría"></div>
                <button type="submit" class="adm-boton adm-boton-principal"><x-icono nombre="correcto" /><span>{{ $categoriaEdicion->exists ? 'Guardar cambios' : 'Crear categoría' }}</span></button>
                @if($categoriaEdicion->exists)<a class="adm-enlace" href="{{ route('administracion.categorias.indice') }}">Cancelar edición</a>@endif
            </form>
        </section><div class="adm-ayuda"><x-icono nombre="informacion" /><p>Editar el nombre conserva la relación con los servicios de esta categoría.</p></div></aside>
    </div>
@endsection
