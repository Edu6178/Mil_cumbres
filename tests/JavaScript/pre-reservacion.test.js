import test from 'node:test';
import assert from 'node:assert/strict';
import { iniciarPreReservacion, resumirTarifa } from '../../resources/js/pre-reservacion.js';

test('muestra precio por persona, por grupo y desde sin convertir cotizaciones en cero', () => {
    const base = { precioCentavos: '78000', unidadCobro: 'persona', tipoPrecio: 'fijo', moneda: 'MXN' };
    assert.equal(resumirTarifa(base, 3).total, '$2,340.00 MXN');
    assert.equal(resumirTarifa({ ...base, unidadCobro: 'grupo' }, 3).total, '$780.00 MXN');
    assert.equal(resumirTarifa({ ...base, tipoPrecio: 'desde' }, 3).total, 'Desde $2,340.00 MXN');
    assert.equal(resumirTarifa({ ...base, unidadCobro: '' }, 3).total, 'Cotización personalizada');
    assert.equal(resumirTarifa({ ...base, precioCentavos: '' }, 3).precio, 'Por cotizar');
    assert.equal(resumirTarifa({ ...base, precioCentavos: '29' }, 3).total, '$0.87 MXN');
});

// Superficie DOM mínima para comprobar la conservación de datos sin instalar dependencias.
class Elemento {
    constructor(etiqueta = 'div') {
        this.etiqueta = etiqueta;
        this.dataset = {};
        this.children = [];
        this.parentElement = null;
        this.eventos = new Map();
        this.value = '';
    }
    appendChild(elemento) {
        elemento.remove();
        elemento.parentElement = this;
        this.children.push(elemento);
    }
    remove() {
        if (this.parentElement) {
            this.parentElement.children = this.parentElement.children.filter((elemento) => elemento !== this);
            this.parentElement = null;
        }
    }
    querySelectorAll() { return this.children.filter((elemento) => elemento.dataset.acompanante !== undefined); }
    addEventListener(nombre, accion) { this.eventos.set(nombre, accion); }
    emitir(nombre) { this.eventos.get(nombre)?.(); }
    setCustomValidity(mensaje) { this.error = mensaje; }
}

// Permite consultar campos por su significado aunque cambie la distribución visual del formulario.
function descendientes(elemento) {
    return elemento.children.flatMap((hijo) => [hijo, ...descendientes(hijo)]);
}

function formulario() {
    const cantidad = new Elemento('input');
    cantidad.value = '2';
    const programacion = new Elemento('select');
    programacion.selectedOptions = [{ dataset: { cupos: '20' } }];
    const contenedor = new Elemento();
    contenedor.dataset.fechaMaxima = '2026-11-16';
    const grupoAnterior = new Elemento('fieldset');
    grupoAnterior.dataset.acompanante = '0';
    const nombreAnterior = new Elemento('input');
    nombreAnterior.name = 'acompanantes[0][nombre]';
    nombreAnterior.value = 'Ana';
    grupoAnterior.appendChild(nombreAnterior);
    contenedor.appendChild(grupoAnterior);
    const elementos = { numero_personas: cantidad, programacion_id: programacion, 'contenedor-acompanantes': contenedor };
    const raiz = { getElementById: (id) => elementos[id], createElement: (etiqueta) => new Elemento(etiqueta) };
    iniciarPreReservacion(raiz);
    return { cantidad, programacion, contenedor, grupoAnterior, nombreAnterior };
}

test('conserva acompañantes al cambiar fecha, aumentar, reducir y volver a aumentar el grupo', () => {
    const { cantidad, programacion, contenedor, grupoAnterior, nombreAnterior } = formulario();
    assert.equal(contenedor.children[0], grupoAnterior);
    cantidad.value = '3';
    cantidad.emitir('input');
    const segundo = contenedor.children[1];
    const nombreSegundo = descendientes(segundo).find((elemento) => elemento.name === 'acompanantes[1][nombre]');
    nombreSegundo.value = 'Luis';
    programacion.emitir('change');
    assert.equal(nombreAnterior.value, 'Ana');
    assert.equal(nombreSegundo.value, 'Luis');
    cantidad.value = '1';
    cantidad.emitir('input');
    assert.equal(contenedor.children.length, 0);
    cantidad.value = '3';
    cantidad.emitir('input');
    assert.equal(contenedor.children[0], grupoAnterior);
    assert.equal(contenedor.children[1], segundo);
    assert.equal(nombreAnterior.value, 'Ana');
    assert.equal(nombreSegundo.value, 'Luis');
});

test('limita los campos al cupo seleccionado y limpia el aviso al corregir la cantidad', () => {
    const { cantidad, programacion, contenedor } = formulario();
    programacion.selectedOptions[0].dataset.cupos = '3';
    cantidad.value = '999';
    cantidad.emitir('input');
    assert.equal(contenedor.children.length, 2);
    assert.equal(cantidad.max, '3');
    assert.notEqual(cantidad.error, '');
    cantidad.value = '2';
    cantidad.emitir('input');
    assert.equal(cantidad.error, '');
    assert.equal(contenedor.children.length, 1);
});

test('los campos nuevos piden nombre completo y edad sin exigir nacimiento ni correo', () => {
    const { cantidad, contenedor } = formulario();
    cantidad.value = '3';
    cantidad.emitir('input');
    const grupo = contenedor.children[1];
    const entradas = descendientes(grupo).filter((elemento) => elemento.etiqueta === 'input');
    assert.equal(entradas.length, 2);
    for (const entrada of entradas) {
        assert.ok(descendientes(grupo).some((etiqueta) => etiqueta.htmlFor === entrada.id));
    }
    const edad = entradas.find((entrada) => entrada.type === 'number');
    assert.equal(edad.min, '0');
    assert.equal(edad.max, '120');
    assert.equal(edad.required, true);
});
