import './pre-reservacion.js';
import './transiciones.js';

// El carrusel comparte los perfiles existentes y se descarga solo donde se muestra.
if (document.querySelector('[data-carrusel-guias]')) {
    import('./carrusel-guias.js').then(({ iniciarCarruseles }) => iniciarCarruseles()).catch(() => {
        // Si falla la mejora visual, las fichas y sus enlaces siguen disponibles.
    });
}

// Las interacciones de presentación funcionan sin peticiones ni datos de ejemplo.
document.body.classList.add('con-js');

const cabecera = document.querySelector('#cabecera');
const botonMenu = document.querySelector('.boton-menu');
const navegacion = document.querySelector('#navegacion-principal');
const pantallaMovil = window.matchMedia('(max-width: 920px)');

// Abre el menú en teléfonos y sincroniza su descripción para lectores de pantalla.
function cambiarMenu(abierto) {
    if (!botonMenu || !navegacion) return;

    navegacion.classList.toggle('abierta', abierto);
    botonMenu.setAttribute('aria-expanded', String(abierto));
    botonMenu.setAttribute('aria-label', abierto ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
}

botonMenu?.addEventListener('click', () => {
    cambiarMenu(botonMenu.getAttribute('aria-expanded') !== 'true');
});

navegacion?.addEventListener('click', (evento) => {
    if (evento.target.closest('a') && pantallaMovil.matches) cambiarMenu(false);
});

document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && botonMenu?.getAttribute('aria-expanded') === 'true') {
        cambiarMenu(false);
        botonMenu.focus();
    }
});

pantallaMovil.addEventListener('change', () => cambiarMenu(false));

// Una sombra discreta separa la cabecera del contenido al desplazarse.
function actualizarCabecera() {
    cabecera?.classList.toggle('con-sombra', window.scrollY > 12);
}
window.addEventListener('scroll', actualizarCabecera, { passive: true });
actualizarCabecera();

const pestanas = [...document.querySelectorAll('[data-catalogo]')];
const panelCatalogo = document.querySelector('#catalogo-contenido');
const tituloVacio = document.querySelector('#titulo-catalogo-vacio');
const descripcionVacia = document.querySelector('#descripcion-catalogo-vacio');

// Cambia la explicación del estado vacío; todavía no hay servicios publicados que filtrar.
function seleccionarCatalogo(pestana, enfocar = false) {
    if (!panelCatalogo || !tituloVacio || !descripcionVacia || !pestana) return;

    pestanas.forEach((elemento) => {
        const seleccionada = elemento === pestana;
        elemento.setAttribute('aria-selected', String(seleccionada));
        elemento.tabIndex = seleccionada ? 0 : -1;
    });

    panelCatalogo.setAttribute('aria-labelledby', pestana.id);

    if (pestana.dataset.catalogo === 'senderismo') {
        tituloVacio.textContent = 'Cada ruta empieza con un primer paso.';
        descripcionVacia.textContent = 'Todavía no hay rutas de senderismo publicadas. Aquí conocerás cada recorrido, su itinerario, duración y disponibilidad cuando se publiquen.';
    } else {
        tituloVacio.textContent = 'Las mejores historias necesitan un buen comienzo.';
        descripcionVacia.textContent = 'Todavía no hay experiencias publicadas. Aquí encontrarás los recorridos, itinerarios y detalles de cada servicio cuando estén disponibles.';
    }

    if (enfocar) pestana.focus();
}

pestanas.forEach((pestana, indice) => {
    pestana.addEventListener('click', () => seleccionarCatalogo(pestana));

    // Las flechas, Inicio y Fin permiten recorrer las pestañas con el teclado.
    pestana.addEventListener('keydown', (evento) => {
        let destino;
        if (evento.key === 'ArrowRight') destino = (indice + 1) % pestanas.length;
        if (evento.key === 'ArrowLeft') destino = (indice - 1 + pestanas.length) % pestanas.length;
        if (evento.key === 'Home') destino = 0;
        if (evento.key === 'End') destino = pestanas.length - 1;

        if (destino !== undefined) {
            evento.preventDefault();
            seleccionarCatalogo(pestanas[destino], true);
        }
    });
});

document.querySelector('[data-ver-senderismo]')?.addEventListener('click', () => {
    seleccionarCatalogo(pestanas.find((pestana) => pestana.dataset.catalogo === 'senderismo'));
});

// El botón modifica solo la visibilidad; nunca guarda ni envía la contraseña por su cuenta.
document.querySelectorAll('[data-alternar-contrasena]').forEach((boton) => {
    boton.addEventListener('click', () => {
        const campo = document.getElementById(boton.getAttribute('aria-controls'));
        if (!campo) return;

        const mostrar = campo.type === 'password';
        campo.type = mostrar ? 'text' : 'password';
        boton.setAttribute('aria-pressed', String(mostrar));
        const confirmacion = campo.name === 'contrasena_confirmation' ? 'confirmación de contraseña' : 'contraseña';
        boton.setAttribute('aria-label', `${mostrar ? 'Ocultar' : 'Mostrar'} ${confirmacion}`);
        boton.querySelector('span').textContent = mostrar ? 'Ocultar' : 'Mostrar';
    });
});

// Evita el doble clic durante el envío; las validaciones reales siguen estando en Laravel.
document.querySelectorAll('[data-formulario-cuenta]').forEach((formulario) => {
    const boton = formulario.querySelector('button[type="submit"]');
    const etiqueta = boton?.querySelector('[data-texto-envio]');
    const textoOriginal = etiqueta?.textContent;

    formulario.addEventListener('submit', () => {
        if (!boton || !etiqueta) return;
        boton.disabled = true;
        etiqueta.textContent = 'Un momento…';
        formulario.setAttribute('aria-busy', 'true');
    });

    // Restaura el botón al volver con el historial sin recargar ni borrar lo escrito.
    window.addEventListener('pageshow', () => {
        if (!boton || !etiqueta) return;
        boton.disabled = false;
        etiqueta.textContent = textoOriginal;
        formulario.removeAttribute('aria-busy');
    });
});

// Anime.js se carga después del contenido; sin JavaScript o con movimiento reducido, todo permanece visible.
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    import('./animaciones.js').then(async ({ iniciarAnimaciones }) => {
        // La entrada del contenido comienza al terminar el cambio de página, para evitar saltos entre efectos.
        if (document.activeViewTransition) await document.activeViewTransition.finished.catch(() => {});
        iniciarAnimaciones();
    }).catch(() => {
        // Si la animación no carga, los formularios y la navegación continúan funcionando.
    });
}
