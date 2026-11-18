// Se anima la navegación real; los formularios conservan sus tokens, validaciones y redirecciones.
const preferenciaMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)');
const transicionNativa = 'onpagereveal' in window && 'onpageswap' in window;
let salida;
let navegando = false;
let restauracion;

function restaurarPagina() {
    salida?.cancel();
    salida = null;
    navegando = false;
    window.clearTimeout(restauracion);
}

document.addEventListener('click', (evento) => {
    const enlace = evento.target.closest?.('a[href]');
    if (!enlace || evento.defaultPrevented || evento.button !== 0 || evento.ctrlKey || evento.metaKey || evento.shiftKey || evento.altKey) return;
    if (enlace.hasAttribute('download') || (enlace.target && enlace.target !== '_self')) return;
    const destino = new URL(enlace.href, window.location.href);
    if (!['http:', 'https:'].includes(destino.protocol) || destino.origin !== window.location.origin) return;
    if (destino.pathname === window.location.pathname && destino.search === window.location.search) return;
    if (transicionNativa || preferenciaMovimiento.matches) return;
    const contenido = document.querySelector('main');
    if (!contenido?.animate) return;
    evento.preventDefault();
    if (navegando) return;
    navegando = true;
    salida = contenido.animate([
        { opacity: 1, transform: 'translateY(0)' },
        { opacity: 0.45, transform: 'translateY(-8px)' },
    ], { duration: 160, easing: 'ease-out', fill: 'forwards' });
    // Esta alternativa conserva el enlace normal en navegadores sin transiciones entre documentos.
    salida.finished.catch(() => {}).then(() => window.location.assign(destino.href));
    restauracion = window.setTimeout(restaurarPagina, 5000);
});

window.addEventListener('pageshow', () => {
    // Volver con el historial restaura la página sin repetir la entrada ni borrar formularios.
    restaurarPagina();
});
preferenciaMovimiento.addEventListener('change', () => {
    if (preferenciaMovimiento.matches) restaurarPagina();
});
