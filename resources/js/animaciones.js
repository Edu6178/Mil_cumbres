import { animate, createScope, stagger } from 'animejs';
import { nivelMovimiento } from './movimiento.js';

export function iniciarAnimaciones() {
    let limpiar = () => {};
    const preparar = () => {
        limpiar();
        if (nivelMovimiento() !== 'sin') limpiar = prepararRecorrido();
        else limpiar = () => {};
    };
    preparar();
    window.addEventListener('milcumbres:movimiento', preparar);
}

function prepararRecorrido() {
    const suave = nivelMovimiento() === 'suaves';
    const eventos = new AbortController();
    const pendientes = new Set();
    const revelaciones = new Map();
    const alcance = createScope();
    const animar = (elementos, opciones) => alcance.execute(() => animate(elementos, opciones));
    let observador, cuadro;
    const liberar = (elemento) => {
        elemento.classList.remove('revelar-al-recorrer');
        pendientes.delete(elemento);
        revelaciones.delete(elemento);
        elemento.style.removeProperty('transform');
        elemento.style.removeProperty('opacity');
        elemento.style.removeProperty('filter');
    };

    if ('IntersectionObserver' in window) {
        observador = new IntersectionObserver((entradas) => {
            entradas.forEach(({ target, isIntersecting }) => {
                if (!isIntersecting) return;
                observador.unobserve(target);
                const orden = [...target.parentElement.children].indexOf(target);
                revelaciones.set(target, animar(target, {
                    y: [suave ? 8 : 36, 0], opacity: [0, 1], scale: [suave ? 1 : 0.985, 1],
                    filter: [suave ? 'blur(0px)' : 'blur(4px)', 'blur(0px)'],
                    duration: suave ? 320 : 820, delay: Math.min(orden * 75, 225), ease: 'outQuint',
                    onComplete: () => liberar(target),
                }));
            });
        }, { threshold: 0, rootMargin: '0px 0px -12px 0px' });

        // Nunca se oculta lo que ya apareció en pantalla, incluso si JavaScript tarda en descargar.
        const candidatos = [...document.querySelectorAll('[data-revelar-bosque], .esencia-cuadricula > div, .lineas-cuadricula > *, .encabezado-seccion, .catalogo-cuadricula > *, .comunidades-interior > div, .guias-cuadricula > div, .organizaciones-cuadricula > div, .preguntas-cuadricula > div, .cierre-interior, .pie-superior > *, .publico-servicios-cuadricula > article, .publico-bloque-perfil, .publico-tres-columnas > article, .publico-manifiesto > div > *, .publico-dos-caminos > article, .publico-contacto > *, .publico-detalle-foto, .publico-texto-detalle, .publico-editorial > .contenedor, .publico-franja > .contenedor, .reserva-seccion')];
        candidatos.forEach((elemento) => {
            if (elemento.hidden || elemento.getBoundingClientRect().top < window.innerHeight + 48) return;
            // Un solo efecto por bloque evita ocultar dos veces un padre y su contenido.
            if (candidatos.some((padre) => padre !== elemento && padre.contains(elemento))) return;
            pendientes.add(elemento);
            elemento.classList.add('revelar-al-recorrer');
            observador.observe(elemento);
        });
    }

    document.addEventListener('focusin', (evento) => {
        for (const elemento of pendientes) {
            if (!elemento.contains(evento.target)) continue;
            observador?.unobserve(elemento);
            revelaciones.get(elemento)?.revert();
            liberar(elemento);
        }
    }, { signal: eventos.signal });

    // Anime.js acompaña solo los resultados visibles; el formulario y la URL se conservan.
    document.addEventListener('catalogo:filtrado', (evento) => {
        const fichas = [...evento.target.querySelectorAll('[data-servicio]:not([hidden])')];
        fichas.forEach((ficha) => {
            observador?.unobserve(ficha);
            revelaciones.get(ficha)?.revert();
            liberar(ficha);
        });
        if (fichas.length) animar(fichas, {
            y: [suave ? 0 : 18, 0], opacity: [0.35, 1], duration: suave ? 220 : 480,
            delay: stagger([0, suave ? 0 : 225]), ease: 'outCubic',
        });
    }, { signal: eventos.signal });

    document.querySelectorAll('.lista-preguntas details, .linea-servicio').forEach((detalle) => {
        detalle.addEventListener('toggle', () => {
            if (!detalle.open) return;
            const contenido = [...detalle.children].filter((elemento) => elemento.tagName !== 'SUMMARY');
            animar(contenido, { y: [suave ? 0 : 12, 0], opacity: [0.4, 1], duration: suave ? 200 : 420, ease: 'outCubic' });
        }, { signal: eventos.signal });
    });

    // El paisaje acompaña el recorrido sin cambiar el comportamiento nativo del scroll.
    const paisajes = [...document.querySelectorAll('.portada-fondo, .comunidades-fondo')];
    const fotos = [...document.querySelectorAll('.publico-hero-foto > img')];
    const conRaton = window.matchMedia('(pointer: fine)');
    const actualizarPaisajes = () => {
        cuadro = null;
        if (suave || !conRaton.matches || document.hidden) return;
        paisajes.forEach((imagen) => {
            const limites = imagen.closest('section').getBoundingClientRect();
            if (limites.bottom < 0 || limites.top > window.innerHeight) return;
            const desplazamiento = Math.max(-22, Math.min(22, -limites.top * 0.06));
            imagen.style.setProperty('--desfase-paisaje', `${desplazamiento}px`);
        });
        fotos.forEach((imagen) => {
            const limites = imagen.parentElement.getBoundingClientRect();
            if (limites.bottom < 0 || limites.top > window.innerHeight) return;
            imagen.style.setProperty('--desfase-editorial', `${Math.max(-9, Math.min(9, -limites.top * 0.025))}px`);
        });
    };
    const solicitarCuadro = () => { if (!cuadro && !suave) cuadro = requestAnimationFrame(actualizarPaisajes); };
    window.addEventListener('scroll', solicitarCuadro, { passive: true, signal: eventos.signal });
    window.addEventListener('resize', solicitarCuadro, { passive: true, signal: eventos.signal });
    window.addEventListener('pageshow', solicitarCuadro, { signal: eventos.signal });
    window.addEventListener('pagehide', () => { cancelAnimationFrame(cuadro); cuadro = null; }, { signal: eventos.signal });
    solicitarCuadro();

    // Una perspectiva pequeña acompaña al ratón, sin mover controles ni activarse en pantallas táctiles.
    const profundidad = [...document.querySelectorAll('[data-profundidad]')];
    if (!suave && conRaton.matches) profundidad.forEach(elemento => {
        elemento.addEventListener('pointermove', e => {
            const r = elemento.getBoundingClientRect();
            elemento.style.transform = `perspective(1000px) rotateX(${(0.5 - (e.clientY - r.top) / r.height) * 3}deg) rotateY(${((e.clientX - r.left) / r.width - 0.5) * 4}deg)`;
        }, { passive: true, signal: eventos.signal });
        elemento.addEventListener('pointerleave', () => elemento.style.removeProperty('transform'), { signal: eventos.signal });
    });

    document.querySelectorAll('.boton:not(.boton-formulario), .enlace-texto, .publico-lineas button').forEach((boton) => {
        const icono = boton.querySelector('.icono');
        if (!icono) return;
        const mover = (entra) => animar(icono, {
            x: entra && !suave ? 5 : 0, rotate: entra && boton.matches('.publico-lineas button') && !suave ? -12 : 0,
            duration: suave ? 120 : 320, ease: 'outCubic',
        });
        boton.addEventListener('pointerenter', () => mover(true), { signal: eventos.signal });
        boton.addEventListener('pointerleave', () => mover(false), { signal: eventos.signal });
        boton.addEventListener('focus', () => mover(true), { signal: eventos.signal });
        boton.addEventListener('blur', () => mover(false), { signal: eventos.signal });
    });

    document.querySelectorAll('.reserva-formulario input, .reserva-formulario select, .reserva-formulario textarea').forEach((campo) => {
        campo.addEventListener('change', () => {
            if (!campo.value || !campo.validity.valid) return;
            animar(campo, { borderColor: ['#809477', '#d9ddd2'], duration: 600, ease: 'outQuad' });
        }, { signal: eventos.signal });
    });

    return () => {
        eventos.abort();
        observador?.disconnect();
        alcance.revert();
        pendientes.forEach(liberar);
        cancelAnimationFrame(cuadro);
        paisajes.forEach((imagen) => imagen.style.removeProperty('--desfase-paisaje'));
        fotos.forEach((imagen) => imagen.style.removeProperty('--desfase-editorial'));
        profundidad.forEach(elemento => elemento.style.removeProperty('transform'));
    };
}
