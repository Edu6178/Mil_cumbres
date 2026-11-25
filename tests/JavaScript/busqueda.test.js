import test from 'node:test';
import assert from 'node:assert/strict';
import { coincideBusqueda, sugerenciasLocales } from '../../resources/js/texto-busqueda.js';

test('Encuentra palabras parciales en cualquier orden, sin distinguir mayúsculas y acentos', () => {
    assert.equal(coincideBusqueda('Consultoría turística en Valle de Bravo', 'TURIS consul'), true);
    assert.equal(coincideBusqueda('MARÍA Núñez · Observación de aves', 'nune maria aves'), true);
    assert.equal(coincideBusqueda('María Núñez', 'mar otro'), false);
    assert.equal(coincideBusqueda('Consultoría', '%'), false);
});

test('Las sugerencias priorizan el nombre y solo contienen los registros públicos proporcionados', () => {
    const fichas = [
        { titulo: 'Ecovoluntariado', busqueda: 'Ecovoluntariado con acompañamiento' },
        { titulo: 'Consultoría turística', busqueda: 'Consultoría turística' },
        { titulo: 'Conservación', busqueda: 'Conservación' },
    ];
    assert.deepEqual(sugerenciasLocales(fichas, 'con').map((i) => i.titulo), ['Consultoría turística', 'Conservación', 'Ecovoluntariado']);
    assert.equal(sugerenciasLocales(fichas, 'con', 1).length, 1);
    assert.deepEqual(sugerenciasLocales(fichas, ''), []);
    assert.deepEqual(sugerenciasLocales(fichas, 'administrador'), []);
});
