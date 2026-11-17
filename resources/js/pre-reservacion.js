// Conserva los campos existentes al cambiar de fecha o ajustar el tamaño del grupo.
export function iniciarPreReservacion(raiz = document) {
    const cantidad = raiz.getElementById('numero_personas');
    const programacion = raiz.getElementById('programacion_id');
    const contenedor = raiz.getElementById('contenedor-acompanantes');
    if (!cantidad || !programacion || !contenedor) return;

    const grupos = new Map([...contenedor.querySelectorAll('[data-acompanante]')]
        .map((grupo) => [Number(grupo.dataset.acompanante), grupo]));
    const campos = {
        nombre: 'Nombre(s)', apellido_paterno: 'Apellido paterno',
        apellido_materno: 'Apellido materno', fecha_nacimiento: 'Fecha de nacimiento',
    };

    function crearAcompanante(indice) {
        const grupo = raiz.createElement('fieldset');
        grupo.dataset.acompanante = String(indice);
        const leyenda = raiz.createElement('legend');
        leyenda.textContent = `Acompañante ${indice + 1}`;
        grupo.appendChild(leyenda);

        for (const [nombre, texto] of Object.entries(campos)) {
            const etiqueta = raiz.createElement('label');
            const entrada = raiz.createElement('input');
            entrada.id = `acompanante-${indice}-${nombre}`;
            entrada.name = `acompanantes[${indice}][${nombre}]`;
            entrada.type = nombre === 'fecha_nacimiento' ? 'date' : 'text';
            entrada.required = nombre !== 'apellido_materno';
            if (nombre === 'fecha_nacimiento') entrada.max = contenedor.dataset.fechaMaxima;
            etiqueta.htmlFor = entrada.id;
            etiqueta.textContent = texto;
            grupo.appendChild(etiqueta);
            grupo.appendChild(entrada);
        }

        return grupo;
    }

    function actualizarAcompanantes() {
        const cupos = Number(programacion.selectedOptions[0]?.dataset.cupos || 0);
        const total = Number(cantidad.value);
        cantidad.max = String(cupos || 1);
        cantidad.setCustomValidity(total > cupos && cupos > 0 ? 'La cantidad supera los cupos disponibles.' : '');
        const necesarios = Number.isSafeInteger(total) && total > 0 ? Math.max(0, Math.min(total, cupos || 1) - 1) : 0;

        // Los campos retirados se conservan en memoria por si se aumenta de nuevo la cantidad.
        for (const [indice, grupo] of grupos) {
            if (indice >= necesarios) grupo.remove();
        }
        for (let indice = 0; indice < necesarios; indice++) {
            if (!grupos.has(indice)) grupos.set(indice, crearAcompanante(indice));
            const grupo = grupos.get(indice);
            if (grupo.parentElement !== contenedor) contenedor.appendChild(grupo);
        }
    }

    cantidad.addEventListener('input', actualizarAcompanantes);
    programacion.addEventListener('change', actualizarAcompanantes);
    actualizarAcompanantes();
}

if (typeof document !== 'undefined') iniciarPreReservacion();
