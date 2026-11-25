@extends('layouts.publico')
@section('titulo', 'Créditos fotográficos')
@section('contenido')
    <section class="contenedor publico-manifiesto">
        <p class="antetitulo"><span></span> Una mirada al paisaje</p>
        <h1>Créditos fotográficos.</h1>
        <p>Las fotografías de recorridos, naturaleza y momentos compartidos fueron proporcionadas por Mil Cumbres para esta propuesta.</p>
        <p>Conoce la colección en la <a class="enlace-texto" href="{{ route('publico.galeria') }}">galería de momentos <x-icono nombre="flecha" /></a>.</p>
        <h2 style="margin-top: 30px">Retratos de referencia</h2><p>Se conservan las imágenes de los perfiles de muestra mientras se cuenta con la información autorizada del equipo.</p>
        <div class="publico-tres-columnas" style="margin-block: 30px">
            @foreach(collect(config('demostracion.fotografias'))->filter(fn ($foto, $clave) => str_starts_with($clave, 'guia-')) as $foto)
                <article>
                    <img src="{{ asset('imagenes/demostracion/'.$foto['archivo']) }}" alt="Fotografía de {{ $foto['autor'] }}" width="400" height="260" loading="lazy" style="width:100%;height:180px;object-fit:cover;border-radius:5px">
                    <h2>{{ $foto['autor'] }}</h2>
                    <p><a href="{{ $foto['fuente'] }}" target="_blank" rel="noopener noreferrer">Fotografía original</a> · <a href="{{ $foto['licencia'] }}" target="_blank" rel="noopener noreferrer">Licencia de uso</a></p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
