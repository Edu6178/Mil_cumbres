@props(['nombre' => 'flecha'])

{{-- Iconos locales reutilizables: heredan el color y no necesitan bibliotecas externas. --}}
<svg {{ $attributes->class(['icono']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($nombre)
        @case('pago')
            <rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20M6 15h3m3 0h2"/>
            @break
        @case('panel')
            <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
            @break
        @case('carpetas')
            <path d="M3 7V5a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Zm0 2h18"/>
            @break
        @case('lista')
            <path d="m3 6 1.5 1.5L7 4m-4 9 1.5 1.5L7 11m-4 9 1.5 1.5L7 18M11 6h10m-10 7h10m-10 7h10"/>
            @break
        @case('correcto')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('buscar')
            <circle cx="10" cy="10" r="6.5"/><path d="m15 15 6 6"/>
            @break
        @case('atras')
            <path d="M20 12H4m6-6-6 6 6 6"/>
            @break
        @case('ojo')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>
            @break
        @case('usuario')
            <circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
            @break
        @case('camara')
            <path d="M8 5l2-2h4l2 2h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4Z"/><circle cx="12" cy="13" r="4"/>
            @break
        @case('chevron')
            <path d="m7 10 5 5 5-5"/>
            @break
        @case('salir')
            <path d="M9 4H4v16h5m4-13 5 5-5 5M8 12h13"/>
            @break
        @case('montana')
            <path d="m2 20 7-13 5 8 3-5 5 10H2Z"/><path d="m6.8 11 2.2 2 2.2-2M15.5 12.5 17 14l1.5-1.5"/><circle cx="17.5" cy="4.5" r="2"/>
            @break
        @case('hoja')
            <path d="M20 3C9 2 3 7 5 14c1.3 4.5 7 6 11 2 3-3 4-8 4-13Z"/><path d="M3 21 15 9M7 17l-1-6m5 2 5 1"/>
            @break
        @case('personas')
            <circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v3"/>
            @break
        @case('brujula')
            <circle cx="12" cy="12" r="9"/><path d="m16 8-2.5 5.5L8 16l2.5-5.5L16 8Z"/>
            @break
        @case('ruta')
            <circle cx="5" cy="5" r="2"/><circle cx="19" cy="19" r="2"/><path d="M7 5h9a4 4 0 0 1 0 8H8a3 3 0 0 0 0 6h9"/>
            @break
        @case('calendario')
            <rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 4h2m4 0h2m-8 3h2"/>
            @break
        @case('paisaje')
            <path d="m2 19 6-9 4 5 3-4 7 8H2Zm3 3h14M8 10l2-3 5 4"/><circle cx="17" cy="5" r="2"/><path d="M2 7h3m-1-3h3M18 22h3"/>
            @break
        @case('mas')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16"/>
            @break
        @case('sol')
            <circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>
            @break
        @case('informacion')
            <circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/>
            @break
        @case('flecha-diagonal')
            <path d="M6 18 18 6M6 6h12v12"/>
            @break
        @case('abajo')
            <path d="M12 4v16m-6-6 6 6 6-6"/>
            @break
        @case('arriba')
            <path d="M12 20V4m-6 6 6-6 6 6"/>
            @break
        @default
            <path d="M4 12h16m-6-6 6 6-6 6"/>
    @endswitch
</svg>
