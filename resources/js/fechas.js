import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';

// Rechaza fechas imposibles en lugar de convertir, por ejemplo, el 31 de febrero a marzo.
function analizarFecha(texto) {
    const iso = /^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{1,2}):(\d{2}))?$/.exec(texto);
    const legible = /^(\d{1,2})\/(\d{1,2})\/(\d{4})(?:\s*[·,]?\s+(\d{1,2}):(\d{2}))?$/.exec(texto);
    if (!iso && !legible) return undefined;
    const [, anio, mes, dia, hora = '0', minuto = '0'] = iso || [null, legible[3], legible[2], legible[1], legible[4], legible[5]];
    const fecha = new Date(Number(anio), Number(mes) - 1, Number(dia), Number(hora), Number(minuto));
    return fecha.getFullYear() === Number(anio) && fecha.getMonth() === Number(mes) - 1
        && fecha.getDate() === Number(dia) && fecha.getHours() === Number(hora)
        && fecha.getMinutes() === Number(minuto) ? fecha : undefined;
}

// El campo original conserva el formato que valida Laravel; la fecha visible se lee en español.
export function iniciarFechas(raiz = document) {
    raiz.querySelectorAll('input[type="date"], input[type="datetime-local"]').forEach((entrada) => {
        if (entrada._flatpickr) return;
        const conHora = entrada.type === 'datetime-local';
        const nacimiento = entrada.name.includes('fecha_nacimiento');
        const identificador = entrada.id;
        const etiqueta = document.querySelector(`label[for="${identificador}"]`)?.textContent.replace(/\s+/g, ' ').trim() || 'Selecciona una fecha';
        const anioActual = new Date().getFullYear();
        let selectorAnio;
        const sincronizarAnio = (_, __, calendario) => {
            if (selectorAnio) selectorAnio.value = String(calendario.currentYear);
        };

        flatpickr(entrada, {
            locale: { ...Spanish, firstDayOfWeek: 1 },
            disableMobile: true,
            enableTime: conHora,
            time_24hr: true,
            minuteIncrement: 1,
            dateFormat: conHora ? 'Y-m-d\\TH:i' : 'Y-m-d',
            altInput: true,
            altFormat: conHora ? 'd/m/Y · H:i' : 'd/m/Y',
            ariaDateFormat: 'l j \\d\\e F \\d\\e Y',
            allowInput: true,
            parseDate: analizarFecha,
            minDate: entrada.min || undefined,
            maxDate: entrada.max || undefined,
            defaultHour: 8,
            position: 'auto right',
            onReady: (_, __, calendario) => {
                const visible = calendario.altInput;
                visible.classList.add('mc-fecha-entrada');
                entrada.id = `${identificador}-valor`;
                visible.id = identificador;
                visible.placeholder = conHora ? 'dd/mm/aaaa · hh:mm' : 'dd/mm/aaaa';
                visible.setAttribute('autocomplete', 'off');
                visible.setAttribute('aria-haspopup', 'dialog');
                visible.setAttribute('aria-expanded', 'false');
                for (const atributo of ['aria-invalid', 'aria-describedby', 'aria-label']) {
                    if (entrada.hasAttribute(atributo)) visible.setAttribute(atributo, entrada.getAttribute(atributo));
                }
                const panel = calendario.calendarContainer;
                panel.classList.add('mc-calendario');
                panel.id = `${identificador}-calendario`;
                panel.setAttribute('role', 'dialog');
                panel.setAttribute('aria-label', etiqueta);
                visible.setAttribute('aria-controls', panel.id);
                const titulo = document.createElement('div');
                titulo.className = 'mc-calendario-titulo';
                titulo.textContent = nacimiento ? 'Un poco sobre ti' : 'Encuentra tu fecha';
                panel.prepend(titulo);

                // El año se elige de un salto, sin recorrer décadas mes a mes.
                selectorAnio = document.createElement('select');
                selectorAnio.className = 'mc-calendario-anio';
                selectorAnio.setAttribute('aria-label', 'Año');
                const limite = nacimiento ? anioActual : Math.max(anioActual + 10, calendario.currentYear);
                for (let anio = limite; anio >= Math.min(1900, calendario.currentYear); anio--) {
                    selectorAnio.add(new Option(String(anio), String(anio)));
                }
                calendario.currentYearElement.parentElement.after(selectorAnio);
                selectorAnio.addEventListener('change', () => calendario.changeYear(Number(selectorAnio.value)));
                sincronizarAnio([], '', calendario);
                calendario.monthsDropdownContainer?.setAttribute('aria-label', 'Mes');
                calendario.prevMonthNav.setAttribute('aria-label', 'Mes anterior');
                calendario.nextMonthNav.setAttribute('aria-label', 'Mes siguiente');
                if (conHora) {
                    calendario.hourElement.setAttribute('aria-label', 'Hora, de 0 a 23');
                    calendario.minuteElement.setAttribute('aria-label', 'Minutos');
                    const nota = document.createElement('p');
                    nota.className = 'mc-calendario-nota';
                    nota.textContent = 'Hora del recorrido · formato de 24 horas';
                    calendario.timeContainer.before(nota);
                }
                const pie = document.createElement('div');
                pie.className = 'mc-calendario-pie';
                const boton = (texto, accion, clase = '') => {
                    const elemento = document.createElement('button');
                    elemento.type = 'button';
                    elemento.textContent = texto;
                    elemento.className = clase;
                    elemento.addEventListener('click', accion);
                    pie.append(elemento);
                };
                boton('Hoy', () => {
                    const hoy = new Date();
                    if (conHora) hoy.setHours(8, 0, 0, 0);
                    calendario.setDate(hoy, true);
                    visible.focus({ preventScroll: true });
                    calendario.close();
                });
                boton('Borrar', () => { calendario.clear(); calendario.close(); });
                if (conHora) boton('Listo', () => calendario.close(), 'mc-calendario-listo');
                panel.append(pie);
            },
            onYearChange: sincronizarAnio,
            onMonthChange: sincronizarAnio,
            onOpen: (_, __, calendario) => {
                sincronizarAnio([], '', calendario);
                calendario.altInput.setAttribute('aria-expanded', 'true');
                // El panel permanece dentro de la ventana, incluso en un teléfono.
                requestAnimationFrame(() => {
                    const panel = calendario.calendarContainer;
                    panel.style.maxHeight = `${innerHeight - 20}px`;
                    panel.style.overflowY = 'auto';
                    const limites = panel.getBoundingClientRect();
                    if (limites.right > innerWidth - 10) panel.style.left = `${Math.max(10, innerWidth - limites.width - 10) + scrollX}px`;
                    else if (limites.left < 10) panel.style.left = `${10 + scrollX}px`;
                    const campo = calendario.altInput.getBoundingClientRect();
                    const alto = Math.min(limites.height, innerHeight - 20);
                    const debajo = campo.bottom + 6;
                    const encima = campo.top - alto - 6;
                    const arriba = debajo + alto <= innerHeight - 10 ? debajo : (encima >= 10 ? encima : Math.max(10, innerHeight - alto - 10));
                    panel.style.top = `${arriba + scrollY}px`;
                });
            },
            onClose: (_, __, calendario) => calendario.altInput.setAttribute('aria-expanded', 'false'),
        });
    });
}

iniciarFechas();

// Los acompañantes se agregan durante la captura y reciben el mismo selector sin perder sus datos.
document.addEventListener('reserva:acompanantes', (evento) => iniciarFechas(evento.detail));

// Se conserva una fecha de cierre ya capturada si el usuario corrige la hora de inicio.
const inicio = document.querySelector('[name="fecha_inicio"]')?._flatpickr;
const cierre = document.querySelector('[name="fecha_fin"]')?._flatpickr;
if (inicio && cierre) {
    const comprobarOrden = () => {
        const incorrecto = inicio.selectedDates[0] && cierre.selectedDates[0]
            && cierre.selectedDates[0] <= inicio.selectedDates[0];
        cierre.altInput.setCustomValidity(incorrecto ? 'El cierre debe ser posterior al inicio.' : '');
    };
    inicio.config.onChange.push(comprobarOrden);
    cierre.config.onChange.push(comprobarOrden);
    comprobarOrden();
}
