@props(['bosque' => false])
{{-- Se prepara antes del primer dibujo para evitar que el contenido aparezca y vuelva a ocultarse. --}}
<style>
    @view-transition { navigation: auto; }
    @media (prefers-reduced-motion: reduce) { @view-transition { navigation: none; } }
    .bienvenida-bosque { display: none; }
    html.bosque-pendiente .bienvenida-bosque { display: block; position: fixed; inset: 0; z-index: 10000; background: #102d24; }
</style>
<style id="regla-movimiento"></style>
<script>
    (function () {
        var nivel = 'completas';
        try {
            var guardada = localStorage.getItem('milcumbres-movimiento');
            if (['completas', 'suaves', 'sin'].includes(guardada)) nivel = guardada;
        } catch (error) {}
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) nivel = 'sin';
        if (new URL(location.href).searchParams.get('vista_fotografias') === '1') nivel = 'sin';
        document.documentElement.dataset.movimiento = nivel;
        @if($bosque)
        try {
            if (nivel !== 'sin' && !location.hash && !new URL(location.href).searchParams.has('vista_fotografias') && !new URL(location.href).searchParams.has('programacion') && !localStorage.getItem('milcumbres-bosque-v1')) {
                document.documentElement.classList.add('bosque-pendiente');
                // Un fallo de JavaScript o de red nunca deja la página bloqueada.
                window.milcumbresBosqueRescate = window.setTimeout(function () { document.documentElement.classList.remove('bosque-pendiente'); }, 6500);
            }
        } catch (error) {}
        @endif
        if (nivel === 'sin') document.getElementById('regla-movimiento').textContent = '@view-transition { navigation: none; }';
    })();
    document.documentElement.classList.add('con-entrada');
    window.addEventListener('pagereveal', function (evento) {
        if (!evento.viewTransition) return;
        document.documentElement.classList.add('con-transicion-nativa');
        // El navegador puede omitir un efecto al navegar rápido; no se reinicia la página por ello.
        evento.viewTransition.ready.catch(function () {});
        evento.viewTransition.finished.catch(function () {});
    });
    window.addEventListener('pageshow', function (evento) {
        if (evento.persisted) document.documentElement.classList.add('entrada-restaurada');
    });
</script>
