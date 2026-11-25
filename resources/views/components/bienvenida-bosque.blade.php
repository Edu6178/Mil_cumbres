@php
    $bosqueFoto = app(\App\Services\Contenido\BibliotecaFotografica::class)->espacio('bosque');
@endphp
<div class="bienvenida-bosque" data-bienvenida-bosque role="dialog" aria-modal="true" aria-labelledby="titulo-bienvenida" aria-hidden="true">
    <div class="bosque-puerta bosque-puerta-izquierda" aria-hidden="true">@if($bosqueFoto)<img src="{{ $bosqueFoto['url'] }}" alt="" width="{{ $bosqueFoto['ancho'] }}" height="{{ $bosqueFoto['alto'] }}">@endif<span></span></div>
    <div class="bosque-puerta bosque-puerta-derecha" aria-hidden="true">@if($bosqueFoto)<img src="{{ $bosqueFoto['url'] }}" alt="" width="{{ $bosqueFoto['ancho'] }}" height="{{ $bosqueFoto['alto'] }}">@endif<span></span></div>
    <div class="bosque-luz" aria-hidden="true"></div>
    <div class="bosque-ramas" aria-hidden="true">
        @foreach(['izquierda', 'derecha'] as $lado)
            <svg class="bosque-rama bosque-rama-{{ $lado }}" viewBox="0 0 360 800" fill="none">
                <path d="M-20 810Q100 540 52 280T12-30M50 320Q160 210 310 180M65 490Q140 385 265 355M55 245Q125 140 148 30" stroke="currentColor" stroke-width="17" stroke-linecap="round" />
                @foreach([[110,240,-30],[190,200,15],[260,180,-20],[125,420,-35],[205,370,20],[91,160,-50],[145,80,-15]] as [$x, $y, $rotacion])
                    <path d="M0 0Q-34-55 0-95Q42-48 0 0Z" transform="translate({{ $x }} {{ $y }}) rotate({{ $rotacion }})" fill="currentColor" />
                @endforeach
            </svg>
        @endforeach
    </div>
    <div class="bosque-hojarasca" aria-hidden="true">
        @for($hoja = 0; $hoja < 24; $hoja++)
            <i class="bosque-hoja" style="--x:{{ ($hoja * 37 + 9) % 100 }}%;--demora:{{ ($hoja % 7) * -0.19 }}s;--giro:{{ ($hoja * 43) % 180 }}deg;--deriva:{{ ($hoja % 2 ? 1 : -1) * (35 + $hoja % 5 * 16) }}px;--tamano:{{ 12 + $hoja % 5 * 4 }}px;--tono:{{ $hoja % 3 }}"></i>
        @endfor
    </div>
    <div class="bosque-sendero" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
    <div class="bosque-mensaje"><img src="{{ asset('imagenes/simbolo.svg') }}" alt="" width="60" height="60"><p>Mil Cumbres</p><h2 id="titulo-bienvenida">El bosque abre<br><em>otro camino.</em></h2><span>Menos prisa. Más conexión.</span></div>
    <button type="button" class="bosque-omitir" data-omitir-bienvenida onclick="document.documentElement.classList.remove('bosque-pendiente');window.dispatchEvent(new Event('milcumbres:omitir-bosque'))">Entrar al sitio <span aria-hidden="true">→</span></button>
</div>
