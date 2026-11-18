import { animate, createScope, stagger } from 'animejs';

// Cada efecto se ejecuta una sola vez y se revierte si cambia la preferencia de movimiento.
export function iniciarAnimaciones() {
    const preferencia = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (preferencia.matches) return;

    let observador;
    const alcance = createScope().add(() => {
        const portada = document.querySelectorAll('.portada-texto > *');
        if (portada.length) {
            // La portada nunca queda invisible mientras termina de cargar su imagen.
            animate(portada, { y: [18, 0], opacity: [0.8, 1], duration: 850, delay: stagger(90), ease: 'outCubic' });
            animate('.portada-pie', { y: [8, 0], opacity: [0.6, 1], duration: 750, delay: 200, ease: 'outCubic' });
            animate('.sello-portada', { scale: [0.92, 1], opacity: [0.6, 1], duration: 1100, ease: 'outCubic' });
        }

        const entradas = document.querySelectorAll('[data-entrada]');
        if (entradas.length) {
            animate(entradas, { y: [18, 0], opacity: [0.75, 1], duration: 800, delay: stagger(80), ease: 'outCubic' });
        }

        const fotografiaPrincipal = document.querySelector('.publico-hero-foto');
        if (fotografiaPrincipal) {
            animate(fotografiaPrincipal, { x: [24, 0], opacity: [0.6, 1], duration: 950, ease: 'outCubic' });
            animate('.publico-sello', { y: [12, 0], opacity: [0.6, 1], duration: 800, delay: 170, ease: 'outCubic' });
        }
        const camposCuenta = document.querySelectorAll('.formulario-cuenta > .campo, .formulario-cuenta > .boton');
        if (camposCuenta.length) {
            // Los campos aparecen en su orden de lectura y nunca se ocultan antes de cargar el módulo.
            animate(camposCuenta, { y: [12, 0], opacity: [0.65, 1], duration: 520, delay: stagger(65, { start: 100 }), ease: 'outCubic' });
        }

        // Solo se anima lo que aparece en pantalla; el resto conserva su contenido accesible.
        observador = new IntersectionObserver((elementos) => {
            elementos.forEach(({ target, isIntersecting }) => {
                if (!isIntersecting || preferencia.matches) return;
                const orden = [...target.parentElement.children].indexOf(target);
                alcance.execute(() => animate(target, {
                    y: [26, 0], opacity: [0.45, 1], duration: 800,
                    delay: Math.min(orden * 60, 180), ease: 'outCubic',
                    // Libera el movimiento para que después funcionen los efectos al pasar el ratón.
                    onComplete: () => { target.style.removeProperty('transform'); target.style.removeProperty('opacity'); },
                }));
                observador.unobserve(target);
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.esencia-cuadricula > div, .lineas-cuadricula > *, .encabezado-seccion, .catalogo-cuadricula > *, .comunidades-interior > div, .guias-cuadricula > div, .organizaciones-cuadricula > div, .preguntas-cuadricula > div, .cierre-interior, .pie-superior > *, .publico-guias-cuadricula > [data-guia], .publico-servicios-cuadricula > article, .publico-bloque-perfil, .publico-tres-columnas > article, .publico-manifiesto > div > *, .publico-dos-caminos > article, .publico-contacto > *, .publico-detalle-foto, .publico-texto-detalle').forEach((elemento) => observador.observe(elemento));
    });

    // Los cambios del catálogo reciben una transición breve, sin recargar la página.
    const transicionCatalogo = () => {
        if (!preferencia.matches) {
            alcance.execute(() => animate('#catalogo-contenido', { opacity: [0.65, 1], y: [5, 0], duration: 280, ease: 'outQuad' }));
        }
    };
    document.querySelectorAll('[data-catalogo]').forEach((elemento) => elemento.addEventListener('click', transicionCatalogo));

    document.querySelectorAll('.lista-preguntas details, .linea-servicio').forEach((detalle) => {
        detalle.addEventListener('toggle', () => {
            if (!detalle.open || preferencia.matches) return;
            const contenido = [...detalle.children].filter((elemento) => elemento.tagName !== 'SUMMARY');
            alcance.execute(() => animate(contenido, { y: [7, 0], opacity: [0.4, 1], duration: 340, ease: 'outCubic' }));
        });
    });

    // El paisaje se desplaza unos pocos píxeles al recorrer la portada; se limita a pantallas con ratón.
    const paisajes = [...document.querySelectorAll('.portada-fondo, .comunidades-fondo')];
    const conRaton = window.matchMedia('(pointer: fine)');
    let cuadro;
    const actualizarPaisajes = () => {
        cuadro = null;
        if (preferencia.matches || !conRaton.matches || document.hidden) return;
        paisajes.forEach((imagen) => {
            const limites = imagen.closest('section').getBoundingClientRect();
            if (limites.bottom < 0 || limites.top > window.innerHeight) return;
            const posicion = (window.innerHeight / 2 - limites.top - limites.height / 2) / window.innerHeight;
            const amplitud = Math.min(14, limites.height * 0.02);
            imagen.style.setProperty('--desfase-paisaje', `${Math.max(-amplitud, Math.min(amplitud, posicion * 22))}px`);
        });
    };
    const solicitarCuadro = () => { if (!cuadro && paisajes.length && !preferencia.matches) cuadro = requestAnimationFrame(actualizarPaisajes); };
    window.addEventListener('scroll', solicitarCuadro, { passive: true });
    window.addEventListener('resize', solicitarCuadro, { passive: true });
    window.addEventListener('pageshow', solicitarCuadro);
    window.addEventListener('pagehide', () => { cancelAnimationFrame(cuadro); cuadro = null; });
    solicitarCuadro();

    // Las flechas acompañan al ratón y al foco del teclado, sin mover el área del enlace.
    document.querySelectorAll('.boton:not(.boton-formulario), .enlace-texto').forEach((boton) => {
        const icono = boton.querySelector('.icono');
        if (!icono) return;
        const mover = (distancia) => {
            if (!preferencia.matches) {
                alcance.execute(() => animate(icono, { x: distancia, duration: 240, ease: 'outCubic' }));
            }
        };
        boton.addEventListener('pointerenter', () => mover(4));
        boton.addEventListener('pointerleave', () => mover(0));
        boton.addEventListener('focus', () => mover(4));
        boton.addEventListener('blur', () => mover(0));
    });

    preferencia.addEventListener('change', () => {
        if (preferencia.matches) {
            observador?.disconnect();
            alcance.revert();
            cancelAnimationFrame(cuadro);
            cuadro = null;
            paisajes.forEach((imagen) => imagen.style.removeProperty('--desfase-paisaje'));
        }
    });
}
