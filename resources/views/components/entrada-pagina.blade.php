{{-- Se prepara antes del primer dibujo para evitar que el contenido aparezca y vuelva a ocultarse. --}}
<style>
    @view-transition { navigation: auto; }
    @media (prefers-reduced-motion: reduce) { @view-transition { navigation: none; } }
</style>
<script>
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
