// Composición original generada aquí: brisa, aves lejanas y acordes lentos.
// No usa grabaciones, canciones externas ni peticiones a servicios de audio.
export function crearAmbienteNaturaleza(contexto, destino) {
    const volumenFondo = .01;
    const acordes = [
        [146.83, 220, 293.66, 329.63],
        [130.81, 196, 293.66, 329.63],
        [123.47, 185, 246.94, 293.66],
        [146.83, 220, 277.18, 329.63],
    ];
    let capa, viento, acorde = 0, relojAcorde, relojAve;
    const fuentes = new Set();

    function conectar(fuente, nodos) {
        const entrada = { fuente, nodos };
        fuentes.add(entrada);
        fuente.onended = () => {
            fuentes.delete(entrada);
            fuente.disconnect(); nodos.forEach(nodo => nodo.disconnect());
        };
    }

    function prepararBrisa() {
        if (viento) return;
        viento = contexto.createBuffer(2, contexto.sampleRate * 4, contexto.sampleRate);
        for (let canal = 0; canal < 2; canal++) {
            const datos = viento.getChannelData(canal); let anterior = 0;
            for (let i = 0; i < datos.length; i++) {
                anterior = anterior * .98 + (Math.random() * 2 - 1) * .02;
                // Los extremos suaves evitan un clic al repetir la brisa.
                const borde = Math.min(1, i / (contexto.sampleRate * .15), (datos.length - 1 - i) / (contexto.sampleRate * .15));
                datos[i] = anterior * 5 * borde;
            }
        }
    }

    function tocarAcorde() {
        if (!capa || contexto.state !== 'running') return;
        const inicio = contexto.currentTime;
        acordes[acorde++ % acordes.length].forEach((frecuencia, indice) => {
            const nota = contexto.createOscillator(), envolvente = contexto.createGain();
            nota.type = 'sine'; nota.frequency.value = frecuencia; nota.detune.value = indice % 2 ? 2 : -2;
            envolvente.gain.setValueAtTime(0, inicio);
            envolvente.gain.linearRampToValueAtTime(.14, inicio + 4);
            envolvente.gain.setValueAtTime(.14, inicio + 10);
            envolvente.gain.linearRampToValueAtTime(0, inicio + 16);
            nota.connect(envolvente).connect(capa); conectar(nota, [envolvente]);
            nota.start(inicio); nota.stop(inicio + 16);
        });
        relojAcorde = setTimeout(tocarAcorde, 14000);
    }

    function aveLejana() {
        if (!capa || contexto.state !== 'running') return;
        const inicio = contexto.currentTime;
        for (const desplazamiento of [0, .22]) {
            const ave = contexto.createOscillator(), envolvente = contexto.createGain();
            const t = inicio + desplazamiento, frecuencia = 1650 + Math.random() * 250;
            ave.type = 'sine'; ave.frequency.setValueAtTime(frecuencia, t);
            ave.frequency.linearRampToValueAtTime(frecuencia * 1.35, t + .06);
            ave.frequency.exponentialRampToValueAtTime(frecuencia * .95, t + .16);
            envolvente.gain.setValueAtTime(0, t);
            envolvente.gain.linearRampToValueAtTime(.08, t + .018);
            envolvente.gain.exponentialRampToValueAtTime(.0001, t + .18);
            ave.connect(envolvente).connect(capa); conectar(ave, [envolvente]);
            ave.start(t); ave.stop(t + .19);
        }
        relojAve = setTimeout(aveLejana, 18000 + Math.random() * 12000);
    }

    function iniciar() {
        if (capa || contexto.state !== 'running') return;
        prepararBrisa();
        capa = contexto.createGain(); capa.connect(destino);
        capa.gain.setValueAtTime(0, contexto.currentTime);
        capa.gain.linearRampToValueAtTime(volumenFondo, contexto.currentTime + 1.5);
        const brisa = contexto.createBufferSource(), filtro = contexto.createBiquadFilter();
        brisa.buffer = viento; brisa.loop = true;
        filtro.type = 'lowpass'; filtro.frequency.value = 650; filtro.Q.value = .3;
        brisa.connect(filtro).connect(capa); conectar(brisa, [filtro]); brisa.start();
        tocarAcorde(); relojAve = setTimeout(aveLejana, 9000);
    }

    function detener() {
        clearTimeout(relojAcorde); clearTimeout(relojAve);
        if (capa) { capa.gain.cancelScheduledValues(contexto.currentTime); capa.gain.setValueAtTime(0, contexto.currentTime); capa.disconnect(); capa = null; }
        for (const { fuente, nodos } of fuentes) {
            fuente.onended = null;
            try { fuente.stop(); } catch { /* La nota puede haber terminado. */ }
            fuente.disconnect(); nodos.forEach(nodo => nodo.disconnect());
        }
        fuentes.clear();
    }

    function atenuar() {
        if (!capa || capa.gain.value < volumenFondo * .5) return;
        const ahora = contexto.currentTime;
        capa.gain.cancelScheduledValues(ahora);
        capa.gain.setValueAtTime(capa.gain.value, ahora);
        capa.gain.linearRampToValueAtTime(volumenFondo * .25, ahora + .006);
        capa.gain.linearRampToValueAtTime(volumenFondo, ahora + .4);
    }

    return { iniciar, detener, atenuar };
}
