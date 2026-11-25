@extends('layouts.publico')
@section('titulo', 'Tu pre-reservación')
@section('robots', 'noindex, nofollow')
@section('contenido')
    <section class="contenedor resumen-personal">
        <div class="resumen-personal-saludo"><x-icono nombre="hoja" /><p class="antetitulo">Tu próxima experiencia</p><h1>Hola, {{ $solicitud->cliente->persona->nombre }}.<br><em>El camino empieza aquí.</em></h1><p>Este es el resumen de tu solicitud en Mil Cumbres.</p></div>
        <div class="resumen-personal-tarjeta"><p class="numero-seccion">{{ $solicitud->folio }}</p><h2>{{ $solicitud->programacion->servicioRegion->servicio->nombre_servicio }}</h2><dl><div><dt>Fecha y horario</dt><dd>{{ $solicitud->programacion->fecha_inicio->format('d/m/Y · H:i') }} hrs</dd></div><div><dt>Territorio</dt><dd>{{ $solicitud->programacion->servicioRegion->region->nombre_region }}</dd></div><div><dt>Personas</dt><dd>{{ $solicitud->numero_personas }}</dd></div><div><dt>Estado actual</dt><dd>{{ $solicitud->estado->nombre_estado }}</dd></div></dl><a class="boton boton-bosque" href="#siguientes-pasos">Ver los siguientes pasos <x-icono nombre="flecha" /></a><a class="enlace-texto" href="{{ $pdf }}" download rel="noreferrer">Descargar mi pre-reserva <x-icono nombre="descargar" /></a></div>
        <div class="resumen-personal-pasos" id="siguientes-pasos"><p class="antetitulo"><span></span> Sin prisas, paso a paso</p><h2>¿Qué sigue?</h2><ol><li><strong>Conserva tu folio.</strong> El comprobante reúne los datos de la solicitud y de tu grupo.</li><li><strong>Espera la confirmación del equipo.</strong> Consultar esta página no confirma ni modifica tu pre-reserva.</li><li><strong>Medios de pago por confirmar.</strong> Te compartiremos la información cuando esté disponible.</li></ol><a class="enlace-texto" href="{{ route('publico.catalogo') }}">Seguir explorando <x-icono nombre="flecha" /></a></div>
    </section>
@endsection
