@extends('layouts.acceso')

@section('titulo', 'Enlace de pre-reserva no disponible')

@section('contenido')
    <p class="antetitulo"><span></span> Tu pre-reservación</p>
    <h2 id="titulo-formulario">Este enlace ya no<br><em>está disponible.</em></h2>
    <p class="acceso-introduccion">No pudimos validar el enlace del comprobante. La descarga tiene una vigencia de 24 horas; utiliza el botón que aparece después de registrar tu solicitud.</p>
    <p class="acceso-introduccion">Si ya descargaste el archivo, consérvalo junto con tu folio para dar seguimiento con Mil Cumbres.</p>
    <a class="boton boton-bosque boton-formulario" href="{{ route('inicio') }}#pre-reservacion">Volver a Mil Cumbres <x-icono nombre="flecha" /></a>
@endsection
