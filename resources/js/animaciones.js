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

        // Solo se anima lo que aparece en pantalla; el resto conserva su contenido accesible.
        observador = new IntersectionObserver((elementos) => {
            elementos.forEach(({ target, isIntersecting }) => {
                if (!isIntersecting || preferencia.matches) return;
                alcance.execute(() => animate(target, { y: [24, 0], opacity: [0.35, 1], duration: 750, ease: 'outCubic' }));
                observador.unobserve(target);
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.esencia-cuadricula > div, .lineas-cuadricula > *, .encabezado-seccion, .catalogo-cuadricula > *, .comunidades-interior > div, .guias-cuadricula > div, .organizaciones-cuadricula > div, .preguntas-cuadricula > div, .cierre-interior, .pie-superior > *').forEach((elemento) => observador.observe(elemento));
    });

    // Los cambios del catálogo reciben una transición breve, sin recargar la página.
    const transicionCatalogo = () => {
        if (!preferencia.matches) {
            alcance.execute(() => animate('#catalogo-contenido', { opacity: [0.65, 1], y: [5, 0], duration: 280, ease: 'outQuad' }));
        }
    };
    document.querySelectorAll('[data-catalogo]').forEach((elemento) => elemento.addEventListener('click', transicionCatalogo));

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
        }
    });
}
