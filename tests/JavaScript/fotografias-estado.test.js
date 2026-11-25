import test from 'node:test';
import assert from 'node:assert/strict';
import { crearBorrador, cambiosPendientes, contarCambios, elegirFotografia } from '../../resources/js/fotografias-estado.js';
function datos() {
    const foto = { titulo: 'Bosque', alternativo: 'Un bosque.', album: 'recorridos', foco_x: 50, foco_y: 50, posicion: 10, publicada: true, destacada: false };
    return { revision: 'a'.repeat(64), fotos: { bosque: { ...foto, id: 'bosque' }, nueva: { ...foto, id: 'nueva', titulo: 'Cascada', publicada: false, posicion: 1000 } },
        espacios: { inicio: { nombre: 'Portada', foto: 'bosque' } }, servicios: { 3: { nombre: 'Travesía', foto: 'bosque' } }, guias: { 2: { nombre: 'Guía', foto: 'bosque' } } };
}
test('el borrador no publica cambios y al deshacer vuelve a quedar limpio', () => {
    const base = datos(), borrador = crearBorrador(base);
    borrador.fotos.bosque.titulo = 'Otro título';
    assert.equal(base.fotos.bosque.titulo, 'Bosque');
    assert.deepEqual(cambiosPendientes(base, borrador).fotos, { bosque: { titulo: 'Otro título' } });
    borrador.fotos.bosque.titulo = 'Bosque'; assert.equal(contarCambios(cambiosPendientes(base, borrador)), 0);
});
test('cambiar la imagen de una experiencia conserva las otras ubicaciones', () => {
    const base = datos(), borrador = crearBorrador(base);
    elegirFotografia(borrador, { tipo: 'servicios', clave: '3' }, 'nueva');
    assert.equal(borrador.servicios[3], 'nueva'); assert.equal(borrador.espacios.inicio, 'bosque');
    assert.equal(borrador.fotos.nueva.publicada, true); assert.equal(borrador.fotos.bosque.publicada, true);
    assert.equal(base.fotos.nueva.publicada, false);
});
test('reemplazar en galería conserva el lugar y remapea los usos antes de ocultar la anterior', () => {
    const base = datos(), borrador = crearBorrador(base);
    elegirFotografia(borrador, { tipo: 'foto', clave: 'bosque' }, 'nueva');
    assert.equal(borrador.fotos.bosque.publicada, false); assert.equal(borrador.fotos.nueva.publicada, true);
    assert.equal(borrador.fotos.nueva.posicion, 10);
    assert.equal(borrador.espacios.inicio, 'nueva'); assert.equal(borrador.servicios[3], 'nueva'); assert.equal(borrador.guias[2], 'nueva');
    const lote = cambiosPendientes(base, borrador); assert.equal(contarCambios(lote), 5);
    assert.equal(base.servicios[3].foto, 'bosque');
});
