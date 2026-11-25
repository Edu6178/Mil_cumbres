import { nivelMovimiento } from './movimiento.js';

const bienvenida = document.querySelector('[data-editor-fotos-vista]') ? null : document.querySelector('[data-bienvenida-bosque]');
const raiz = document.documentElement;
let temporizador, focoAnterior, restaurar = [];
const recordar = () => { try { localStorage.setItem('milcumbres-bosque-v1', 'vista'); } catch { /* Sigue siendo posible omitir la entrada. */ } };

function terminar() {
    clearTimeout(temporizador);
    raiz.classList.remove('bosque-pendiente');
    bienvenida?.setAttribute('aria-hidden', 'true');
    restaurar.forEach(([elemento, eraInerte]) => { elemento.inert = eraInerte; });
    restaurar = [];
    if (focoAnterior?.isConnected && bienvenida?.contains(document.activeElement)) focoAnterior.focus({ preventScroll: true });
}

function abrir() {
    if (!bienvenida || nivelMovimiento() === 'sin') { terminar(); return; }
    clearTimeout(window.milcumbresBosqueRescate);
    focoAnterior = document.activeElement;
    recordar();
    raiz.classList.remove('bosque-pendiente');
    // Reiniciar solo esta escena permite repetirla sin recargar la página ni borrar el formulario.
    void bienvenida.offsetWidth;
    raiz.classList.add('bosque-pendiente');
    bienvenida.setAttribute('aria-hidden', 'false');
    window.dispatchEvent(new Event('milcumbres:bosque-abierto'));
    restaurar = [...document.body.children].filter(e => e !== bienvenida && !['SCRIPT', 'STYLE'].includes(e.tagName)).map(e => [e, e.inert]);
    restaurar.forEach(([e]) => { e.inert = true; });
    bienvenida.querySelector('[data-omitir-bienvenida]').focus({ preventScroll: true });
    clearTimeout(temporizador);
    temporizador = setTimeout(terminar, nivelMovimiento() === 'suaves' ? 1250 : 2650);
}

document.querySelectorAll('[data-repetir-bosque]').forEach(boton => {
    if (!bienvenida) { boton.hidden = true; return; }
    const actualizar = () => {
        boton.disabled = nivelMovimiento() === 'sin';
        boton.title = boton.disabled ? 'La bienvenida respeta el movimiento reducido de tu dispositivo y la opción Desactivadas.' : 'Reproducir de nuevo la bienvenida';
    };
    actualizar();
    boton.addEventListener('click', abrir);
    window.addEventListener('milcumbres:movimiento', actualizar);
});
window.addEventListener('milcumbres:omitir-bosque', () => { recordar(); terminar(); });
window.addEventListener('milcumbres:movimiento', () => { if (nivelMovimiento() === 'sin') terminar(); });
window.addEventListener('pagehide', terminar);
window.addEventListener('pageshow', e => { if (e.persisted) terminar(); });
document.addEventListener('keydown', e => {
    if (!raiz.classList.contains('bosque-pendiente')) return;
    if (e.key === 'Escape') { e.preventDefault(); terminar(); }
    if (e.key === 'Tab') { e.preventDefault(); bienvenida?.querySelector('[data-omitir-bienvenida]').focus(); }
});
if (raiz.classList.contains('bosque-pendiente')) abrir();
