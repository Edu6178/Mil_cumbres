import { nivelMovimiento } from './movimiento.js';

// Las fechas proceden del servidor. El calendario nunca genera salidas ni habilita bloqueos.
document.querySelectorAll('[data-calendario-visitas]').forEach((raiz) => {
    const datos = raiz.querySelector('[data-fechas-visitas]');
    if (!datos) return;
    const fechas = JSON.parse(datos.textContent);
    const hoy = new Date();
    let mes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    const primero = mes.getTime();
    const ultimaFecha = fechas.reduce((ultima, fecha) => fecha.fin > ultima ? fecha.fin : ultima, fechas[0].dia);
    const [anioFin, mesFin] = ultimaFecha.split('-').map(Number);
    const ultimo = new Date(anioFin, mesFin - 1, 1).getTime();
    const cuadricula = raiz.querySelector('[data-dias-visitas]');
    const anterior = raiz.querySelector('[data-mes-anterior]');
    const siguiente = raiz.querySelector('[data-mes-siguiente]');
    const clave = (fecha) => `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}-${String(fecha.getDate()).padStart(2, '0')}`;
    let elegido = '';
    const elemento = (tipo, texto, clase) => {
        const nodo = document.createElement(tipo);
        nodo.textContent = texto;
        if (clase) nodo.className = clase;
        return nodo;
    };

    function mostrarDia(dia, salidas, boton) {
        elegido = clave(dia);
        cuadricula.querySelectorAll('button').forEach((nodo) => nodo.setAttribute('aria-pressed', String(nodo === boton)));
        raiz.querySelector('[data-dia-elegido]').textContent = dia.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long' });
        const detalle = raiz.querySelector('[data-salidas-del-dia]');
        detalle.replaceChildren();
        salidas.forEach((salida) => {
            const ficha = elemento('article', '', 'visitas-salida');
            ficha.append(elemento('strong', salida.nombre), elemento('p', `${salida.hora} h · ${salida.region}`));
            if (salida.estado === 'disponible' && salida.dia === elegido) {
                ficha.append(elemento('span', `${salida.cupos} lugares disponibles`, 'visitas-estado'));
                const enlace = elemento('a', 'Elegir esta salida →', 'enlace-texto');
                enlace.href = `/?programacion=${salida.id}#pre-reservacion`;
                enlace.addEventListener('click', (evento) => {
                    const seleccion = document.getElementById('programacion_id');
                    if (!seleccion?.querySelector(`option[value="${salida.id}"]`)) return;
                    evento.preventDefault();
                    seleccion.value = String(salida.id);
                    seleccion.dispatchEvent(new Event('change', { bubbles: true }));
                    document.getElementById('pre-reservacion').scrollIntoView({ behavior: nivelMovimiento() === 'sin' ? 'auto' : 'smooth' });
                    seleccion.focus({ preventScroll: true });
                });
                ficha.append(enlace);
            } else {
                ficha.append(elemento('span', salida.estado === 'bloqueada' ? 'Fecha bloqueada · No admite solicitudes' : salida.estado === 'completa' ? 'Cupo completo' : 'Actividad en curso · Consulta el día de inicio', 'visitas-estado'));
            }
            detalle.append(ficha);
        });
    }

    function dibujarMes() {
        raiz.querySelector('[data-titulo-mes]').textContent = mes.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
        anterior.disabled = mes.getTime() <= primero;
        siguiente.disabled = mes.getTime() >= ultimo;
        cuadricula.replaceChildren();
        const huecos = (mes.getDay() + 6) % 7;
        for (let i = 0; i < huecos; i++) cuadricula.append(elemento('span', ''));
        const total = new Date(mes.getFullYear(), mes.getMonth() + 1, 0).getDate();
        for (let numero = 1; numero <= total; numero++) {
            const dia = new Date(mes.getFullYear(), mes.getMonth(), numero);
            const fecha = clave(dia);
            const salidas = fechas.filter((salida) => salida.dia <= fecha && salida.fin >= fecha);
            const boton = elemento('button', String(numero));
            boton.type = 'button';
            boton.disabled = salidas.length === 0;
            const disponible = salidas.some(salida => salida.estado === 'disponible' && salida.dia === fecha);
            const bloqueada = salidas.some(salida => salida.estado === 'bloqueada');
            if (salidas.length) boton.className = disponible ? 'es-disponible' : bloqueada ? 'es-bloqueada' : 'es-completa';
            boton.setAttribute('aria-label', `${dia.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' })}: ${disponible ? 'salidas disponibles' : bloqueada ? 'fecha bloqueada' : salidas.length ? 'sin lugares disponibles' : 'sin salidas publicadas'}`);
            boton.setAttribute('aria-pressed', String(fecha === elegido));
            boton.addEventListener('click', () => mostrarDia(dia, salidas, boton));
            cuadricula.append(boton);
        }
    }
    anterior.addEventListener('click', () => { mes = new Date(mes.getFullYear(), mes.getMonth() - 1, 1); dibujarMes(); });
    siguiente.addEventListener('click', () => { mes = new Date(mes.getFullYear(), mes.getMonth() + 1, 1); dibujarMes(); });
    dibujarMes();
    raiz.querySelector('[data-calendario-interactivo]').hidden = false;
});
