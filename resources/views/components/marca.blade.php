@props(['destino' => route('inicio')])

{{-- La misma marca se utiliza en los formularios y en la cuenta personal. --}}
<a {{ $attributes->class(['marca']) }} href="{{ $destino }}" aria-label="Mil Cumbres, volver al inicio">
    <img src="{{ asset('imagenes/simbolo.svg') }}" alt="" width="47" height="47">
    <span>Mil Cumbres<small>Turismo regenerativo</small></span>
</a>
