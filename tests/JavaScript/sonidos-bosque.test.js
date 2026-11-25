import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const codigo = readFileSync(new URL('../../resources/js/ambiente-bosque.js', import.meta.url), 'utf8').replace(/^import .*;\r?\n/, '');

function escenario() {
    const eventos = new Map(), eventosVentana = new Map(), preferencias = new Map([['milcumbres-sonidos', 'si']]);
    const control = { textContent: '', atributos: {}, eventos: new Map(), setAttribute(k, v) { this.atributos[k] = v; }, addEventListener(k, f) { this.eventos.set(k, f); } };
    const fondo = { inicios: 0, paradas: 0, iniciar() { this.inicios++; }, detener() { this.paradas++; }, atenuar() {} };
    let ahora = 0, audio;
    const parametro = () => ({ value: 0, setValueAtTime() {}, linearRampToValueAtTime() {}, exponentialRampToValueAtTime() {} });
    const nodo = () => ({ gain: parametro(), connect(destino) { return destino; }, disconnect() {} });
    class Audio {
        constructor() { audio = this; this.currentTime = 10; this.state = 'running'; this.destination = {}; this.tonos = []; }
        createGain() { return nodo(); }
        createOscillator() {
            const tono = { ...nodo(), frequency: parametro(), inicios: [], paradas: [], start(t) { this.inicios.push(t); }, stop(t) { this.paradas.push(t ?? audio.currentTime); } };
            this.tonos.push(tono); return tono;
        }
        suspend() { this.state = 'suspended'; return Promise.resolve(); }
        resume() { this.state = 'running'; return Promise.resolve(); }
    }
    const escuchar = (registro, nombre, funcion) => { if (!registro.has(nombre)) registro.set(nombre, []); registro.get(nombre).push(funcion); };
    const documento = {
        hidden: false,
        documentElement: { classList: { toggle() {} } },
        querySelectorAll: () => [control], querySelector: () => null,
        addEventListener: (k, f) => escuchar(eventos, k, f),
    };
    const emitir = (nombre, evento = {}) => eventos.get(nombre)?.forEach(f => f(evento));
    vm.runInNewContext(codigo, {
        document: documento, window: { AudioContext: Audio, addEventListener: (k, f) => escuchar(eventosVentana, k, f) },
        localStorage: { getItem: k => preferencias.get(k), setItem: (k, v) => preferencias.set(k, v) },
        sessionStorage: { getItem: () => null, removeItem() {} },
        performance: { now: () => ahora }, crearAmbienteNaturaleza: () => fondo,
    });
    const boton = () => ({ closest: () => null, matches: () => false, contains: () => false });
    const apuntar = objetivo => emitir('pointerover', { pointerType: 'mouse', relatedTarget: null, target: { closest: () => objetivo } });
    return { audio, control, fondo, documento, emitir, boton, apuntar, avanzar() { ahora += 30; audio.currentTime += .03; } };
}

test('cada entrada rápida a un botón inicia su tono en el mismo evento, sin la antigua pausa de 650 ms', () => {
    const e = escenario();
    for (let i = 0; i < 6; i++) {
        e.apuntar(e.boton());
        assert.equal(e.audio.tonos.length, i + 1);
        assert.equal(e.audio.tonos[i].inicios[0], e.audio.currentTime);
        assert(e.audio.tonos[i].paradas[0] - e.audio.currentTime < .06);
        e.avanzar();
    }
    const b = e.boton();
    e.emitir('pointerdown', { isPrimary: true, button: 0, target: { closest: () => b } });
    assert.equal(e.audio.tonos.length, 7);
    assert.equal(e.audio.tonos[6].inicios[0], e.audio.currentTime);
});

test('el interruptor del pie silencia el fondo y los efectos, sin dejar sonidos pendientes', () => {
    const e = escenario(); e.apuntar(e.boton());
    e.control.eventos.get('click')();
    assert.equal(e.control.atributos['aria-pressed'], 'false');
    assert.equal(e.fondo.paradas, 1);
    assert.equal(e.audio.state, 'suspended');
    assert.equal(e.audio.tonos[0].paradas.at(-1), e.audio.currentTime);
    e.apuntar(e.boton()); assert.equal(e.audio.tonos.length, 1);
});

test('volver de una pestaña oculta recupera el ambiente sin repetir el botón que estaba sonando', async () => {
    const e = escenario(); e.apuntar(e.boton());
    e.documento.hidden = true; e.emitir('visibilitychange');
    assert.equal(e.fondo.paradas, 1); assert.equal(e.audio.state, 'suspended');
    e.documento.hidden = false; e.emitir('visibilitychange');
    await Promise.resolve();
    assert.equal(e.audio.state, 'running'); assert.equal(e.audio.tonos.length, 1);
    e.apuntar(e.boton()); assert.equal(e.audio.tonos.length, 2);
});
