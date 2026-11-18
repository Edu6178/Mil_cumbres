@extends('layouts.publico')
@section('titulo', 'Créditos fotográficos')
@section('contenido')
    <section class="contenedor publico-manifiesto">
        <p class="antetitulo"><span></span> Una mirada al paisaje</p>
        <h1>Créditos fotográficos.</h1>
        <p>Conoce a las personas detrás de las fotografías que acompañan nuestro sitio.</p>
        <div class="publico-tres-columnas" style="margin-block: 30px">
            @foreach(config('demostracion.fotografias') as $foto)
                <article>
                    <img src="{{ asset('imagenes/demostracion/'.$foto['archivo']) }}" alt="Fotografía de {{ $foto['autor'] }}" width="400" height="260" loading="lazy" style="width:100%;height:180px;object-fit:cover;border-radius:5px">
                    <h2>{{ $foto['autor'] }}</h2>
                    <p><a href="{{ $foto['fuente'] }}" target="_blank" rel="noopener noreferrer">Fotografía original</a> · <a href="{{ $foto['licencia'] }}" target="_blank" rel="noopener noreferrer">Licencia de uso</a></p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
