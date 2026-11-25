import { nivelMovimiento } from './movimiento.js';
import { coincideBusqueda } from './texto-busqueda.js';

document.querySelectorAll('[data-galeria-filtrable]').forEach(galeria => {
    if (document.querySelector('[data-editor-fotos-vista]')) return;
    const buscar = galeria.querySelector('[data-buscar-foto]');
    const album = galeria.querySelector('[data-album-foto]');
    const fichas = [...galeria.querySelectorAll('[data-foto-galeria]')];
    function filtrar() {
        let total = 0;
        fichas.forEach(ficha => {
            ficha.hidden = !((!album.value || ficha.dataset.album === album.value) && coincideBusqueda(ficha.dataset.texto, buscar.value));
            if (!ficha.hidden) total++;
        });
        galeria.querySelector('[data-total-fotos]').textContent = total;
        galeria.querySelector('[data-fotos-vacio]').hidden = total > 0;
        const url = new URL(location.href);
        for (const [k, v] of [['buscar', buscar.value.trim()], ['album', album.value]]) { if (v) url.searchParams.set(k, v); else url.searchParams.delete(k); }
        history.replaceState(null, '', url);
    }
    buscar.addEventListener('input', filtrar);
    album.addEventListener('change', filtrar);
    buscar.form.addEventListener('submit', e => { e.preventDefault(); filtrar(); });
});

if (!document.querySelector('[data-editor-fotos-vista]') && document.querySelector('[data-abrir-foto]') && 'HTMLDialogElement' in window) {
    const visor = document.createElement('dialog');
    visor.className = 'foto-ampliada';
    visor.setAttribute('aria-labelledby', 'visor-titulo');
    // Marcado fijo; ningún título o texto del administrador se interpreta como HTML.
    visor.innerHTML = '<header><span>Mil Cumbres · Galería de momentos</span><button type="button" data-visor-cerrar aria-label="Cerrar fotografía">Cerrar <span aria-hidden="true">✕</span></button></header><div class="visor-distribucion"><div class="visor-foto"><img alt="" decoding="async"></div><section class="visor-relato" aria-labelledby="visor-titulo"><span class="visor-antetitulo" data-visor-album></span><h2 id="visor-titulo"></h2><p data-visor-descripcion></p><dl class="visor-detalles"><div><dt>Año de la fotografía</dt><dd data-visor-anio>Por confirmar</dd></div></dl><div class="visor-recorrido"><span data-visor-cuenta role="status" aria-live="polite"></span><div><button type="button" data-visor-anterior aria-label="Fotografía anterior">‹</button><button type="button" data-visor-siguiente aria-label="Siguiente fotografía">›</button></div></div><span class="visor-ayuda">Usa las flechas para seguir explorando.</span></section></div>';
    document.body.append(visor);
    let enlaces = [], actual = 0, ultimoFoco, animaciones = [];
    const cancelarAnimaciones = () => { animaciones.forEach(a => a.cancel()); animaciones = []; };
    function animar(origen, direccion = 1) {
        cancelarAnimaciones();
        if (nivelMovimiento() === 'sin') return;
        const img = visor.querySelector('img'), relato = visor.querySelector('.visor-relato');
        if (!img.animate) return;
        const suave = nivelMovimiento() === 'suaves';
        const destino = img.getBoundingClientRect();
        const desde = origen && !suave
            ? `translate(${origen.left + origen.width / 2 - destino.left - destino.width / 2}px, ${origen.top + origen.height / 2 - destino.top - destino.height / 2}px) scale(${origen.width / destino.width}, ${origen.height / destino.height})`
            : (suave ? 'none' : `translateX(${direccion * 45}px) scale(.97)`);
        animaciones.push(img.animate([{ opacity: .25, transform: desde }, { opacity: 1, transform: 'none' }], { duration: suave ? 180 : 650, easing: 'cubic-bezier(.16,.8,.2,1)' }));
        animaciones.push(relato.animate([{ opacity: 0, transform: suave ? 'none' : 'translateX(35px)' }, { opacity: 1, transform: 'none' }], { duration: suave ? 180 : 540, delay: suave ? 0 : 110, fill: 'backwards', easing: 'cubic-bezier(.16,.8,.2,1)' }));
    }
    function mostrar() {
        const enlace = enlaces[actual];
        const img = visor.querySelector('img');
        img.src = enlace.href; img.alt = enlace.dataset.descripcion;
        visor.querySelector('#visor-titulo').textContent = enlace.dataset.titulo;
        visor.querySelector('[data-visor-descripcion]').textContent = enlace.dataset.descripcion;
        visor.querySelector('[data-visor-album]').textContent = enlace.dataset.albumNombre || enlace.closest('figure')?.querySelector('figcaption > span')?.textContent || 'Un momento en la naturaleza';
        // No se deduce la fecha de la carga, del nombre del archivo ni del reloj del servidor.
        visor.querySelector('[data-visor-anio]').textContent = 'Por confirmar';
        visor.querySelector('[data-visor-cuenta]').textContent = `${String(actual + 1).padStart(2, '0')} / ${enlaces.length}`;
        visor.querySelectorAll('[data-visor-anterior], [data-visor-siguiente]').forEach(b => { b.disabled = enlaces.length < 2; });
    }
    function sonidoTransicion() { if (nivelMovimiento() !== 'sin') document.dispatchEvent(new CustomEvent('milcumbres:transicion')); }
    function avanzar(direccion) { if (enlaces.length < 2) return; actual = (actual + direccion + enlaces.length) % enlaces.length; mostrar(); animar(null, direccion); sonidoTransicion(); }
    document.addEventListener('click', e => {
        const enlace = e.target.closest('[data-abrir-foto]');
        if (!enlace || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;
        e.preventDefault(); ultimoFoco = enlace;
        enlaces = [...enlace.closest('[data-galeria]').querySelectorAll('[data-abrir-foto]')].filter(e => !e.closest('[hidden]'));
        const origen = enlace.querySelector('img').getBoundingClientRect();
        actual = enlaces.indexOf(enlace); mostrar(); visor.showModal(); document.documentElement.classList.add('visor-abierto'); animar(origen); sonidoTransicion();
        document.dispatchEvent(new CustomEvent('milcumbres:visor', { detail: { abierto: true } }));
    });
    visor.querySelector('[data-visor-cerrar]').addEventListener('click', () => visor.close());
    visor.querySelector('[data-visor-anterior]').addEventListener('click', () => avanzar(-1));
    visor.querySelector('[data-visor-siguiente]').addEventListener('click', () => avanzar(1));
    visor.addEventListener('keydown', e => { if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { e.preventDefault(); avanzar(e.key === 'ArrowRight' ? 1 : -1); } });
    visor.addEventListener('close', () => { cancelarAnimaciones(); document.documentElement.classList.remove('visor-abierto'); ultimoFoco?.focus({ preventScroll: true }); document.dispatchEvent(new CustomEvent('milcumbres:visor', { detail: { abierto: false } })); });
    window.addEventListener('milcumbres:movimiento', cancelarAnimaciones);
    window.addEventListener('pagehide', () => { if (visor.open) visor.close(); });
}

document.querySelectorAll('[data-carrusel-fotos]').forEach(carrusel => {
    const pista = carrusel.querySelector('[data-fotos-pista]');
    const fichas = [...pista.children];
    const ventana = carrusel.querySelector('[data-fotos-ventana]');
    const botonPausa = carrusel.querySelector('[data-fotos-pausa]');
    let actual = 0, reproduciendo = false, reloj, animacion, visible = false, suspendido = false, inicio;
    function pintar(animar = true) {
        const distancia = fichas[actual].offsetLeft - fichas[0].offsetLeft;
        animacion?.cancel();
        if (animar && nivelMovimiento() !== 'sin' && pista.animate) {
            const anterior = getComputedStyle(pista).transform;
            pista.style.transform = `translateX(-${distancia}px)`;
            animacion = pista.animate([{ transform: anterior === 'none' ? 'translateX(0)' : anterior, filter: 'blur(0px)' }, { filter: nivelMovimiento() === 'completas' ? 'blur(2px)' : 'blur(0px)', offset: .45 }, { transform: `translateX(-${distancia}px)`, filter: 'blur(0px)' }], { duration: nivelMovimiento() === 'suaves' ? 200 : 760, easing: 'cubic-bezier(.2,.75,.2,1)' });
        } else pista.style.transform = `translateX(-${distancia}px)`;
        fichas.forEach((ficha, indice) => { ficha.inert = indice !== actual; ficha.setAttribute('aria-hidden', String(indice !== actual)); });
        carrusel.querySelector('[data-fotos-actual]').textContent = String(actual + 1).padStart(2, '0');
        carrusel.querySelector('[data-fotos-progreso]').style.width = `${(actual + 1) / fichas.length * 100}%`;
        carrusel.querySelector('[data-fotos-anterior]').disabled = fichas.length < 2;
        carrusel.querySelector('[data-fotos-siguiente]').disabled = fichas.length < 2;
    }
    function programar() {
        clearTimeout(reloj);
        if (reproduciendo && visible && !suspendido && !document.hidden && nivelMovimiento() === 'completas') reloj = setTimeout(() => { actual = (actual + 1) % fichas.length; pintar(); programar(); }, 6500);
    }
    function avanzar(paso) { actual = (actual + paso + fichas.length) % fichas.length; pintar(); carrusel.querySelector('[data-fotos-aviso]').textContent = `Fotografía ${actual + 1} de ${fichas.length}: ${fichas[actual].querySelector('h3').textContent}`; programar(); }
    function pausar() { reproduciendo = false; botonPausa.setAttribute('aria-pressed', 'true'); botonPausa.setAttribute('aria-label', 'Reproducir carrusel'); botonPausa.querySelector('span').textContent = 'Reproducir'; programar(); }
    botonPausa.addEventListener('click', () => { if (reproduciendo) pausar(); else { reproduciendo = true; botonPausa.setAttribute('aria-pressed', 'false'); botonPausa.setAttribute('aria-label', 'Pausar carrusel'); botonPausa.querySelector('span').textContent = 'Pausar'; programar(); } });
    carrusel.querySelector('[data-fotos-anterior]').addEventListener('click', () => avanzar(-1));
    carrusel.querySelector('[data-fotos-siguiente]').addEventListener('click', () => avanzar(1));
    ventana.addEventListener('keydown', e => { if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { e.preventDefault(); avanzar(e.key === 'ArrowRight' ? 1 : -1); } });
    ventana.addEventListener('pointerdown', e => { inicio = { x: e.clientX, y: e.clientY }; });
    ventana.addEventListener('pointerup', e => { if (inicio && Math.abs(e.clientX - inicio.x) > 55 && Math.abs(e.clientY - inicio.y) < 55) avanzar(e.clientX < inicio.x ? 1 : -1); inicio = null; });
    ventana.addEventListener('pointercancel', () => { inicio = null; });
    carrusel.addEventListener('pointerenter', () => { suspendido = true; programar(); });
    carrusel.addEventListener('pointerleave', () => { suspendido = false; programar(); });
    carrusel.addEventListener('focusin', pausar);
    document.addEventListener('milcumbres:visor', e => { suspendido = e.detail.abierto; programar(); });
    document.addEventListener('visibilitychange', programar);
    window.addEventListener('resize', () => pintar(false));
    window.addEventListener('milcumbres:movimiento', () => { if (nivelMovimiento() !== 'completas') pausar(); animacion?.cancel(); pintar(false); });
    window.addEventListener('pagehide', () => { clearTimeout(reloj); animacion?.cancel(); });
    const observador = new IntersectionObserver(([entrada]) => { visible = entrada.isIntersecting; programar(); }, { threshold: .2 });
    observador.observe(carrusel); pintar(false);
});
