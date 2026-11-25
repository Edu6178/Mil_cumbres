@props(['venta'])
@use('App\Services\Finanzas\Dinero')
@php($tarifa = $venta->tarifa_catalogo)
<section class="adm-panel"><div class="adm-panel-titulo"><div><h2>{{ $tarifa['guardada'] ? 'Precio al solicitar' : 'Tarifa actual del catálogo' }}</h2><p>{{ $tarifa['guardada'] ? 'Este precio se conserva aunque después cambie el catálogo.' : 'Sugerencia para esta solicitud anterior; no representa un precio histórico guardado.' }}</p></div><x-icono nombre="pago" /></div>
    <dl class="fin-datos"><div><dt>Tarifa publicada</dt><dd>@if($tarifa['precio'] !== null){{ $tarifa['tipo'] === 'desde' ? 'Desde ' : '' }}{{ Dinero::mostrar(Dinero::centavos($tarifa['precio'])) }} {{ $tarifa['moneda'] }} {{ \App\Services\Finanzas\TarifaReserva::UNIDADES[$tarifa['unidad']] ?? '· unidad por confirmar' }}@else Cotización personalizada @endif</dd></div>
    @if($tarifa['importe'] !== null)<div><dt>{{ $tarifa['tipo'] === 'desde' ? 'Total inicial desde' : 'Total según catálogo' }}</dt><dd>{{ Dinero::mostrar(Dinero::centavos($tarifa['importe'])) }} {{ $tarifa['moneda'] }}@if($tarifa['unidad'] === 'persona')<small>{{ $venta->numero_personas }} personas × {{ Dinero::mostrar(Dinero::centavos($tarifa['precio'])) }}</small>@endif</dd></div>@endif</dl>
</section>
