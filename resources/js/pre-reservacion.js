// El cálculo visible es informativo; el servidor vuelve a leer la tarifa al guardar la solicitud.
export function resumirTarifa(datos, personas) {
    const precio = datos?.precioCentavos === '' || datos?.precioCentavos == null ? null : Number(datos.precioCentavos);
    const unidades = { persona: 'por persona', grupo: 'por grupo', servicio: 'por servicio' };
    const moneda = ['MXN', 'USD', 'EUR'].includes(datos?.moneda) ? datos.moneda : 'MXN';
    const desde = datos?.tipoPrecio === 'desde' ? 'Desde ' : '';
    const formato = (centavos) => '$' + new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(centavos / 100) + ' ' + moneda;
    const valido = Number.isSafeInteger(precio) && precio >= 0;
    const total = valido && unidades[datos?.unidadCobro] && Number.isSafeInteger(personas) && personas > 0
        ? precio * (datos.unidadCobro === 'persona' ? personas : 1) : null;
    const calculable = total !== null && Number.isSafeInteger(total) && total <= 999999999999;
    return {
        precio: valido ? desde + formato(precio) + ' ' + (unidades[datos?.unidadCobro] || '· unidad por confirmar') : 'Por cotizar',
        total: calculable ? desde + formato(total) : 'Cotización personalizada',
        'precio-nota': calculable
            ? (datos.unidadCobro === 'persona' ? `${personas} ${personas === 1 ? 'persona' : 'personas'} × ${formato(precio)}. ` : 'Importe para el grupo completo. ') + 'Cualquier ajuste se acuerda contigo antes de confirmar.'
            : 'El importe final se acuerda con Mil Cumbres antes de confirmar la visita.',
    };
}

// Conserva los campos existentes al cambiar de fecha o ajustar el tamaño del grupo.
export function iniciarPreReservacion(raiz = document) {
    const cantidad = raiz.getElementById('numero_personas');
    const programacion = raiz.getElementById('programacion_id');
    const contenedor = raiz.getElementById('contenedor-acompanantes');
    if (!cantidad || !programacion || !contenedor) return;

    const grupos = new Map([...contenedor.querySelectorAll('[data-acompanante]')]
        .map((grupo) => [Number(grupo.dataset.acompanante), grupo]));
    const campos = {
        nombre: 'Nombre completo', edad: 'Edad al realizar la visita',
    };

    function crearAcompanante(indice) {
        const grupo = raiz.createElement('fieldset');
        grupo.className = 'reserva-acompanante';
        grupo.dataset.acompanante = String(indice);
        const leyenda = raiz.createElement('legend');
        leyenda.textContent = `Acompañante ${indice + 1}`;
        grupo.appendChild(leyenda);

        for (const [nombre, texto] of Object.entries(campos)) {
            const campo = raiz.createElement('div');
            campo.className = 'reserva-campo';
            const etiqueta = raiz.createElement('label');
            const entrada = raiz.createElement('input');
            entrada.id = `acompanante-${indice}-${nombre}`;
            entrada.name = `acompanantes[${indice}][${nombre}]`;
            entrada.type = nombre === 'edad' ? 'number' : 'text';
            entrada.required = true;
            if (nombre === 'edad') { entrada.min = '0'; entrada.max = '120'; entrada.step = '1'; entrada.inputMode = 'numeric'; entrada.placeholder = 'Años cumplidos'; }
            else { entrada.maxLength = 120; entrada.placeholder = 'Nombre y apellidos'; }
            etiqueta.htmlFor = entrada.id;
            etiqueta.textContent = texto + (nombre === 'apellido_materno' ? ' (opcional)' : '');
            campo.appendChild(etiqueta);
            campo.appendChild(entrada);
            grupo.appendChild(campo);
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
        const aviso = raiz.getElementById('reserva-sin-acompanantes');
        if (aviso) aviso.hidden = necesarios > 0;
        if (typeof CustomEvent !== 'undefined') contenedor.dispatchEvent?.(new CustomEvent('reserva:acompanantes', { bubbles: true, detail: contenedor }));

        // El resumen usa únicamente los datos de la opción seleccionada y nunca modifica los campos.
        const opcion = programacion.selectedOptions[0];
        const resumen = {
            nombre: opcion?.dataset.experiencia || 'Tu próxima salida empieza aquí.',
            fecha: opcion?.dataset.fecha || 'Elige una fecha en el formulario',
            region: opcion?.dataset.region || 'Por seleccionar',
            cupos: cupos ? `${cupos} ${cupos === 1 ? 'lugar disponible' : 'lugares disponibles'}` : 'Consulta los lugares al elegir tu salida',
            ...resumirTarifa(opcion?.dataset, total),
        };
        for (const [clave, valor] of Object.entries(resumen)) {
            const destino = raiz.querySelector?.(`[data-reserva-${clave}]`);
            if (destino) destino.textContent = valor;
        }
    }

    cantidad.addEventListener('input', actualizarAcompanantes);
    programacion.addEventListener('change', actualizarAcompanantes);
    actualizarAcompanantes();
}

if (typeof document !== 'undefined') iniciarPreReservacion();
