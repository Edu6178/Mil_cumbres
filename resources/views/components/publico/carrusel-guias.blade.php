@props(['guias', 'identificador' => 'equipo', 'individual' => false, 'buscar' => ''])
@php
    // Reutiliza las mismas fichas y conserva la búsqueda cuando JavaScript no está disponible.
    $guias = collect($guias)->values();
    $normalizar = fn ($texto) => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $texto));
@endphp
<div class="publico-carrusel {{ $individual ? 'publico-carrusel-individual' : 'publico-carrusel-equipo' }}" data-carrusel-guias role="region" aria-roledescription="carrusel" aria-label="{{ $individual ? 'Conoce a nuestros guías' : 'Perfiles del equipo' }}">
    <div class="publico-carrusel-ventana" data-carrusel-ventana>
        <div class="publico-guias-cuadricula" id="{{ $identificador }}-fichas" data-carrusel-pista>
            @foreach($guias as $indice => $guia)
                @php($texto = $guia['nombre'].' '.($guia['especialidad'] ?? '').' '.($guia['talentos'] ?? ''))
                <div data-guia data-carrusel-ficha data-indice="{{ $indice }}" data-nombre="{{ $guia['nombre'] }}" data-busqueda="{{ $texto }}" @if(!str_contains($normalizar($texto), $normalizar(trim($buscar)))) hidden @endif>
                    <x-publico.guia :guia="$guia" />
                </div>
            @endforeach
        </div>
    </div>
    <div class="publico-carrusel-controles" data-carrusel-controles hidden>
        <div class="publico-carrusel-posicion"><span data-carrusel-actual>01</span><span class="publico-carrusel-separador">/</span><span data-carrusel-total>{{ str_pad((string) $guias->count(), 2, '0', STR_PAD_LEFT) }}</span></div>
        <div class="publico-carrusel-puntos" aria-label="Elegir un guía">
            @foreach($guias as $indice => $guia)
                <button type="button" data-carrusel-ir="{{ $indice }}" aria-label="Mostrar a {{ $guia['nombre'] }}" aria-controls="{{ $identificador }}-fichas" @if($indice === 0) aria-current="true" @endif><span></span></button>
            @endforeach
        </div>
        <div class="publico-carrusel-botones">
            <button type="button" data-carrusel-anterior aria-label="Guía anterior" aria-controls="{{ $identificador }}-fichas"><x-icono nombre="atras" /></button>
            <button type="button" class="publico-carrusel-reproduccion" data-carrusel-pausa aria-label="Pausar carrusel" aria-pressed="false">
                <svg class="publico-carrusel-reloj" viewBox="0 0 44 44" aria-hidden="true"><circle cx="22" cy="22" r="19" pathLength="100" /><circle cx="22" cy="22" r="19" pathLength="100" data-carrusel-progreso /></svg>
                <svg class="icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path data-icono-pausa d="M9 6v12M15 6v12"/><path data-icono-reproducir d="m9 5 10 7-10 7V5Z" hidden/></svg>
            </button>
            <button type="button" data-carrusel-siguiente aria-label="Guía siguiente" aria-controls="{{ $identificador }}-fichas"><x-icono nombre="flecha" /></button>
        </div>
    </div>
    <p class="solo-lectores" data-carrusel-aviso role="status" aria-live="polite" aria-atomic="true"></p>
</div>
