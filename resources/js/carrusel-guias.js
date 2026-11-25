import { animate } from 'animejs';
import { nivelMovimiento } from './movimiento.js';

export function iniciarCarruseles() {
    document.querySelectorAll('[data-carrusel-guias]').forEach(prepararCarrusel);
}

function prepararCarrusel(carrusel) {
    if (carrusel.classList.contains('carrusel-listo')) return;
    const pista = carrusel.querySelector('[data-carrusel-pista]');
    const ventana = carrusel.querySelector('[data-carrusel-ventana]');
    const fichas = [...pista.querySelectorAll('[data-carrusel-ficha]')];
    const controles = carrusel.querySelector('[data-carrusel-controles]');
    const puntos = [...carrusel.querySelectorAll('[data-carrusel-ir]')];
    const pausa = carrusel.querySelector('[data-carrusel-pausa]');
    const progreso = carrusel.querySelector('[data-carrusel-progreso]');
    const sinMovimiento = () => nivelMovimiento() === 'sin';
    let animacion, reloj, copia;
    let visible = false, encima = false, foco = false, pausado = false, moviendo = false;
    let gesto, omitirClicHasta = 0;

    const originalesVisibles = () => fichas.filter((ficha) => !ficha.hidden);
    const ordenVisible = () => [...pista.children].filter((ficha) => !ficha.hidden && ficha !== copia);
    const porVista = () => Math.max(1, parseInt(getComputedStyle(carrusel).getPropertyValue('--guias-por-vista'), 10) || 1);

    function detenerReloj() {
        reloj?.cancel();
        reloj = null;
        progreso.style.strokeDashoffset = '100';
    }

    // El avance se detiene fuera de pantalla, al leer con ratón o teclado y al ocultar la pestaña.
    function programar() {
        detenerReloj();
        if (!visible || encima || foco || pausado || moviendo || document.hidden || sinMovimiento() || originalesVisibles().length < 2) return;
        reloj = animate(progreso, {
            strokeDashoffset: [100, 0], duration: 6500, ease: 'linear',
            onComplete: () => mover(1),
        });
    }

    function sincronizar(anunciar = false) {
        const orden = ordenVisible();
        const originales = originalesVisibles();
        const actual = originales.indexOf(orden[0]);
        ventana.hidden = orden.length === 0;
        controles.hidden = orden.length < 2;
        carrusel.querySelector('[data-carrusel-actual]').textContent = String(actual + 1).padStart(2, '0');
        carrusel.querySelector('[data-carrusel-total]').textContent = String(orden.length).padStart(2, '0');
        fichas.forEach((ficha) => {
            const posicion = orden.indexOf(ficha);
            const accesible = posicion >= 0 && posicion < porVista();
            ficha.inert = !accesible;
            ficha.setAttribute('aria-hidden', String(!accesible));
            ficha.setAttribute('role', 'group');
            ficha.setAttribute('aria-roledescription', 'diapositiva');
            ficha.setAttribute('aria-label', `${originales.indexOf(ficha) + 1} de ${orden.length}: ${ficha.dataset.nombre}`);
        });
        puntos.forEach((punto) => {
            const ficha = fichas.find((ficha) => ficha.dataset.indice === punto.dataset.carruselIr);
            punto.hidden = !ficha || ficha.hidden;
            if (ficha === orden[0]) punto.setAttribute('aria-current', 'true');
            else punto.removeAttribute('aria-current');
        });
        if (anunciar && orden[0]) carrusel.querySelector('[data-carrusel-aviso]').textContent = orden[0].dataset.nombre;
        pausa.disabled = sinMovimiento();
        pausa.setAttribute('aria-pressed', String(pausado));
        pausa.setAttribute('aria-label', sinMovimiento() ? 'Rotación automática desactivada' : (pausado ? 'Reanudar carrusel' : 'Pausar carrusel'));
        pausa.querySelector('[data-icono-pausa]').toggleAttribute('hidden', pausado || sinMovimiento());
        pausa.querySelector('[data-icono-reproducir]').toggleAttribute('hidden', !pausado && !sinMovimiento());
    }

    function terminar() {
        animacion?.cancel();
        animacion = null;
        copia?.remove();
        copia = null;
        pista.style.transform = '';
        pista.style.opacity = '';
        moviendo = false;
        carrusel.removeAttribute('data-moviendo');
        sincronizar();
    }

    function mover(direccion, manual = false) {
        const orden = ordenVisible();
        if (moviendo || orden.length < 2) return;
        detenerReloj();
        moviendo = true;
        carrusel.setAttribute('data-moviendo', 'true');
        const enfocarFicha = manual && pista.contains(document.activeElement);
        const paso = orden[0].getBoundingClientRect().width + (parseFloat(getComputedStyle(pista).gap) || 0);
        if (direccion > 0) {
            // La copia solo rellena el borde durante el giro; no duplica enlaces accesibles ni retratos compartidos.
            copia = orden[0].cloneNode(true);
            copia.inert = true;
            copia.setAttribute('aria-hidden', 'true');
            copia.removeAttribute('data-carrusel-ficha');
            copia.removeAttribute('data-guia');
            copia.querySelectorAll('[data-retrato-guia]').forEach((foto) => { foto.style.viewTransitionName = 'none'; });
            pista.append(copia);
        } else {
            pista.prepend(orden.at(-1));
        }
        const completar = () => {
            if (direccion > 0) pista.append(orden[0]);
            terminar();
            sincronizar(manual);
            if (enfocarFicha) ordenVisible()[0]?.querySelector('h2 a')?.focus({ preventScroll: true });
            programar();
        };
        if (sinMovimiento()) {
            completar();
            return;
        }
        animacion = animate(pista, {
            x: direccion > 0 ? [0, -paso] : [-paso, 0],
            duration: nivelMovimiento() === 'suaves' ? 360 : 720, ease: 'inOutCubic', onComplete: completar,
        });
    }

    carrusel.querySelector('[data-carrusel-anterior]').addEventListener('click', () => mover(-1, true));
    carrusel.querySelector('[data-carrusel-siguiente]').addEventListener('click', () => mover(1, true));
    pausa.addEventListener('click', () => { pausado = !pausado; sincronizar(); programar(); });
    puntos.forEach((punto) => punto.addEventListener('click', () => {
        const destino = fichas.find((ficha) => ficha.dataset.indice === punto.dataset.carruselIr);
        if (!destino || destino.hidden || moviendo || ordenVisible()[0] === destino) return;
        detenerReloj();
        while (ordenVisible()[0] !== destino) pista.append(ordenVisible()[0]);
        sincronizar(true);
        if (!sinMovimiento()) {
            moviendo = true;
            animacion = animate(pista, { opacity: [0.45, 1], duration: 350, ease: 'outCubic', onComplete: () => { terminar(); programar(); } });
        } else programar();
    }));

    carrusel.addEventListener('pointerenter', (evento) => { if (evento.pointerType === 'mouse') { encima = true; detenerReloj(); } });
    carrusel.addEventListener('pointerleave', (evento) => { if (evento.pointerType === 'mouse') { encima = false; programar(); } });
    carrusel.addEventListener('focusin', () => { foco = true; detenerReloj(); });
    carrusel.addEventListener('focusout', () => queueMicrotask(() => { foco = carrusel.contains(document.activeElement); programar(); }));
    carrusel.addEventListener('keydown', (evento) => {
        if (evento.key !== 'ArrowLeft' && evento.key !== 'ArrowRight') return;
        evento.preventDefault();
        mover(evento.key === 'ArrowRight' ? 1 : -1, true);
    });

    // En teléfonos se puede deslizar sin interferir con el desplazamiento vertical de la página.
    ventana.addEventListener('pointerdown', (evento) => {
        if (evento.pointerType === 'mouse') return;
        gesto = { x: evento.clientX, y: evento.clientY };
        detenerReloj();
    });
    ventana.addEventListener('pointerup', (evento) => {
        if (!gesto) return;
        const x = evento.clientX - gesto.x, y = evento.clientY - gesto.y;
        gesto = null;
        if (Math.abs(x) > 45 && Math.abs(x) > Math.abs(y) * 1.3) {
            omitirClicHasta = performance.now() + 400;
            mover(x < 0 ? 1 : -1, true);
        } else programar();
    });
    ventana.addEventListener('pointercancel', () => { gesto = null; programar(); });
    ventana.addEventListener('click', (evento) => { if (performance.now() < omitirClicHasta) evento.preventDefault(); }, true);
    carrusel.addEventListener('guias:filtrados', () => {
        detenerReloj(); terminar();
        pista.replaceChildren(...fichas);
        sincronizar(); programar();
    });
    window.addEventListener('milcumbres:movimiento', () => { detenerReloj(); terminar(); programar(); });
    document.addEventListener('visibilitychange', programar);
    window.addEventListener('pagehide', () => { detenerReloj(); terminar(); });
    window.addEventListener('pageshow', programar);
    const observador = new IntersectionObserver(([entrada]) => { visible = entrada.isIntersecting; programar(); }, { threshold: 0.25 });
    observador.observe(carrusel);
    const tamano = new ResizeObserver(() => { detenerReloj(); terminar(); programar(); });
    tamano.observe(ventana);
    carrusel.classList.add('carrusel-listo');
    sincronizar();
}
