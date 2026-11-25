import { coincideBusqueda } from './texto-busqueda.js';
import { camposFoto, gruposFotos, crearBorrador, cambiosPendientes, contarCambios, elegirFotografia } from './fotografias-estado.js';

const raiz = document.querySelector('[data-fotos-administracion]');
raiz.closest('main')?.classList.add('adm-principal-fotografias');
const $ = selector => raiz.querySelector(selector);
let base = JSON.parse($('[data-fotos-datos]').textContent);
let borrador = crearBorrador(base), destino, guardando = false, comparar = false, dispositivo = 1440, vista = 'biblioteca';
const frame = $('[data-fotos-iframe]');
const formulario = $('[data-editor-formulario]');
const dialogos = Object.fromEntries(['editor', 'elegir', 'revision', 'carga', 'ubicaciones'].map(clave => [clave, $(`[data-dialogo-${clave}]`)]));
const etiquetas = { titulo: 'Título', alternativo: 'Descripción', album: 'Álbum', foco_x: 'Encuadre horizontal', foco_y: 'Encuadre vertical', posicion: 'Lugar en la galería', publicada: 'Publicación', destacada: 'Carrusel de inicio' };
const tipos = { espacios: 'Página o portada', servicios: 'Experiencia', guias: 'Guía' };
const elemento = (tipo, clase, texto) => { const e = document.createElement(tipo); if (clase) e.className = clase; if (texto !== undefined) e.textContent = texto; return e; };
const boton = (texto, accion, clase = 'adm-boton adm-boton-contorno') => { const b = elemento('button', clase, texto); b.type = 'button'; b.addEventListener('click', accion); return b; };
const imagen = foto => { const i = elemento('img'); i.src = foto.mini || foto.url; i.alt = foto.alternativo || foto.titulo; i.loading = 'lazy'; i.style.objectPosition = `${foto.foco_x}% ${foto.foco_y}%`; return i; };
const aviso = texto => { $('[data-fotos-aviso]').textContent = texto; };
const pendientes = () => cambiosPendientes(base, borrador);
const fotoDestino = () => destino?.tipo === 'foto' ? destino.clave : borrador[destino?.tipo]?.[destino?.clave];
const usos = id => gruposFotos.flatMap(tipo => Object.entries(borrador[tipo]).filter(([, foto]) => foto === id).map(([clave]) => `${tipos[tipo]}: ${base[tipo][clave]?.nombre || clave}`));

function enviarVista() {
    frame.contentWindow?.postMessage({ tipo: 'milcumbres:fotos-estado', datos: comparar ? crearBorrador(base) : borrador,
        cambios: comparar ? { fotos: {}, espacios: {}, servicios: {}, guias: {} } : pendientes(), albumes: base.albumes }, location.origin);
}
function marca(id, cambios) {
    if (!cambios.fotos[id]) return '';
    if (base.fotos[id]?.publicada && !borrador.fotos[id].publicada) return 'se-oculta';
    if (!base.fotos[id]?.publicada && borrador.fotos[id].publicada) return 'se-publica';
    return 'modificada';
}
function pintarBiblioteca() {
    const cambios = pendientes(), buscar = $('[data-fotos-buscar]').value, album = $('[data-fotos-album]').value, estado = $('[data-fotos-estado]').value;
    const lista = Object.values(borrador.fotos).sort((a, b) => a.posicion - b.posicion || a.id.localeCompare(b.id));
    const visibles = lista.filter(f => (!album || f.album === album) && coincideBusqueda(`${f.titulo} ${f.alternativo} ${base.albumes[f.album]} ${usos(f.id).join(' ')} ${(f.originales || []).join(' ')}`, buscar)
        && (!estado || (estado === 'borrador' && !f.publicada) || (estado === 'publicada' && f.publicada) || (estado === 'usada' && usos(f.id).length) || (estado === 'cambiada' && cambios.fotos[f.id])));
    const tarjetas = visibles.map(foto => {
        const modificada = marca(foto.id, cambios);
        const tarjeta = elemento('article', `adm-panel foto-editor foto-biblioteca-tarjeta ${modificada}`); tarjeta.dataset.fotoId = foto.id;
        const vistaFoto = elemento('div', 'foto-editor-vista'); vistaFoto.append(imagen(foto));
        const estadoFoto = elemento('span', 'foto-estado', modificada === 'se-oculta' ? 'Se ocultará al guardar' : modificada === 'se-publica' ? 'Se publicará al guardar' : modificada ? 'Cambio pendiente' : foto.publicada ? 'Publicada' : 'Guardada sin publicar');
        vistaFoto.append(estadoFoto);
        const cuerpo = elemento('div', 'foto-editor-titulo'); cuerpo.append(elemento('span', '', base.albumes[foto.album]), elemento('h3', '', foto.titulo));
        const ubicaciones = usos(foto.id);
        cuerpo.append(elemento('p', 'foto-donde', ubicaciones.length ? `Se usa en ${ubicaciones.length} lugar(es): ${ubicaciones.slice(0, 2).join(' · ')}${ubicaciones.length > 2 ? '…' : ''}` : foto.publicada ? 'Galería pública · Sin asignación a portadas' : 'Solo biblioteca · Nadie la ve en la página'));
        cuerpo.append(boton('Editar y previsualizar', () => abrirEditor({ tipo: 'foto', clave: foto.id })));
        tarjeta.append(vistaFoto, cuerpo); return tarjeta;
    });
    $('[data-fotos-biblioteca]').replaceChildren(...tarjetas);
    $('[data-fotos-contador]').textContent = `${visibles.length} de ${lista.length} fotografías`;
    $('[data-fotos-vacio]').hidden = visibles.length > 0;
}
function actualizar() {
    const numero = contarCambios(pendientes());
    if (numero) aviso('');
    $('[data-fotos-pendientes]').hidden = !numero;
    $('[data-fotos-pendientes-cantidad]').textContent = `${numero} ${numero === 1 ? 'elemento con cambios' : 'elementos con cambios'}`;
    raiz.classList.toggle('fotos-con-pendientes', !!numero);
    $('[data-fotos-sugerencias]').replaceChildren(...Object.values(borrador.fotos).map(foto => { const o = elemento('option'); o.value = foto.titulo; return o; }));
    pintarBiblioteca(); enviarVista();
}
function rellenarEditor() {
    const id = fotoDestino(), foto = borrador.fotos[id];
    const originalId = destino.tipo === 'foto' ? destino.original || destino.clave : base[destino.tipo]?.[destino.clave]?.foto;
    const antes = base.fotos[originalId];
    $('[data-editor-contexto]').textContent = destino.tipo === 'foto' ? 'Biblioteca y galería' : tipos[destino.tipo];
    $('[data-editor-titulo]').textContent = destino.tipo === 'foto' ? foto?.titulo || 'Fotografía' : base[destino.tipo]?.[destino.clave]?.nombre || 'Fotografía del perfil';
    $('[data-editor-alcance]').textContent = destino.tipo === 'foto' ? 'Aquí editas la foto en todos sus usos. Al reemplazarla, la anterior pasa a borradores y la nueva conserva sus ubicaciones.' : 'Cambiar la imagen afecta a este lugar. El título, el álbum y el encuadre pertenecen a la foto y se reflejan en todos sus usos.';
    const fuenteAntes = antes?.url || destino.url || base[destino.tipo]?.[destino.clave]?.url || foto?.url;
    const imgAntes = $('[data-editor-antes]'), imgDespues = $('[data-editor-despues]');
    if (fuenteAntes) imgAntes.src = fuenteAntes; else imgAntes.removeAttribute('src');
    imgAntes.style.objectPosition = antes ? `${antes.foco_x}% ${antes.foco_y}%` : 'center';
    if (foto) { imgDespues.src = foto.url; imgDespues.style.objectPosition = `${foto.foco_x}% ${foto.foco_y}%`; }
    else if (fuenteAntes) imgDespues.src = fuenteAntes;
    for (const campo of camposFoto.filter(campo => campo !== 'posicion')) {
        const control = formulario.elements.namedItem(campo);
        if (control) { control.disabled = !foto; control.value = String(foto?.[campo] ?? ''); control.classList.toggle('dato-modificado', !!foto && base.fotos[id]?.[campo] !== foto[campo]); }
    }
    $('[data-editor-uso]').textContent = (foto ? `Ubicación actual: ${base.albumes[foto.album]}. ${usos(id).join(' · ') || (foto.publicada ? 'Galería publicada.' : 'Guardada sin publicar.')}` : 'Este perfil conserva su retrato actual. Elige otra imagen para previsualizar un reemplazo.') + (destino.tipo === 'guias' && !base.guias[destino.clave].publicado ? ' La fotografía del guía solo se mostrará cuando su perfil esté autorizado para publicación.' : '');
}
function abrirEditor(elegido) {
    if (elegido.tipo !== 'foto' && !base[elegido.tipo]?.[elegido.clave]) { aviso('Este lugar no está disponible para edición.'); return; }
    if (elegido.tipo === 'foto' && !borrador.fotos[elegido.clave]) return;
    if (comparar) { comparar = false; $('[data-fotos-comparar]').textContent = 'Ver antes'; $('[data-fotos-comparar]').setAttribute('aria-pressed', 'false'); enviarVista(); }
    destino = { ...elegido }; rellenarEditor(); dialogos.editor.showModal();
}
function pintarSelector() {
    const buscar = $('[data-elegir-buscar]').value, album = $('[data-elegir-album]').value;
    const fotos = Object.values(borrador.fotos).filter(foto => (!album || foto.album === album) && coincideBusqueda(`${foto.titulo} ${foto.alternativo} ${base.albumes[foto.album]}`, buscar));
    $('[data-elegir-resultados]').replaceChildren(...fotos.map(foto => {
        const b = boton('', () => {
            const original = destino.original || destino.clave;
            elegirFotografia(borrador, destino, foto.id);
            if (destino.tipo === 'foto') destino = { tipo: 'foto', clave: foto.id, original };
            dialogos.elegir.close(); actualizar(); rellenarEditor();
        }, 'foto-eleccion');
        b.dataset.elegirFoto = foto.id; b.append(imagen(foto), elemento('strong', '', foto.titulo), elemento('span', '', `${base.albumes[foto.album]} · ${foto.publicada ? 'Usar esta foto' : 'Usar y publicar al guardar'}`)); return b;
    }));
    if (!fotos.length) $('[data-elegir-resultados]').append(elemento('p', '', 'No encontramos fotos con esa búsqueda.'));
}
function describir(campo, valor) {
    if (campo === 'album') return base.albumes[valor];
    if (campo === 'publicada') return valor ? 'Publicada' : 'Guardada sin publicar';
    if (campo === 'destacada') return valor ? 'Destacada en inicio' : 'Sin destacar';
    if (campo.startsWith('foco_')) return `${valor}%`;
    return String(valor ?? 'Sin selección');
}
function mostrarRevision() {
    const cambios = pendientes(), filas = [];
    for (const [id, datos] of Object.entries(cambios.fotos)) {
        const fila = elemento('article', `fotos-revision-fila ${marca(id, cambios)}`);
        fila.append(imagen(borrador.fotos[id]));
        const texto = elemento('div'); texto.append(elemento('strong', '', borrador.fotos[id].titulo));
        for (const [campo, valor] of Object.entries(datos)) texto.append(elemento('p', '', `${etiquetas[campo]}: ${describir(campo, base.fotos[id][campo])} → ${describir(campo, valor)}`));
        fila.append(texto); filas.push(fila);
    }
    for (const tipo of gruposFotos) for (const [id, nueva] of Object.entries(cambios[tipo])) {
        const fila = elemento('article', 'fotos-revision-fila modificada');
        if (borrador.fotos[nueva]) fila.append(imagen(borrador.fotos[nueva]));
        const texto = elemento('div'); texto.append(elemento('strong', '', `${tipos[tipo]}: ${base[tipo][id].nombre}`), elemento('p', '', `${base.fotos[base[tipo][id].foto]?.titulo || 'Imagen actual'} → ${borrador.fotos[nueva]?.titulo || 'Selección original'}`));
        fila.append(texto); filas.push(fila);
    }
    $('[data-lista-cambios]').replaceChildren(...filas); $('[data-error-guardar]').textContent = ''; dialogos.revision.showModal();
}
async function solicitar(url, opciones = {}) {
    const respuesta = await fetch(url, { credentials: 'same-origin', ...opciones, headers: { Accept: 'application/json', 'X-CSRF-TOKEN': raiz.dataset.csrf, ...opciones.headers } });
    let datos; try { datos = await respuesta.json(); } catch { throw new Error('No pudimos completar la operación. Comprueba la conexión y tu sesión. Tus cambios siguen aquí.'); }
    if (!respuesta.ok) throw new Error(Object.values(datos.errors || {}).flat().join(' ') || datos.message || 'No se guardaron cambios. Vuelve a intentarlo.');
    return datos;
}
function dimensionar() {
    if (!frame.src) return;
    const ancho = $('[data-fotos-marco]').clientWidth - 24;
    const escala = Math.min(1, ancho / dispositivo);
    const superficie = $('[data-fotos-superficie]');
    superficie.style.width = `${dispositivo * escala}px`; superficie.style.height = '780px';
    frame.style.width = `${dispositivo}px`; frame.style.height = `${780 / escala}px`; frame.style.transform = `scale(${escala})`;
}
function abrirPagina(clave) {
    if (!base.paginas[clave]) return;
    $('[data-fotos-pagina]').value = clave;
    const url = new URL(base.paginas[clave].url); url.searchParams.set('vista_fotografias', '1'); frame.src = url.href; dimensionar();
}
function cambiarVista(nueva) {
    vista = nueva;
    raiz.querySelectorAll('[data-vista-fotos]').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.vistaFotos === nueva)));
    $('[data-panel-fotos="biblioteca"]').hidden = nueva !== 'biblioteca'; $('[data-panel-fotos="vista"]').hidden = nueva === 'biblioteca';
    if (nueva === 'galeria') abrirPagina('galeria');
    else if (nueva === 'paginas' && (!frame.getAttribute('src') || $('[data-fotos-pagina]').value === 'galeria')) abrirPagina('inicio');
    dimensionar();
    const url = new URL(location.href); url.searchParams.set('vista', nueva); history.replaceState(null, '', url);
}
function pintarUbicaciones() {
    const buscar = $('[data-buscar-ubicacion]').value;
    const filas = gruposFotos.flatMap(tipo => Object.entries(base[tipo]).filter(([, item]) => coincideBusqueda(`${tipos[tipo]} ${item.nombre}`, buscar)).map(([clave, item]) => {
        const b = boton('', () => { dialogos.ubicaciones.close(); abrirEditor({ tipo, clave }); }, 'foto-ubicacion');
        const id = borrador[tipo][clave]; if (borrador.fotos[id]) b.append(imagen(borrador.fotos[id]));
        const texto = elemento('span'); texto.append(elemento('small', '', tipos[tipo]), elemento('strong', '', item.nombre)); b.append(texto); return b;
    }));
    $('[data-lista-ubicaciones]').replaceChildren(...filas);
}

raiz.querySelectorAll('[data-vista-fotos]').forEach(b => b.addEventListener('click', () => cambiarVista(b.dataset.vistaFotos)));
for (const selector of ['[data-fotos-buscar]', '[data-fotos-album]', '[data-fotos-estado]']) $(selector).addEventListener('input', pintarBiblioteca);
$('[data-limpiar-fotos]').addEventListener('click', () => { for (const s of ['[data-fotos-buscar]', '[data-fotos-album]', '[data-fotos-estado]']) $(s).value = ''; pintarBiblioteca(); });
formulario.addEventListener('submit', e => e.preventDefault());
formulario.addEventListener('input', e => {
    const campo = e.target.name, foto = borrador.fotos[fotoDestino()]; if (!foto || !camposFoto.includes(campo)) return;
    foto[campo] = ['publicada', 'destacada'].includes(campo) ? e.target.value === 'true' : campo.startsWith('foco_') ? Number(e.target.value) : e.target.value;
    e.target.classList.toggle('dato-modificado', base.fotos[foto.id]?.[campo] !== foto[campo]);
    const despues = $('[data-editor-despues]'); despues.style.objectPosition = `${foto.foco_x}% ${foto.foco_y}%`;
    actualizar();
});
$('[data-cambiar-imagen]').addEventListener('click', () => { $('[data-elegir-buscar]').value = ''; $('[data-elegir-album]').value = ''; pintarSelector(); dialogos.elegir.showModal(); });
$('[data-elegir-buscar]').addEventListener('input', pintarSelector); $('[data-elegir-album]').addEventListener('change', pintarSelector);
$('[data-terminar-editor]').addEventListener('click', () => { if (formulario.reportValidity()) dialogos.editor.close(); });
raiz.querySelectorAll('[data-cerrar-dialogo]').forEach(b => b.addEventListener('click', () => { if (!guardando) b.closest('dialog').close(); }));
Object.values(dialogos).forEach(d => d.addEventListener('cancel', e => { if (guardando) e.preventDefault(); }));
$('[data-revisar-fotos]').addEventListener('click', mostrarRevision);
$('[data-descartar-fotos]').addEventListener('click', () => { if (confirm('¿Descartar los cambios pendientes? Las fotos guardadas y la página publicada se conservarán.')) { borrador = crearBorrador(base); actualizar(); aviso('Cambios descartados. La página publicada no se modificó.'); } });
$('[data-aplicar-fotos]').addEventListener('click', async () => {
    if (guardando) return;
    guardando = true; const b = $('[data-aplicar-fotos]'); b.disabled = true; b.textContent = 'Aplicando cambios…';
    try {
        base = await solicitar(raiz.dataset.urlGuardar, { method: 'PATCH', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(pendientes()) });
        borrador = crearBorrador(base); actualizar(); dialogos.revision.close(); aviso('Cambios aplicados. Las fotografías ya se muestran en la página.');
    } catch (error) { $('[data-error-guardar]').textContent = error.message; }
    finally { guardando = false; b.disabled = false; b.textContent = 'Aplicar cambios a la página'; }
});
$('[data-fotos-pagina]').addEventListener('change', e => abrirPagina(e.target.value));
raiz.querySelectorAll('[data-fotos-dispositivo]').forEach(b => b.addEventListener('click', () => { dispositivo = Number(b.dataset.fotosDispositivo); raiz.querySelectorAll('[data-fotos-dispositivo]').forEach(e => e.setAttribute('aria-pressed', String(e === b))); dimensionar(); }));
$('[data-fotos-comparar]').addEventListener('click', e => { comparar = !comparar; e.currentTarget.setAttribute('aria-pressed', String(comparar)); e.currentTarget.textContent = comparar ? 'Volver a mis cambios' : 'Ver antes'; enviarVista(); });
$('[data-abrir-ubicaciones]').addEventListener('click', () => { pintarUbicaciones(); dialogos.ubicaciones.showModal(); });
$('[data-buscar-ubicacion]').addEventListener('input', pintarUbicaciones);
window.addEventListener('message', e => {
    if (e.origin !== location.origin || e.source !== frame.contentWindow) return;
    if (e.data?.tipo === 'milcumbres:fotos-lista') enviarVista();
    if (e.data?.tipo === 'milcumbres:editar-foto') abrirEditor(e.data.destino);
    if (e.data?.tipo === 'milcumbres:fotos-aviso') aviso(e.data.texto);
});
new ResizeObserver(dimensionar).observe($('[data-fotos-marco]'));
window.addEventListener('beforeunload', e => { if (contarCambios(pendientes()) || guardando) { e.preventDefault(); e.returnValue = ''; } });

let vistasCarga = [];
const abrirCarga = () => { $('[data-carga-error]').textContent = ''; dialogos.carga.showModal(); };
$('[data-abrir-carga]').addEventListener('click', abrirCarga); $('[data-cargar-desde-selector]').addEventListener('click', abrirCarga);
const carga = $('[data-formulario-carga]');
carga.querySelector('input[type=file]').addEventListener('change', e => {
    vistasCarga.forEach(URL.revokeObjectURL); vistasCarga = [];
    $('[data-carga-previas]').replaceChildren(...[...e.target.files].slice(0, 10).map(archivo => {
        const f = elemento('figure'), i = elemento('img'); i.src = URL.createObjectURL(archivo); vistasCarga.push(i.src); i.alt = ''; f.append(i, elemento('figcaption', '', archivo.name)); return f;
    }));
});
carga.addEventListener('submit', async e => {
    e.preventDefault(); if (guardando || !carga.reportValidity()) return;
    guardando = true; const b = carga.querySelector('button[type=submit]'); b.disabled = true; $('[data-carga-error]').textContent = '';
    try {
        const previos = pendientes(), ids = new Set(Object.keys(base.fotos));
        base = await solicitar(raiz.dataset.urlSubir, { method: 'POST', body: new FormData(carga) }); borrador = crearBorrador(base);
        for (const [id, datos] of Object.entries(previos.fotos)) Object.assign(borrador.fotos[id], datos);
        for (const tipo of gruposFotos) Object.assign(borrador[tipo], previos[tipo]);
        carga.reset(); $('[data-carga-previas]').replaceChildren(); vistasCarga.forEach(URL.revokeObjectURL); vistasCarga = [];
        dialogos.carga.close(); actualizar(); aviso('Fotografías guardadas en privado. No se han publicado.');
        if (dialogos.elegir.open) { pintarSelector(); const nueva = Object.keys(base.fotos).find(id => !ids.has(id)); $('[data-elegir-resultados]').querySelector(`[data-elegir-foto="${nueva}"]`)?.scrollIntoView({ block: 'center' }); }
    } catch (error) { $('[data-carga-error]').textContent = error.message; }
    finally { guardando = false; b.disabled = false; }
});
const parametros = new URL(location.href).searchParams;
actualizar(); cambiarVista(['biblioteca', 'galeria', 'paginas'].includes(parametros.get('vista')) ? parametros.get('vista') : 'biblioteca');
if (base.paginas[parametros.get('pagina')]) abrirPagina(parametros.get('pagina'));
if (parametros.get('seleccionar')) {
    const [tipo, clave] = parametros.get('seleccionar').split(':');
    if (gruposFotos.includes(tipo) && base[tipo]?.[clave]) abrirEditor({ tipo, clave });
}
