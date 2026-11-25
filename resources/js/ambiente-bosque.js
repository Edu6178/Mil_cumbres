import { crearAmbienteNaturaleza } from './ambiente-naturaleza.js';

const raiz = document.documentElement;
const controles = [...document.querySelectorAll('[data-sonidos-bosque]')];
const cursor = document.querySelector('[data-cursor-bosque]');
const estado = document.querySelector('[data-estado-ambiente]');
const leer = clave => { try { return localStorage.getItem(clave); } catch { return null; } };
const guardar = (clave, valor) => { try { localStorage.setItem(clave, valor); } catch { /* Preferencia válida durante esta visita. */ } };
const editandoFotos = !!document.querySelector('[data-editor-fotos-vista]');
const efectos = new Set();
let habilitado = leer('milcumbres-sonidos') === 'si';
let contexto, salida, ambiente, secuencia = 0, ultimoEfecto = -Infinity;
const permitido = () => habilitado && !editandoFotos && !document.hidden && controles.length > 0;
const listo = () => permitido() && contexto?.state === 'running';

function actualizar() {
    controles.forEach(boton => {
        boton.hidden = false;
        boton.textContent = `Sonidos: ${habilitado ? 'activados' : 'apagados'}`;
        boton.setAttribute('aria-pressed', String(habilitado));
        boton.setAttribute('aria-label', `${habilitado ? 'Apagar' : 'Activar'} todo el audio: botones y ambiente de naturaleza`);
        boton.title = habilitado ? 'Apaga todo el audio: botones y ambiente de naturaleza.' : 'Activa los botones y un ambiente de naturaleza a volumen muy bajo.';
    });
    const hoja = leer('milcumbres-cursor') !== 'sistema';
    raiz.classList.toggle('cursor-bosque', !!cursor && hoja);
    cursor?.setAttribute('aria-pressed', String(hoja));
    if (cursor) cursor.textContent = hoja ? 'Cursor de hoja' : 'Cursor del sistema';
}

function preparar() {
    if (!permitido()) return Promise.resolve(false);
    const Audio = window.AudioContext || window.webkitAudioContext;
    if (!Audio) return Promise.resolve(false);
    const turno = secuencia;
    try {
        if (!contexto) {
            contexto = new Audio({ latencyHint: 'interactive' });
            salida = contexto.createGain(); salida.connect(contexto.destination);
            ambiente = crearAmbienteNaturaleza(contexto, salida);
        }
        const arrancar = () => {
            if (turno !== secuencia || !listo()) return false;
            salida.gain.setValueAtTime(.85, contexto.currentTime);
            ambiente.iniciar();
            return true;
        };
        return contexto.state === 'running' ? Promise.resolve(arrancar()) : contexto.resume().then(arrancar).catch(() => false);
    } catch { return Promise.resolve(false); }
}

function retirar(entrada) {
    entrada.fuente.onended = null;
    try { entrada.fuente.stop(); } catch { /* El efecto puede haber terminado. */ }
    entrada.fuente.disconnect(); entrada.nodos.forEach(nodo => nodo.disconnect());
    efectos.delete(entrada);
}

function registrar(fuente, nodos) {
    // Se conserva la respuesta inmediata incluso al recorrer muchos botones seguidos.
    if (efectos.size >= 8) retirar(efectos.values().next().value);
    const entrada = { fuente, nodos }; efectos.add(entrada);
    fuente.onended = () => { fuente.disconnect(); nodos.forEach(nodo => nodo.disconnect()); efectos.delete(entrada); };
}

function silenciar() {
    secuencia++;
    if (salida) salida.gain.setValueAtTime(0, contexto.currentTime);
    ambiente?.detener();
    [...efectos].forEach(retirar);
    contexto?.suspend().catch(() => {});
}

function hojas(tipo = 'hojas') {
    if (!listo() || performance.now() - ultimoEfecto < 1200) return;
    ultimoEfecto = performance.now();
    const inicio = contexto.currentTime, pasos = tipo === 'pasos';
    const duracion = pasos ? .65 : .48;
    const buffer = contexto.createBuffer(1, Math.ceil(contexto.sampleRate * duracion), contexto.sampleRate);
    const datos = buffer.getChannelData(0);
    for (let i = 0; i < datos.length; i++) datos[i] = Math.random() * 2 - 1;
    const fuente = contexto.createBufferSource(), filtro = contexto.createBiquadFilter(), volumen = contexto.createGain();
    fuente.buffer = buffer; filtro.type = 'bandpass'; filtro.Q.value = .65;
    filtro.frequency.setValueAtTime(pasos ? 650 : 1100, inicio);
    filtro.frequency.linearRampToValueAtTime(pasos ? 360 : 1900, inicio + duracion * .6);
    volumen.gain.setValueAtTime(0, inicio);
    volumen.gain.linearRampToValueAtTime(pasos ? .23 : .17, inicio + .025);
    if (pasos) {
        volumen.gain.exponentialRampToValueAtTime(.002, inicio + .23);
        volumen.gain.linearRampToValueAtTime(.16, inicio + .32);
    }
    volumen.gain.exponentialRampToValueAtTime(.0001, inicio + duracion);
    fuente.connect(filtro).connect(volumen).connect(salida); registrar(fuente, [filtro, volumen]);
    ambiente.atenuar(); fuente.start(inicio); fuente.stop(inicio + duracion);
}

function tocar(anticipo = false) {
    // Sin promesas, esperas ni pausa entre botones: se programa en este mismo evento.
    if (!listo()) return;
    const inicio = contexto.currentTime, duracion = anticipo ? .048 : .085;
    const tono = contexto.createOscillator(), volumen = contexto.createGain();
    tono.type = 'sine'; tono.frequency.setValueAtTime(anticipo ? 760 : 620, inicio);
    tono.frequency.exponentialRampToValueAtTime(anticipo ? 900 : 270, inicio + duracion);
    volumen.gain.setValueAtTime(0, inicio);
    volumen.gain.linearRampToValueAtTime(anticipo ? .07 : .115, inicio + .002);
    volumen.gain.setValueAtTime(anticipo ? .07 : .115, inicio + .009);
    volumen.gain.exponentialRampToValueAtTime(.0001, inicio + duracion);
    tono.connect(volumen).connect(salida); registrar(tono, [volumen]); ambiente.atenuar();
    tono.start(inicio); tono.stop(inicio + duracion);
}

function trasDesbloquear(accion) {
    const inicio = performance.now(), turno = secuencia;
    // Solo el primer gesto puede necesitar permiso del navegador. Se descartan efectos tardíos.
    preparar().then(ok => { if (ok && turno === secuencia && performance.now() - inicio < 100) accion(); });
}

controles.forEach(boton => boton.addEventListener('click', () => {
    if (editandoFotos) return;
    habilitado = !habilitado; secuencia++;
    guardar('milcumbres-sonidos', habilitado ? 'si' : 'no'); actualizar();
    if (estado) estado.textContent = habilitado ? 'Botones al instante y un fondo original de brisa, aves y notas suaves, a volumen muy bajo. Este botón apaga todo el audio.' : 'Todo el audio está apagado.';
    if (habilitado) trasDesbloquear(() => tocar()); else silenciar();
}));
cursor?.addEventListener('click', () => {
    guardar('milcumbres-cursor', raiz.classList.contains('cursor-bosque') ? 'sistema' : 'hoja'); actualizar();
});
document.querySelector('[data-repetir-bosque]')?.addEventListener('click', () => { if (listo()) hojas('pasos'); else trasDesbloquear(() => hojas('pasos')); });

if (controles.length && !editandoFotos) {
    const interactuables = 'button, a[href], summary, [role="button"]';
    const disponible = evento => {
        const boton = evento.target.closest?.(interactuables);
        return boton && !boton.closest('[inert]') && !boton.matches(':disabled, [aria-disabled="true"], [data-sonidos-bosque], [data-repetir-bosque]') ? boton : null;
    };
    // Se prepara una vez al entrar y al recuperar un gesto; el hover nunca espera a resume().
    document.addEventListener('pointerdown', () => { if (permitido() && !listo()) preparar(); }, { capture: true, passive: true });
    document.addEventListener('keydown', () => { if (permitido() && !listo()) preparar(); }, { capture: true, passive: true });
    document.addEventListener('pointerover', e => {
        const boton = disponible(e);
        if (e.pointerType === 'mouse' && boton && !boton.contains(e.relatedTarget)) tocar(true);
    }, { passive: true });
    document.addEventListener('pointerdown', e => {
        if (e.isPrimary && e.button === 0 && disponible(e)) { if (listo()) tocar(); else trasDesbloquear(() => tocar()); }
    }, { passive: true });
    document.addEventListener('click', e => {
        if (e.detail === 0 && disponible(e)) { if (listo()) tocar(); else trasDesbloquear(() => tocar()); }
    });
    document.addEventListener('milcumbres:transicion', e => {
        if (e.detail?.destino) {
            if (habilitado) try { sessionStorage.setItem('milcumbres-transicion-sonora', JSON.stringify({ destino: e.detail.destino, instante: Date.now() })); } catch { hojas(); }
        } else hojas();
    });
    try {
        const llegada = JSON.parse(sessionStorage.getItem('milcumbres-transicion-sonora') || 'null');
        sessionStorage.removeItem('milcumbres-transicion-sonora');
        if (llegada?.destino === location.href && Date.now() - llegada.instante < 5000) trasDesbloquear(() => hojas());
    } catch { /* La navegación no depende del almacenamiento de preferencias. */ }
}
document.addEventListener('visibilitychange', () => { if (document.hidden) silenciar(); else preparar(); });
window.addEventListener('pagehide', silenciar);
window.addEventListener('pageshow', e => { if (e.persisted) preparar(); });
window.addEventListener('storage', evento => {
    if (evento.key === 'milcumbres-sonidos' || evento.key === null) {
        habilitado = leer('milcumbres-sonidos') === 'si';
        if (habilitado) preparar(); else silenciar();
    }
    actualizar();
});
actualizar();
if (permitido()) preparar();
