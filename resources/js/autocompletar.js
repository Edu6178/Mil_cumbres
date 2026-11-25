let siguienteId = 0;

// La lista vive fuera de los paneles para que las tablas y tarjetas no la recorten.
export function autocompletar(entrada, { obtener, navegar = false, titulo = 'Sugerencias', vistaPrevia = false }) {
    if (!entrada || entrada.dataset.autocompletarListo) return;
    entrada.dataset.autocompletarListo = 'true';
    const id = `sugerencias-${++siguienteId}`;
    const panel = document.createElement('div');
    panel.className = `buscador-emergente${vistaPrevia ? ' buscador-con-vista' : ''}`;
    panel.hidden = true;
    const cabecera = document.createElement('div');
    cabecera.className = 'buscador-cabecera';
    const etiqueta = document.createElement('strong');
    etiqueta.textContent = titulo;
    const cantidad = document.createElement('span');
    cabecera.append(etiqueta, cantidad);
    const cuerpo = document.createElement('div');
    cuerpo.className = 'buscador-cuerpo';
    const lista = document.createElement('div');
    lista.id = id;
    lista.className = 'buscador-lista';
    lista.setAttribute('role', 'listbox');
    lista.setAttribute('aria-label', titulo);
    const mensaje = document.createElement('p');
    mensaje.className = 'buscador-mensaje';
    const vista = document.createElement('aside');
    vista.className = 'buscador-vista';
    vista.setAttribute('aria-label', 'Vista previa del resultado');
    const tipoVista = document.createElement('span');
    const tituloVista = document.createElement('strong');
    const resumenVista = document.createElement('p');
    const accionVista = document.createElement('small');
    accionVista.textContent = navegar ? 'Enter para abrir este resultado →' : 'Enter para aplicar esta búsqueda →';
    vista.append(tipoVista, tituloVista, resumenVista, accionVista);
    cuerpo.append(lista, mensaje);
    if (vistaPrevia) cuerpo.append(vista);
    const ayuda = document.createElement('p');
    ayuda.className = 'buscador-ayuda';
    ayuda.textContent = '↑ ↓ para elegir · Enter para seleccionar · Esc para cerrar';
    panel.append(cabecera, cuerpo, ayuda);
    document.body.append(panel);
    const aviso = document.createElement('span');
    aviso.className = 'buscador-solo-lectores';
    aviso.setAttribute('role', 'status');
    aviso.setAttribute('aria-live', 'polite');
    entrada.after(aviso);
    entrada.setAttribute('role', 'combobox');
    entrada.setAttribute('aria-autocomplete', 'list');
    entrada.setAttribute('aria-controls', id);
    entrada.setAttribute('aria-expanded', 'false');
    entrada.setAttribute('autocomplete', 'off');

    let resultados = [], activo = -1, temporizador, peticion, version = 0, componiendo = false;
    function posicionar() {
        if (panel.hidden) return;
        const rect = entrada.getBoundingClientRect();
        const alto = window.visualViewport?.height ?? window.innerHeight;
        if (rect.bottom < 0 || rect.top > alto) { cerrar(); return; }
        const ancho = Math.min(vistaPrevia ? 660 : Math.max(350, rect.width), window.innerWidth - 24);
        panel.style.width = `${ancho}px`;
        panel.style.left = `${Math.max(12, Math.min(rect.left, window.innerWidth - ancho - 12))}px`;
        const arriba = alto - rect.bottom < 210 && rect.top > alto - rect.bottom;
        panel.style.maxHeight = `${Math.max(120, Math.min(470, (arriba ? rect.top : alto - rect.bottom) - 18))}px`;
        panel.style.top = arriba ? 'auto' : `${rect.bottom + 8}px`;
        panel.style.bottom = arriba ? `${window.innerHeight - rect.top + 8}px` : 'auto';
    }
    function mostrar() {
        panel.hidden = false;
        entrada.setAttribute('aria-expanded', 'true');
        posicionar();
    }
    function cancelar() {
        clearTimeout(temporizador);
        peticion?.abort();
        version++;
    }
    function cerrar() {
        cancelar();
        panel.hidden = true;
        activo = -1;
        entrada.setAttribute('aria-expanded', 'false');
        entrada.removeAttribute('aria-activedescendant');
        entrada.removeAttribute('aria-busy');
    }
    function seleccionar(indice, desplazar = false) {
        activo = indice;
        [...lista.children].forEach((opcion, n) => opcion.setAttribute('aria-selected', String(n === indice)));
        const opcion = lista.children[indice];
        const item = resultados[indice];
        if (!item || !opcion) return;
        entrada.setAttribute('aria-activedescendant', opcion.id);
        tipoVista.textContent = item.tipo;
        tituloVista.textContent = item.titulo;
        resumenVista.textContent = item.resumen;
        if (desplazar) opcion.scrollIntoView({ block: 'nearest' });
    }
    function aplicar(indice) {
        const item = resultados[indice];
        if (!item) return;
        if (navegar) {
            cerrar();
            window.location.assign(item.url);
        } else {
            entrada.value = item.valor ?? item.titulo;
            entrada.dispatchEvent(new Event('input', { bubbles: true }));
            cerrar();
            entrada.form?.requestSubmit();
        }
    }
    function estado(texto) {
        lista.replaceChildren();
        lista.hidden = true;
        vista.hidden = true;
        mensaje.hidden = false;
        mensaje.textContent = texto;
        cantidad.textContent = '';
        activo = -1;
        resultados = [];
        entrada.removeAttribute('aria-activedescendant');
        mostrar();
    }
    async function buscar() {
        if (document.activeElement !== entrada || componiendo) return;
        cancelar();
        const turno = version;
        peticion = new AbortController();
        entrada.setAttribute('aria-busy', 'true');
        try {
            const items = await obtener(entrada.value.trim(), peticion.signal);
            if (turno !== version || document.activeElement !== entrada) return;
            entrada.removeAttribute('aria-busy');
            if (!items.length) {
                estado(entrada.value.trim() ? 'No hay coincidencias. Prueba con otra palabra o ajusta los filtros.' : 'Escribe el inicio de un nombre, un talento o una experiencia.');
                aviso.textContent = mensaje.textContent;
                return;
            }
            resultados = items;
            activo = -1;
            lista.replaceChildren();
            lista.hidden = false;
            mensaje.hidden = true;
            vista.hidden = !vistaPrevia;
            cantidad.textContent = `${items.length} ${items.length === 1 ? 'resultado' : 'resultados'}`;
            items.forEach((item, indice) => {
                const opcion = document.createElement('div');
                opcion.id = `${id}-${indice}`;
                opcion.className = 'buscador-opcion';
                opcion.setAttribute('role', 'option');
                opcion.setAttribute('aria-selected', 'false');
                const tipo = document.createElement('small');
                tipo.textContent = item.tipo;
                const nombre = document.createElement('strong');
                nombre.textContent = item.titulo;
                const contexto = document.createElement('span');
                contexto.textContent = item.contexto;
                opcion.append(tipo, nombre, contexto);
                opcion.addEventListener('pointermove', () => seleccionar(indice));
                opcion.addEventListener('click', () => aplicar(indice));
                lista.append(opcion);
            });
            tipoVista.textContent = 'Encuentra tu siguiente paso';
            tituloVista.textContent = 'Todo, más cerca.';
            resumenVista.textContent = 'Pasa por un resultado o usa las flechas para ver un resumen antes de abrirlo.';
            aviso.textContent = `${cantidad.textContent}. Usa las flechas para elegir.`;
            mostrar();
        } catch (error) {
            if (turno !== version || error.name === 'AbortError') return;
            entrada.removeAttribute('aria-busy');
            estado('No pudimos cargar las sugerencias. Puedes usar el botón Buscar para continuar.');
            aviso.textContent = mensaje.textContent;
        }
    }
    entrada.addEventListener('input', () => {
        cancelar();
        if (componiendo) return;
        estado('Buscando coincidencias…');
        temporizador = setTimeout(buscar, 160);
    });
    entrada.addEventListener('focus', buscar);
    entrada.addEventListener('compositionstart', () => { componiendo = true; cerrar(); });
    entrada.addEventListener('compositionend', () => { componiendo = false; buscar(); });
    entrada.addEventListener('keydown', (evento) => {
        if (componiendo || evento.isComposing) return;
        if (evento.key === 'Escape') { evento.preventDefault(); cerrar(); return; }
        if (evento.key === 'Tab') { cerrar(); return; }
        if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
            evento.preventDefault();
            if (panel.hidden) { buscar(); return; }
            if (resultados.length) seleccionar((activo + (evento.key === 'ArrowDown' ? 1 : activo < 0 ? 0 : -1) + resultados.length) % resultados.length, true);
        } else if (evento.key === 'Enter' && !panel.hidden && activo >= 0) {
            evento.preventDefault();
            evento.stopPropagation();
            aplicar(activo);
        }
    });
    panel.addEventListener('mousedown', (evento) => evento.preventDefault());
    entrada.addEventListener('blur', () => setTimeout(() => { if (document.activeElement !== entrada) cerrar(); }, 120));
    entrada.form?.addEventListener('submit', cerrar);
    document.addEventListener('pointerdown', (evento) => { if (evento.target !== entrada && !panel.contains(evento.target)) cerrar(); });
    window.addEventListener('resize', posicionar);
    window.addEventListener('scroll', posicionar, true);
    window.visualViewport?.addEventListener('resize', posicionar);
    window.addEventListener('pagehide', cerrar);
}

export function activarBuscadoresRemotos() {
    document.querySelectorAll('[data-buscador-remoto]').forEach((entrada) => {
        const ambito = entrada.dataset.buscadorRemoto;
        autocompletar(entrada, {
            titulo: ambito === 'todo' ? 'Buscar en Mil Cumbres' : 'Coincidencias en este apartado',
            navegar: ambito === 'todo', vistaPrevia: ambito === 'todo',
            obtener: async (texto, signal) => {
                const url = new URL(entrada.dataset.urlSugerencias, window.location.href);
                url.searchParams.set('buscar', texto);
                url.searchParams.set('ambito', ambito);
                if (ambito !== 'todo') {
                    for (const nombre of ['estado', 'categoria']) {
                        const valor = entrada.form?.elements.namedItem(nombre)?.value;
                        if (valor) url.searchParams.set(nombre, valor);
                    }
                }
                const respuesta = await fetch(url, { signal, headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store' });
                if (!respuesta.ok || !respuesta.headers.get('content-type')?.includes('application/json')) throw new Error('No se pudo consultar la búsqueda');
                return (await respuesta.json()).resultados;
            },
        });
    });
}
