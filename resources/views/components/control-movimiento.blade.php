<div class="control-movimiento" data-control-movimiento hidden>
    <label><x-icono nombre="hoja" /><span>Animaciones</span>
        <select data-nivel-movimiento aria-label="Intensidad de las animaciones">
            <option value="completas">Completas</option>
            <option value="suaves">Suaves</option>
            <option value="sin">Desactivadas</option>
        </select>
    </label>
    @if(!request()->is('administracion*', 'pagos*'))
        <button class="ambiente-control" type="button" data-sonidos-bosque aria-pressed="false" title="Activa o apaga todo el audio: botones y ambiente suave de naturaleza.">Sonidos: apagados</button>
        <button class="ambiente-control ambiente-cursor" type="button" data-cursor-bosque aria-pressed="true">Cursor de hoja</button>
        <button class="repetir-bosque" type="button" data-repetir-bosque>Volver a abrir el bosque <span aria-hidden="true">↗</span></button>
        <span class="ambiente-estado" data-estado-ambiente role="status" aria-live="polite"></span>
    @endif
    <span class="movimiento-estado" data-estado-movimiento role="status" aria-live="polite"></span>
</div>
