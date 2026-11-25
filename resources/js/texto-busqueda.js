export const normalizarBusqueda = (texto) => String(texto ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es').trim();
export const palabrasBusqueda = (texto) => normalizarBusqueda(texto).split(/\s+/).filter(Boolean);
export const coincideBusqueda = (texto, consulta) => palabrasBusqueda(consulta).every((palabra) => normalizarBusqueda(texto).includes(palabra));

export function sugerenciasLocales(elementos, consulta, limite = 8) {
    const texto = normalizarBusqueda(consulta);
    if (!texto) return [];
    return elementos.filter((item) => coincideBusqueda(item.busqueda ?? `${item.titulo} ${item.contexto} ${item.resumen}`, texto))
        .map((item, indice) => ({ item, indice, prioridad: normalizarBusqueda(item.titulo) === texto ? 2 : Number(normalizarBusqueda(item.titulo).startsWith(texto)) }))
        .sort((a, b) => b.prioridad - a.prioridad || a.indice - b.indice)
        .slice(0, limite).map(({ item }) => item);
}
