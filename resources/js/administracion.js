// El menú móvil conserva los enlaces accesibles y se cierra con Escape.
document.body.classList.add('adm-con-js');
const boton = document.querySelector('.adm-menu-boton');
const menu = document.querySelector('#menu-administracion');
const pantalla = window.matchMedia('(max-width: 1000px)');

function cambiarMenu(abierto) {
    document.body.classList.toggle('adm-menu-abierto', abierto);
    menu?.classList.toggle('adm-abierto', abierto);
    boton?.setAttribute('aria-expanded', String(abierto));
    boton?.setAttribute('aria-label', abierto ? 'Cerrar navegación administrativa' : 'Abrir navegación administrativa');
}

boton?.addEventListener('click', () => cambiarMenu(boton.getAttribute('aria-expanded') !== 'true'));
pantalla.addEventListener('change', () => cambiarMenu(false));
document.addEventListener('click', (evento) => {
    if (pantalla.matches && menu?.classList.contains('adm-abierto') && !menu.contains(evento.target) && !boton?.contains(evento.target)) cambiarMenu(false);
});
document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && boton?.getAttribute('aria-expanded') === 'true') {
        cambiarMenu(false);
        boton.focus();
    }
});

// Desactiva únicamente el botón durante el envío y lo restaura al volver con el historial.
document.querySelectorAll('[data-formulario-administracion]').forEach((formulario) => {
    const enviar = formulario.querySelector('button[type="submit"]');
    const texto = enviar?.querySelector('span');
    const original = texto?.textContent;
    const inicialmenteDesactivado = enviar?.disabled;
    formulario.addEventListener('submit', () => {
        if (!enviar) return;
        enviar.disabled = true;
        formulario.setAttribute('aria-busy', 'true');
        if (texto) texto.textContent = 'Guardando…';
    });
    window.addEventListener('pageshow', () => {
        if (!enviar) return;
        enviar.disabled = inicialmenteDesactivado;
        formulario.removeAttribute('aria-busy');
        if (texto) texto.textContent = original;
    });
});

// Anime.js aporta una entrada breve; todo el contenido es visible si no carga o se reduce el movimiento.
const preferencia = window.matchMedia('(prefers-reduced-motion: reduce)');
if (!preferencia.matches) {
    import('animejs').then(({ animate, stagger }) => {
        if (preferencia.matches) return;
        const elementos = document.querySelectorAll('.adm-revelar');
        if (!elementos.length) return;
        const entrada = animate(elementos, { y: [8, 0], opacity: [0.8, 1], duration: 450, delay: stagger(55), ease: 'outCubic' });
        preferencia.addEventListener('change', () => { if (preferencia.matches) entrada.revert(); });
    }).catch(() => {});
}
