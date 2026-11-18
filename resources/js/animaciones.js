import { animate, createScope } from 'animejs';

// Cada efecto se ejecuta una sola vez y se revierte si cambia la preferencia de movimiento.
export function iniciarAnimaciones() {
    const preferencia = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (preferencia.matches) return;

    let observador;
    const pendientes = new Set();
    const revelaciones = new Map();
    const alcance = createScope().add(() => {
        if (!('IntersectionObserver' in window)) return;
        // Solo se preparan bloques que todavía están debajo de la pantalla al cargar el módulo.
        observador = new IntersectionObserver((elementos) => {
            elementos.forEach(({ target, isIntersecting }) => {
                if (!isIntersecting || preferencia.matches) return;
                observador.unobserve(target);
                const orden = [...target.parentElement.children].indexOf(target);
                alcance.execute(() => {
                    const revelacion = animate(target, {
                        y: [18, 0], opacity: [0, 1], duration: 620,
                        delay: Math.min(orden * 45, 100), ease: 'outCubic',
                        // Libera el movimiento para que después funcionen los efectos al pasar el ratón.
                        onComplete: () => {
                            target.classList.remove('revelar-al-recorrer');
                            pendientes.delete(target);
                            revelaciones.delete(target);
                            target.style.removeProperty('transform');
                            target.style.removeProperty('opacity');
                        },
                    });
                    revelaciones.set(target, revelacion);
                });
            });
        }, { threshold: 0, rootMargin: '0px 0px 48px 0px' });

        document.querySelectorAll('.esencia-cuadricula > div, .lineas-cuadricula > *, .encabezado-seccion, .catalogo-cuadricula > *, .comunidades-interior > div, .guias-cuadricula > div, .organizaciones-cuadricula > div, .preguntas-cuadricula > div, .cierre-interior, .pie-superior > *, .publico-guias-cuadricula > [data-guia], .publico-servicios-cuadricula > article, .publico-bloque-perfil, .publico-tres-columnas > article, .publico-manifiesto > div > *, .publico-dos-caminos > article, .publico-contacto > *, .publico-detalle-foto, .publico-texto-detalle').forEach((elemento) => {
            if (elemento.hidden || elemento.getBoundingClientRect().top < window.innerHeight + 48) return;
            pendientes.add(elemento);
            elemento.classList.add('revelar-al-recorrer');
            observador.observe(elemento);
        });
    });

    // El teclado revela inmediatamente cualquier bloque que reciba el foco.
    document.addEventListener('focusin', (evento) => {
        for (const elemento of pendientes) {
            if (!elemento.contains(evento.target)) continue;
            observador?.unobserve(elemento);
            revelaciones.get(elemento)?.revert();
            revelaciones.delete(elemento);
            elemento.classList.remove('revelar-al-recorrer');
            pendientes.delete(elemento);
        }
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
            pendientes.forEach((elemento) => elemento.classList.remove('revelar-al-recorrer'));
            pendientes.clear();
            revelaciones.clear();
            cancelAnimationFrame(cuadro);
            cuadro = null;
            paisajes.forEach((imagen) => imagen.style.removeProperty('--desfase-paisaje'));
        }
    });
}
