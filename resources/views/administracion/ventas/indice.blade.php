@extends('administracion.layout')
@section('titulo', 'Solicitudes y ventas')
@use('App\Services\Finanzas\Dinero')
@section('contenido')
<header class="adm-encabezado adm-revelar"><div><p class="adm-antetitulo">Seguimiento comercial</p><h1>De una solicitud a una venta.</h1><p>Revisa el grupo, confirma el importe acordado y da seguimiento a sus abonos.</p></div><a href="{{ route('administracion.finanzas') }}" class="adm-boton adm-boton-contorno"><x-icono nombre="panel" /> Resumen financiero</a></header>
<section class="adm-panel adm-revelar">
    <form class="adm-filtros" method="GET" role="search">
        <div class="adm-campo adm-busqueda"><label for="buscar">Cliente, experiencia o folio</label><input type="search" id="buscar" name="buscar" maxlength="100" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Escribe un nombre, una experiencia o un folio…" data-buscador-remoto="ventas" data-url-sugerencias="{{ route('administracion.buscar') }}"></div>
        <div class="adm-campo"><label for="estado">Estado de la reservación</label><select id="estado" name="estado"><option value="">Todos los estados</option>@foreach($estados as $estado)<option value="{{ $estado->id }}" @selected(($filtros['estado'] ?? '') == $estado->id)>{{ $estado->nombre_estado }}</option>@endforeach</select></div>
        <button class="adm-boton adm-boton-principal" type="submit"><x-icono nombre="buscar" /> Buscar</button><a class="adm-enlace" href="{{ route('administracion.ventas.indice') }}">Limpiar</a>
    </form>
    @if($ventas->isEmpty())<div class="adm-vacio"><div class="adm-vacio-ilustracion"><x-icono nombre="lista" /></div><h3>No hay solicitudes para mostrar</h3><p>Las pre-reservaciones recibidas aparecerán aquí para revisar sus datos y confirmar una venta.</p></div>
    @else<div class="adm-tabla-contenedor"><table class="adm-tabla"><caption class="adm-solo-lector">Solicitudes, importes pactados y saldos</caption><thead><tr><th scope="col">Cliente y folio</th><th scope="col">Experiencia y grupo</th><th scope="col">Estado</th><th scope="col">Importe del grupo</th><th scope="col">Por cobrar</th><th scope="col">Acción</th></tr></thead><tbody>
    @foreach($ventas as $venta)
    @php
        $importeSolicitud = $venta->monto_total ?? $venta->tarifa_catalogo['importe'];
        $monedaSolicitud = $venta->monto_total !== null ? $venta->moneda : $venta->tarifa_catalogo['moneda'];
    @endphp
    <tr><td><strong>{{ $venta->cliente?->persona?->nombre }} {{ $venta->cliente?->persona?->apellido_paterno }}</strong><small>{{ $venta->folio }}</small></td><td><strong>{{ $venta->programacion?->servicioRegion?->servicio?->nombre_servicio }}</strong><small>{{ $venta->programacion?->fecha_inicio?->format('d/m/Y · H:i') }} · {{ $venta->numero_personas }} personas</small></td><td><span class="fin-estado {{ $venta->estado?->nombre_estado === 'Confirmada' ? 'fin-estado-confirmado' : '' }}">{{ $venta->estado?->nombre_estado }}</span></td><td>@if($importeSolicitud !== null)<strong>{{ $venta->estado?->nombre_estado !== 'Confirmada' && $venta->tarifa_catalogo['tipo'] === 'desde' ? 'Desde ' : '' }}{{ Dinero::mostrar(Dinero::centavos($importeSolicitud)) }} <small>{{ $monedaSolicitud }}</small></strong>@if($venta->estado?->nombre_estado === 'Pre-reserva')<small>Importe ajustable</small>@endif
@else<span class="fin-suave">Por acordar</span>@endif</td><td>@if($venta->estado?->nombre_estado === 'Confirmada'){{ Dinero::mostrar(max(0, Dinero::centavos($venta->monto_total) - Dinero::centavos($venta->cobrado))) }} {{ $monedaSolicitud }}@else
@if($venta->estado?->nombre_estado === 'Pre-reserva' && $importeSolicitud !== null){{ Dinero::mostrar(Dinero::centavos($importeSolicitud)) }} {{ $monedaSolicitud }}<small>Pendiente de confirmar</small>@else<span class="fin-suave">Sin venta confirmada</span>@endif
@endif</td><td><a class="adm-enlace" href="{{ route('administracion.ventas.mostrar', $venta) }}">Revisar <x-icono nombre="flecha" /></a></td></tr>@endforeach
    </tbody></table></div><x-administracion.paginacion :registros="$ventas" />@endif
</section>
@endsection
