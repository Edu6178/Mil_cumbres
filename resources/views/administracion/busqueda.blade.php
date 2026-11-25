@extends('administracion.layout')
@section('titulo', 'Búsqueda general')
@section('contenido')
    <header class="adm-encabezado"><div><p class="adm-antetitulo">Un acceso a todo tu trabajo</p><h1>Encuentra tu siguiente paso.</h1><p>{{ $buscar === '' ? 'Abre un apartado o escribe en el buscador superior.' : 'Coincidencias destacadas para «'.$buscar.'».' }}</p></div></header>
    @if(count($resultados))
        <p class="adm-busqueda-nota">Apartados y registros relacionados. Abre un apartado para consultar y filtrar su lista completa.</p>
        <div class="adm-busqueda-resultados">@foreach($resultados as $resultado)
            <a class="adm-panel adm-resultado-busqueda" href="{{ $resultado['url'] }}"><span class="adm-antetitulo">{{ $resultado['tipo'] }}</span><h2>{{ $resultado['titulo'] }} <x-icono nombre="flecha" /></h2><small>{{ $resultado['contexto'] }}</small><p>{{ $resultado['resumen'] }}</p></a>
        @endforeach</div>
    @else
        <section class="adm-panel"><x-administracion.vacio titulo="No encontramos coincidencias" descripcion="Prueba con el inicio de un nombre, una experiencia, un folio o un apartado como finanzas o calendario." icono="buscar" /></section>
    @endif
@endsection
