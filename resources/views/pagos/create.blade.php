@extends('administracion.layout')
@section('titulo', 'Registrar pago')
@use('App\Services\Finanzas\Dinero')
@section('contenido')
<a class="adm-enlace adm-volver" href="{{ route('pagos.index') }}"><x-icono nombre="atras" /> Volver a pagos y abonos</a>
<header class="adm-encabezado adm-revelar"><div><p class="adm-antetitulo">Nuevo registro</p><h1>Un paso más para organizar el cobro.</h1><p>Asocia el importe a una venta y conserva sus referencias para la revisión manual.</p></div></header>
@if($reservaciones->isEmpty() || $metodos->isEmpty())
<section class="adm-panel"><div class="adm-vacio"><div class="adm-vacio-ilustracion"><x-icono nombre="pago" /></div><h3>{{ $reservaciones->isEmpty() ? 'Primero confirma una venta' : 'Faltan los métodos de pago' }}</h3><p>{{ $reservaciones->isEmpty() ? 'Revisa una solicitud y guarda su importe acordado para comenzar a registrar sus pagos.' : 'El catálogo de métodos de pago debe estar preparado para registrar un abono.' }}</p><a href="{{ route('administracion.ventas.indice') }}" class="adm-boton adm-boton-principal">Revisar solicitudes</a></div></section>
@else
<div class="fin-distribucion"><form class="adm-panel" method="POST" action="{{ route('pagos.store') }}" data-formulario-administracion>@csrf
    {{-- La clave evita duplicar el pago si se reenvía este mismo formulario. --}}
    <input type="hidden" name="clave_registro" value="{{ old('clave_registro', $clave) }}">
    <div class="adm-panel-titulo"><div><h2>Datos del pago</h2><p>Todos los registros comienzan pendientes de revisión.</p></div><x-icono nombre="pago" /></div>
    <div class="fin-formulario">
        <div class="adm-campo"><label for="reservacion_id">Venta confirmada</label><select id="reservacion_id" name="reservacion_id" required><option value="">Selecciona una venta</option>@foreach($reservaciones as $venta)<option value="{{ $venta->id }}" data-moneda="{{ $venta->moneda }}" @selected(old('reservacion_id', $seleccion) == $venta->id)>{{ $venta->folio }} · {{ $venta->cliente?->persona?->nombre }} {{ $venta->cliente?->persona?->apellido_paterno }} · Saldo {{ Dinero::mostrar(max(0, Dinero::centavos($venta->monto_total) - Dinero::centavos($venta->cobrado))) }} {{ $venta->moneda }}</option>@endforeach</select></div>
        <div class="fin-campos"><x-administracion.campo-financiero nombre="monto" etiqueta="Importe de este abono" inputmode="decimal" maxlength="13" pattern="[0-9]{1,10}(\.[0-9]{1,2})?" placeholder="0.00" /><div class="adm-campo"><label for="moneda">Moneda</label><select id="moneda" name="moneda" required>@foreach($monedas as $moneda)<option @selected(old('moneda') === $moneda)>{{ $moneda }}</option>@endforeach</select><small>Debe coincidir con la moneda de la venta.</small></div></div>
        <div class="adm-campo"><label for="metodo_pago_id">Método de pago</label><select id="metodo_pago_id" name="metodo_pago_id" required><option value="">Selecciona un método</option>@foreach($metodos as $metodo)<option value="{{ $metodo->id }}" @selected(old('metodo_pago_id') == $metodo->id)>{{ $metodo->metodo }}</option>@endforeach</select></div>
        <div class="fin-separador"><h3>Enlace y proveedor</h3><p>Completa estos datos si el pago se gestiona con un enlace externo.</p></div>
        <x-administracion.campo-financiero nombre="proveedor" etiqueta="Proveedor" opcional maxlength="120" placeholder="Nombre del proveedor" />
        <x-administracion.campo-financiero nombre="url_pago" etiqueta="Enlace de pago" tipo="url" opcional maxlength="2000" placeholder="https://…" />
        <x-administracion.campo-financiero nombre="proveedor_referencia" etiqueta="Referencia del proveedor" opcional maxlength="180" />
        <div class="fin-separador"><h3>Comprobante recibido</h3><p>Puedes registrarlo ahora y confirmar el cobro al terminar de revisarlo.</p></div>
        <x-administracion.campo-financiero nombre="referencia_comprobante" etiqueta="Referencia del comprobante" opcional maxlength="180" />
        <x-administracion.campo-financiero nombre="fecha_pago" :etiqueta="'Fecha y hora del pago ('.config('app.timezone').')'" tipo="datetime-local" opcional :max="now()->format('Y-m-d\TH:i')" />
        <button type="submit" class="adm-boton adm-boton-principal"><x-icono nombre="correcto" /><span>Guardar para revisión</span></button>
    </div>
</form><aside class="fin-resumen"><x-icono nombre="hoja" /><p class="adm-antetitulo">Control manual</p><h2>El registro conserva la información.</h2><p>Un enlace guardado no equivale a un cobro. Después podrás revisar el comprobante y confirmar el importe recibido.</p><dl><div><dt>Abonos parciales</dt><dd>Se descuentan del saldo al confirmarlos.</dd></div><div><dt>Monedas separadas</dt><dd>Los importes no se convierten ni se suman entre monedas.</dd></div></dl><a href="{{ route('administracion.ventas.indice') }}" class="adm-boton">Consultar ventas <x-icono nombre="flecha" /></a></aside></div>
@endif
@endsection
