// Los filtros mejoran el formulario GET: sin JavaScript, Buscar y las líneas siguen enviando los mismos campos.
const normalizar = (texto) => String(texto).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es').trim();
const catalogo = document.querySelector('[data-catalogo-publico]');

if (catalogo) {
    const formulario = catalogo.querySelector('[data-filtros-catalogo]');
    const buscar = catalogo.querySelector('[data-buscar-servicio]');
    const duracion = catalogo.querySelector('[data-duracion-servicio]');
    const lineaActual = catalogo.querySelector('[data-linea-actual]');
    const botones = [...catalogo.querySelectorAll('[data-filtro-linea]')];
    const servicios = [...catalogo.querySelectorAll('[data-servicio]')].map((elemento) => ({
        elemento, texto: normalizar(elemento.dataset.busqueda), linea: elemento.dataset.linea,
        minutos: elemento.dataset.duracion === '' ? null : Number(elemento.dataset.duracion),
    }));
    const vacio = catalogo.querySelector('[data-vacio-catalogo]');
    const total = catalogo.querySelector('[data-total-servicios]');
    const etiqueta = catalogo.querySelector('[data-etiqueta-resultados]');

    function filtrar() {
        const consulta = normalizar(buscar.value);
        const linea = lineaActual.value;
        let visibles = 0;
        for (const servicio of servicios) {
            const tiempo = duracion.value === '' || (servicio.minutos !== null && (
                (duracion.value === 'corta' && servicio.minutos <= 180) ||
                (duracion.value === 'media' && servicio.minutos > 180 && servicio.minutos <= 360) ||
                (duracion.value === 'larga' && servicio.minutos > 360)
            ));
            const visible = servicio.texto.includes(consulta) && (!linea || servicio.linea === linea) && tiempo;
            servicio.elemento.hidden = !visible;
            if (visible) visibles++;
        }
        total.textContent = String(visibles);
        etiqueta.textContent = visibles === 1 ? 'experiencia publicada' : 'experiencias publicadas';
        vacio.hidden = visibles > 0;
        vacio.querySelector('[data-vacio-titulo]').textContent = servicios.length ? 'No encontramos experiencias con esos filtros.' : 'Las mejores historias necesitan un buen comienzo.';
        vacio.querySelector('[data-vacio-descripcion]').textContent = servicios.length ? 'Prueba con otro nombre, una duración distinta o todas las líneas de servicio.' : 'Estamos preparando el catálogo. Aquí encontrarás cada ruta, su itinerario, duración, precio y las fechas publicadas.';
        botones.forEach((boton) => boton.setAttribute('aria-pressed', String(boton.dataset.filtroLinea === linea)));

        // La URL permite compartir la búsqueda sin guardar información personal ni consultar otros módulos.
        const url = new URL(window.location.href);
        for (const [clave, valor] of [['buscar', buscar.value.trim()], ['linea', linea], ['duracion', duracion.value]]) {
            if (valor) url.searchParams.set(clave, valor); else url.searchParams.delete(clave);
        }
        window.history.replaceState(null, '', url);
    }

    formulario.addEventListener('submit', (evento) => { evento.preventDefault(); filtrar(); });
    botones.forEach((boton) => boton.addEventListener('click', (evento) => {
        evento.preventDefault();
        if (boton.disabled) return;
        lineaActual.value = boton.dataset.filtroLinea;
        filtrar();
    }));
    buscar.addEventListener('input', filtrar);
    duracion.addEventListener('change', filtrar);
    catalogo.querySelector('[data-limpiar-catalogo]').addEventListener('click', (evento) => {
        evento.preventDefault(); buscar.value = ''; duracion.value = ''; lineaActual.value = ''; filtrar(); buscar.focus();
    });
    catalogo.querySelectorAll('[data-vista-catalogo]').forEach((boton) => boton.addEventListener('click', () => {
        catalogo.querySelector('[data-lista-servicios]').classList.toggle('publico-vista-lista', boton.dataset.vistaCatalogo === 'lista');
        catalogo.querySelectorAll('[data-vista-catalogo]').forEach((opcion) => opcion.setAttribute('aria-pressed', String(opcion === boton)));
    }));
}

const equipo = document.querySelector('[data-equipo-publico]');
if (equipo) {
    const entrada = equipo.querySelector('[data-buscar-guia]');
    const formulario = entrada.closest('form');
    const guias = [...equipo.querySelectorAll('[data-guia]')].map((elemento) => ({ elemento, texto: normalizar(elemento.dataset.busqueda) }));
    const filtrar = () => {
        const texto = normalizar(entrada.value);
        let visibles = 0;
        guias.forEach((guia) => {
            guia.elemento.hidden = !guia.texto.includes(texto);
            if (!guia.elemento.hidden) visibles++;
        });
        equipo.querySelector('[data-total-guias]').textContent = String(visibles);
        const vacio = equipo.querySelector('[data-vacio-guias]');
        vacio.hidden = visibles > 0;
        vacio.querySelector('[data-vacio-titulo]').textContent = guias.length ? 'No encontramos perfiles con esa búsqueda.' : 'Las personas detrás de la experiencia.';
        vacio.querySelector('[data-vacio-descripcion]').textContent = guias.length ? 'Prueba con otro nombre, talento o especialidad.' : 'Todavía no hay perfiles publicados. Aquí podrás conocer su formación, especialidad y talentos cuando su información esté autorizada.';
        const url = new URL(window.location.href);
        if (entrada.value.trim()) url.searchParams.set('buscar', entrada.value.trim()); else url.searchParams.delete('buscar');
        window.history.replaceState(null, '', url);
        equipo.querySelector('[data-carrusel-guias]')?.dispatchEvent(new Event('guias:filtrados'));
    };
    entrada.addEventListener('input', filtrar);
    formulario.addEventListener('submit', (evento) => { evento.preventDefault(); filtrar(); });
}
