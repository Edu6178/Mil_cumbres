// El borrador vive separado de la versión publicada hasta aplicar el lote completo.
export const camposFoto = ['titulo', 'alternativo', 'album', 'foco_x', 'foco_y', 'posicion', 'publicada', 'destacada'];
export const gruposFotos = ['espacios', 'servicios', 'guias'];
export function crearBorrador(base) {
    return { fotos: structuredClone(base.fotos), ...Object.fromEntries(gruposFotos.map(tipo => [tipo,
        Object.fromEntries(Object.entries(base[tipo]).map(([id, dato]) => [id, dato.foto ?? null]))])) };
}
export function cambiosPendientes(base, borrador) {
    const original = crearBorrador(base);
    const cambios = { revision: base.revision, fotos: {}, espacios: {}, servicios: {}, guias: {} };
    for (const [id, foto] of Object.entries(borrador.fotos)) {
        const distintos = Object.fromEntries(camposFoto.filter(campo => foto[campo] !== original.fotos[id]?.[campo]).map(campo => [campo, foto[campo]]));
        if (Object.keys(distintos).length) cambios.fotos[id] = distintos;
    }
    for (const tipo of gruposFotos) for (const [id, foto] of Object.entries(borrador[tipo])) {
        if (foto !== original[tipo][id]) cambios[tipo][id] = foto;
    }
    return cambios;
}
export function contarCambios(cambios) {
    return ['fotos', ...gruposFotos].reduce((total, tipo) => total + Object.keys(cambios[tipo]).length, 0);
}
export function elegirFotografia(borrador, destino, id) {
    const foto = borrador.fotos[id];
    if (!foto) return;
    foto.publicada = true;
    if (destino.tipo !== 'foto') { borrador[destino.tipo][destino.clave] = id; return; }
    if (destino.clave === id) return;
    const anterior = borrador.fotos[destino.clave];
    foto.posicion = anterior.posicion;
    foto.destacada = anterior.destacada || foto.destacada;
    anterior.publicada = false;
    anterior.destacada = false;
    // Reemplazar una foto de galería conserva sus usos; no deja portadas apuntando a un borrador.
    for (const tipo of gruposFotos) for (const clave of Object.keys(borrador[tipo])) {
        if (borrador[tipo][clave] === destino.clave) borrador[tipo][clave] = id;
    }
}
