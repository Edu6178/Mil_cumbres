@php
    // Se usa una silueta neutral hasta que la persona suba su propia foto.
    $tieneFoto = \Illuminate\Support\Facades\Storage::disk('local')->exists('perfiles/'.auth()->id().'/foto');
@endphp
<img {{ $attributes->class(['adm-foto-perfil']) }} src="{{ $tieneFoto ? route('administracion.perfil.foto') : asset('imagenes/perfil.svg') }}" alt="" width="80" height="80">
