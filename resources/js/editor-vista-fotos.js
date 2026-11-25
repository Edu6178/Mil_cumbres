// Esta mejora se carga únicamente en la vista de administración autorizada por el servidor.
const raiz = document.documentElement;
raiz.classList.add('vista-editor-fotos');
document.querySelectorAll('[data-sonidos-bosque], [data-cursor-bosque], [data-nivel-movimiento]').forEach(control => { control.disabled = true; });
const capa = document.createElement('div'); capa.className = 'editor-fotos-capa'; document.body.append(capa);
const originales = new Map(), botones = new Map();
let estado, cuadro;
const avisar = datos => parent.postMessage(datos, location.origin);
const identificar = img => ({ tipo: img.dataset.editorTipo || 'foto', clave: img.dataset.editorClave || img.dataset.fotografiaId });
const guardarOriginal = img => {
    if (!originales.has(img)) originales.set(img, { src: img.src, alt: img.alt, foco: img.style.objectPosition, id: img.dataset.fotografiaId });
};
function fotosEditables() {
    return [...document.querySelectorAll('img[data-fotografia-id], img[data-editor-tipo="guias"]')].filter(img => img.dataset.editorClave || img.dataset.fotografiaId);
}
function abrir(img) {
    avisar({ tipo: 'milcumbres:editar-foto', destino: { ...identificar(img), url: originales.get(img)?.src || img.src } });
}
function posicionar() {
    cuadro = null;
    const techo = document.querySelector('.cabecera')?.getBoundingClientRect().bottom || 34;
    for (const [img, boton] of botones) {
        const r = img.getBoundingClientRect();
        const arriba = Math.max(techo, r.top), abajo = Math.min(innerHeight, r.bottom), izquierda = Math.max(0, r.left), derecha = Math.min(innerWidth, r.right);
        boton.hidden = !img.isConnected || img.closest('[hidden]') !== null || getComputedStyle(img).display === 'none' || abajo - arriba < 15 || derecha - izquierda < 15;
        // El marco escalado puede demorar la carga diferida nativa; prioriza solo las fotos visibles.
        if (!boton.hidden && img.loading === 'lazy') img.loading = 'eager';
        boton.style.cssText = `top:${arriba}px;left:${izquierda}px;width:${derecha - izquierda}px;height:${abajo - arriba}px`;
    }
}
function solicitarPosicion() { if (!cuadro) cuadro = requestAnimationFrame(posicionar); }
function registrar() {
    for (const img of fotosEditables()) {
        if (botones.has(img)) continue;
        guardarOriginal(img);
        const boton = document.createElement('button'); boton.type = 'button'; boton.className = 'editor-foto-boton';
        const etiqueta = document.createElement('span'); etiqueta.textContent = 'Editar imagen'; boton.append(etiqueta);
        boton.addEventListener('click', () => abrir(img)); img.addEventListener('load', solicitarPosicion);
        botones.set(img, boton); capa.append(boton);
    }
    solicitarPosicion();
}
function sincronizarGaleria() {
    const mosaico = document.querySelector('.galeria-mosaico'); if (!mosaico || !estado) return;
    const modelo = mosaico.querySelector('[data-foto-galeria]'); if (!modelo) return;
    const existentes = new Map([...mosaico.children].map(f => [f.querySelector('img')?.dataset.editorClave, f]));
    for (const foto of Object.values(estado.datos.fotos)) {
        if (existentes.has(foto.id) || !foto.publicada) continue;
        const ficha = modelo.cloneNode(true); ficha.hidden = false;
        const img = ficha.querySelector('img'); img.dataset.editorTipo = 'foto'; img.dataset.editorClave = foto.id; img.dataset.fotografiaId = foto.id;
        img.removeAttribute('srcset'); img.src = foto.url; img.alt = foto.alternativo; img.width = foto.ancho; img.height = foto.alto;
        ficha.querySelector('a').href = foto.url; mosaico.append(ficha);
    }
    // La sustituta ocupa el mismo lugar que tendrá al publicar, incluso si acaba de cargarse.
    const ordenadas = [...mosaico.children].sort((a, b) => {
        const izquierda = estado.datos.fotos[a.querySelector('img')?.dataset.editorClave];
        const derecha = estado.datos.fotos[b.querySelector('img')?.dataset.editorClave];
        return (izquierda?.posicion ?? 0) - (derecha?.posicion ?? 0) || (izquierda?.id ?? '').localeCompare(derecha?.id ?? '');
    });
    mosaico.append(...ordenadas);
    const buscar = document.querySelector('[data-buscar-foto]')?.value || '', album = document.querySelector('[data-album-foto]')?.value || '';
    const normalizar = texto => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    let visibles = 0;
    for (const ficha of mosaico.children) {
        const img = ficha.querySelector('img'), foto = estado.datos.fotos[img?.dataset.editorClave]; if (!foto) continue;
        ficha.querySelector('h2').textContent = foto.titulo;
        ficha.querySelector('figcaption > span').textContent = estado.albumes[foto.album];
        const resumen = ficha.querySelector('[data-foto-resumen]'); if (resumen) resumen.textContent = foto.alternativo;
        const enlace = ficha.querySelector('[data-abrir-foto]');
        if (enlace) { enlace.dataset.titulo = foto.titulo; enlace.dataset.descripcion = foto.alternativo; enlace.dataset.albumNombre = estado.albumes[foto.album]; enlace.setAttribute('aria-label', `Ampliar: ${foto.titulo}`); }
        ficha.dataset.album = foto.album; ficha.dataset.texto = `${foto.titulo} ${foto.alternativo}`;
        const pendienteOcultar = estado.cambios.fotos[foto.id]?.publicada === false;
        ficha.hidden = (!foto.publicada && !pendienteOcultar) || (!!album && album !== foto.album) || !normalizar(buscar).split(/\s+/).every(p => normalizar(`${foto.titulo} ${foto.alternativo}`).includes(p));
        if (!ficha.hidden) visibles++;
    }
    const contador = document.querySelector('[data-total-fotos]'); if (contador) contador.textContent = String(visibles);
    const vacio = document.querySelector('[data-fotos-vacio]'); if (vacio) vacio.hidden = visibles > 0;
}
function pintar() {
    if (!estado) return;
    sincronizarGaleria(); registrar();
    for (const img of fotosEditables()) {
        const destino = identificar(img), original = originales.get(img);
        const id = destino.tipo === 'foto' ? destino.clave : estado.datos[destino.tipo]?.[destino.clave];
        const foto = estado.datos.fotos[id];
        if (foto) {
            img.removeAttribute('srcset'); if (img.src !== foto.url) img.src = foto.url;
            img.alt = foto.alternativo; img.style.objectPosition = `${foto.foco_x}% ${foto.foco_y}%`;
        } else if (original) { img.removeAttribute('srcset'); img.src = original.src; img.alt = original.alt; img.style.objectPosition = original.foco; }
        const boton = botones.get(img); if (!boton) continue;
        const modificada = estado.cambios.fotos[id] || (destino.tipo !== 'foto' && Object.hasOwn(estado.cambios[destino.tipo] || {}, destino.clave));
        const ocultar = destino.tipo === 'foto' && estado.cambios.fotos[id]?.publicada === false;
        const publicar = destino.tipo === 'foto' && estado.cambios.fotos[id]?.publicada === true;
        boton.classList.toggle('modificada', !!modificada); boton.classList.toggle('se-oculta', ocultar); boton.classList.toggle('se-publica', publicar && !ocultar);
        boton.querySelector('span').textContent = ocultar ? 'Se ocultará · Editar' : publicar ? 'Se publicará · Editar' : modificada ? 'Modificada · Editar' : 'Editar imagen';
        boton.setAttribute('aria-label', `${boton.querySelector('span').textContent}: ${foto?.titulo || 'Fotografía del guía'}`);
    }
    solicitarPosicion();
}
window.addEventListener('message', e => {
    if (e.origin !== location.origin || e.source !== parent || e.data?.tipo !== 'milcumbres:fotos-estado') return;
    estado = e.data; pintar();
});
// Los enlaces internos mantienen esta vista; los formularios nunca crean reservas desde aquí.
document.addEventListener('submit', e => { e.preventDefault(); e.stopImmediatePropagation(); avisar({ tipo: 'milcumbres:fotos-aviso', texto: 'Esta vista permite editar fotografías. Los formularios no se envían desde la previsualización.' }); }, true);
document.addEventListener('click', e => {
    const enlace = e.target.closest('a[href]'); if (!enlace) return;
    const url = new URL(enlace.href);
    if (enlace.closest('[data-foto-galeria]')) { e.preventDefault(); e.stopImmediatePropagation(); const img = enlace.querySelector('img'); if (img) abrir(img); return; }
    if (url.origin === location.origin && (url.pathname === '/' || /^\/(catalogo|guias(?:\/\d+)?|experiencias(?:\/\d+)?|nosotros|turismo-regenerativo|operadora|consultoria|comunidades|galeria)\/?$/.test(url.pathname))) {
        if (url.pathname === location.pathname && url.hash) return;
        url.searchParams.set('vista_fotografias', '1'); e.preventDefault(); e.stopImmediatePropagation(); location.href = url.href;
    } else { e.preventDefault(); e.stopImmediatePropagation(); avisar({ tipo: 'milcumbres:fotos-aviso', texto: 'Los enlaces de contacto y las acciones de clientes no se abren desde esta vista de edición.' }); }
}, true);
document.querySelectorAll('[data-buscar-foto], [data-album-foto]').forEach(e => e.addEventListener('input', () => { sincronizarGaleria(); registrar(); solicitarPosicion(); }));
window.addEventListener('scroll', solicitarPosicion, { passive: true }); window.addEventListener('resize', solicitarPosicion);
document.addEventListener('click', solicitarPosicion, { passive: true });
new ResizeObserver(solicitarPosicion).observe(document.body);
document.addEventListener('milcumbres:visor', solicitarPosicion);
registrar(); avisar({ tipo: 'milcumbres:fotos-lista' });
setTimeout(solicitarPosicion, 1200);
