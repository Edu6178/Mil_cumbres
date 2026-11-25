// Solo se guarda una preferencia visual en este navegador; no contiene datos personales.
const clave = 'milcumbres-movimiento';
const sistema = window.matchMedia('(prefers-reduced-motion: reduce)');
const opciones = ['completas', 'suaves', 'sin'];
let eleccion = 'completas';
try {
    const guardada = window.localStorage.getItem(clave);
    if (opciones.includes(guardada)) eleccion = guardada;
} catch { /* La interfaz también funciona si el navegador impide guardar preferencias. */ }

export const nivelMovimiento = () => sistema.matches || document.querySelector('[data-editor-fotos-vista]') ? 'sin' : eleccion;

function aplicar(anunciar = false) {
    const nivel = nivelMovimiento();
    document.documentElement.dataset.movimiento = nivel;
    // La regla también existe en la cabecera para respetar la elección antes del primer dibujo.
    const regla = document.querySelector('#regla-movimiento');
    if (regla) regla.textContent = nivel === 'sin' ? '@view-transition { navigation: none; }' : '';
    document.querySelectorAll('[data-control-movimiento]').forEach((control) => {
        control.hidden = false;
        control.querySelector('[data-nivel-movimiento]').value = eleccion;
        control.querySelector('[data-estado-movimiento]').textContent = sistema.matches
            ? 'Se respeta el movimiento reducido de tu dispositivo.'
            : (anunciar ? 'Preferencia guardada en este navegador.' : '');
    });
    if (anunciar) {
        // Cambiar el ajuste no vuelve a ocultar una página que el visitante ya está leyendo.
        document.documentElement.classList.add('entrada-restaurada');
        window.dispatchEvent(new CustomEvent('milcumbres:movimiento', { detail: { nivel } }));
    }
}

document.querySelectorAll('[data-nivel-movimiento]').forEach((selector) => {
    selector.addEventListener('change', () => {
        if (!opciones.includes(selector.value)) return;
        eleccion = selector.value;
        try { window.localStorage.setItem(clave, eleccion); } catch { /* Ajuste válido durante esta visita. */ }
        aplicar(true);
    });
});
sistema.addEventListener('change', () => aplicar(true));
window.addEventListener('storage', (evento) => {
    if (evento.key !== clave && evento.key !== null) return;
    eleccion = opciones.includes(evento.newValue) ? evento.newValue : 'completas';
    aplicar(true);
});
aplicar();
