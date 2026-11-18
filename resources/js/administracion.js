import './transiciones.js';

// Cambiar de venta sugiere su moneda; el servidor vuelve a comprobarla al guardar el abono.
const ventaPago = document.querySelector('#reservacion_id');
const monedaPago = document.querySelector('#moneda');
if (ventaPago && monedaPago) {
    const actualizarMoneda = () => { const moneda = ventaPago.selectedOptions[0]?.dataset.moneda; if (moneda) monedaPago.value = moneda; };
    ventaPago.addEventListener('change', actualizarMoneda);
    actualizarMoneda();
}

// El menú móvil conserva los enlaces accesibles y se cierra con Escape.
document.body.classList.add('adm-con-js');
const boton = document.querySelector('.adm-menu-boton');
const menu = document.querySelector('#menu-administracion');
const pantalla = window.matchMedia('(max-width: 860px)');

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

// El menú de la cuenta se cierra al salir de él o al pulsar Escape.
const perfil = document.querySelector('.adm-menu-perfil');
document.addEventListener('click', (evento) => {
    if (perfil?.open && !perfil.contains(evento.target)) perfil.open = false;
});
document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && perfil?.open) {
        perfil.open = false;
        perfil.querySelector('summary')?.focus();
    }
});

// La vista previa usa solo el archivo elegido; la foto se guarda al enviar el formulario.
const archivoFoto = document.querySelector('[data-archivo-foto]');
const vistaFoto = document.querySelector('[data-vista-foto]');
const nombreFoto = document.querySelector('[data-nombre-foto]');
let vistaTemporal;
archivoFoto?.addEventListener('change', () => {
    if (vistaTemporal) URL.revokeObjectURL(vistaTemporal);
    archivoFoto.setCustomValidity('');
    const archivo = archivoFoto.files?.[0];
    if (nombreFoto) nombreFoto.textContent = archivo?.name || 'Ningún archivo seleccionado';
    if (!archivo) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(archivo.type) || archivo.size > 2 * 1024 * 1024) {
        archivoFoto.setCustomValidity('Elige una foto JPG, PNG o WebP de hasta 2 MB.');
        archivoFoto.reportValidity();
        return;
    }
    vistaTemporal = URL.createObjectURL(archivo);
    if (vistaFoto) vistaFoto.src = vistaTemporal;
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

// La entrada del panel se prepara en la cabecera y CSS, sin reiniciar contenido que ya se ve.
