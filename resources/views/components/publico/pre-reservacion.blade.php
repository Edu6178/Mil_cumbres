@props(['programaciones', 'disponibilidadInterrumpida' => false])
@php
    // El resumen toma la misma programación que el formulario, sin inventar cupos ni tarifas.
    $seleccion = old('programacion_id', is_scalar(request('programacion')) ? request('programacion') : null);
    $salidaElegida = $programaciones->firstWhere('id', $seleccion);
    $personasPrecio = is_numeric(old('numero_personas', 1)) ? max(1, (int) old('numero_personas', 1)) : 1;
    $tarifaElegida = \App\Services\Finanzas\TarifaReserva::calcular($salidaElegida?->servicioRegion?->servicio, min($personasPrecio, $salidaElegida?->cupos_disponibles ?? 1));
    $ultimoAcuse = session('ultima_pre_reserva');
    $acuseDisponible = is_array($ultimoAcuse) && ($ultimoAcuse['vence'] ?? 0) > now()->timestamp;
@endphp
<section class="formulario-prereserva" id="pre-reservacion" aria-labelledby="titulo-reservacion">
    <div class="reserva-encabezado">
        <div><p class="antetitulo"><span></span> Planea tu visita</p><h2 id="titulo-reservacion">El primer paso de tu<br><em>próxima experiencia.</em></h2></div>
        <p>Elige una salida y cuéntanos quiénes te acompañan.<br>Podrás solicitar tu lugar sin crear una cuenta.</p>
    </div>

    @if(session('success'))
        <div class="reserva-aviso reserva-aviso-correcto" role="status">
            <x-icono nombre="correcto" /><div><strong>{{ session('success') }}</strong>
            @if(session('folio'))<p>Tu folio de solicitud es <strong>{{ session('folio') }}</strong>. Consérvalo para dar seguimiento.</p>@endif</div>
        </div>
    @endif
    @if($acuseDisponible)
        <div class="reserva-comprobante" aria-labelledby="titulo-comprobante">
            <div><p class="numero-seccion">Tu última pre-reservación</p><h3 id="titulo-comprobante">Conserva el inicio de tu próxima experiencia.</h3><p>Folio <strong>{{ $ultimoAcuse['folio'] }}</strong>. Descarga el resumen con los datos de tu solicitud y de tu grupo.</p><small>El enlace estará disponible durante 24 horas. La confirmación se acuerda después con Mil Cumbres.</small></div>
            <a class="boton boton-bosque" href="{{ $ultimoAcuse['pdf'] }}" download rel="noreferrer">Descargar mi pre-reserva <x-icono nombre="descargar" /></a>
        </div>
    @endif
    @if($acuseDisponible && in_array($ultimoAcuse['whatsapp_estado'] ?? '', ['aceptado', 'fallido', 'no_configurado', 'enviando'], true))
        <div class="reserva-aviso {{ ($ultimoAcuse['whatsapp_estado'] ?? '') === 'aceptado' ? 'reserva-aviso-correcto' : '' }}" role="status">
            <x-icono nombre="informacion" /><div>
            @if($ultimoAcuse['whatsapp_estado'] === 'aceptado')<strong>WhatsApp aceptó el envío a tu teléfono.</strong><p>El aviso se envió automáticamente desde Mil Cumbres. No necesitas abrir un chat ni enviar el mensaje. La entrega depende de WhatsApp y de la disponibilidad del destinatario.</p>
            @elseif($ultimoAcuse['whatsapp_estado'] === 'enviando')<strong>Tu pre-reserva está guardada.</strong><p>El resultado del aviso automático por WhatsApp todavía no está confirmado. Conserva tu comprobante.</p>
            @else<strong>Tu pre-reserva está guardada.</strong><p>No se pudo enviar el aviso automático por WhatsApp. Puedes descargar tu comprobante; el equipo conserva tu solicitud.</p>@endif
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="reserva-aviso reserva-aviso-error" role="alert">
            <x-icono nombre="informacion" /><div><strong>Falta revisar algunos datos</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif

    @if($disponibilidadInterrumpida)
        <div class="reserva-estado" role="status"><x-icono nombre="calendario" /><h3>No pudimos consultar las fechas</h3><p>Vuelve a intentarlo más tarde. Mientras tanto, puedes explorar las experiencias.</p></div>
    @elseif($programaciones->isNotEmpty())
        <div class="reserva-distribucion">
            <form class="reserva-formulario" action="{{ route('pre-reservaciones.guardar') }}" method="POST">
                @csrf
                <fieldset class="reserva-seccion">
                    <legend><span class="reserva-numero" aria-hidden="true">01</span> Tu experiencia</legend>
                    <p class="reserva-introduccion">Encuentra una fecha y el tamaño de tu grupo.</p>
                    <div class="reserva-campos">
                        <div class="reserva-campo reserva-ancho-completo">
                            <label for="programacion_id">Experiencia y fecha</label>
                            <select id="programacion_id" name="programacion_id" required
                                aria-invalid="{{ $errors->has('programacion_id') ? 'true' : 'false' }}"
                                @error('programacion_id') aria-describedby="programacion-error" @enderror>
                                <option value="">Selecciona tu próxima salida</option>
                                @foreach($programaciones as $programacion)
                                    @php
                                        $servicioPrecio = $programacion->servicioRegion?->servicio;
                                    @endphp
                                    <option value="{{ $programacion->id }}" data-cupos="{{ $programacion->cupos_disponibles }}"
                                        data-precio-centavos="{{ $servicioPrecio?->precio_base !== null ? \App\Services\Finanzas\Dinero::centavos($servicioPrecio->precio_base) : '' }}"
                                        data-unidad-cobro="{{ $servicioPrecio?->unidad_cobro }}" data-tipo-precio="{{ $servicioPrecio?->tipo_precio }}" data-moneda="{{ $servicioPrecio?->moneda ?? 'MXN' }}"
                                        data-experiencia="{{ $programacion->servicioRegion?->servicio?->nombre_servicio ?? 'Experiencia' }}"
                                        data-fecha="{{ \Carbon\Carbon::parse($programacion->fecha_inicio)->format('d/m/Y · H:i') }} hrs"
                                        data-region="{{ $programacion->servicioRegion?->region?->nombre_region }}"
                                        @selected($seleccion == $programacion->id)>
                                        {{ $programacion->servicioRegion?->servicio?->nombre_servicio ?? 'Experiencia' }} · {{ \Carbon\Carbon::parse($programacion->fecha_inicio)->format('d/m/Y') }} · {{ $programacion->cupos_disponibles }} cupos
                                    </option>
                                @endforeach
                            </select>
                            @error('programacion_id')<p class="reserva-error" id="programacion-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="reserva-campo">
                            <label for="numero_personas">Número de personas</label>
                            <div class="reserva-cantidad" data-control-cantidad>
                            <button type="button" data-cantidad-cambio="-1" aria-label="Quitar una persona" disabled>−</button>
                            <output id="cantidad-visible" role="spinbutton" tabindex="0" aria-label="Número de personas" aria-valuemin="1" aria-valuenow="{{ $personasPrecio }}">{{ $personasPrecio }}</output>
                            <button type="button" data-cantidad-cambio="1" aria-label="Agregar una persona" disabled>+</button>
                            </div>
                            <input type="hidden" id="numero_personas" name="numero_personas" min="1" disabled autocomplete="off"
                                max="{{ $programaciones->max('cupos_disponibles') }}" value="{{ is_scalar(old('numero_personas', 1)) ? old('numero_personas', 1) : 1 }}"
                                aria-describedby="cantidad-ayuda" aria-invalid="{{ $errors->has('numero_personas') ? 'true' : 'false' }}">
                            <noscript><label for="cantidad-sin-js">Cantidad de visitantes</label><input type="number" id="cantidad-sin-js" name="numero_personas" min="1" max="{{ $programaciones->max('cupos_disponibles') }}" value="{{ $personasPrecio }}" required autocomplete="off"></noscript>
                            <p class="{{ $errors->has('numero_personas') ? 'reserva-error' : 'reserva-ayuda' }}" id="cantidad-ayuda">{{ $errors->first('numero_personas') ?: 'Inclúyete a ti y a tus acompañantes.' }}</p>
                        </div>
                        <div class="reserva-campo">
                            <label for="tipo_grupo">Tipo de grupo <span>Opcional</span></label>
                            <select id="tipo_grupo" name="tipo_grupo">
                                <option value="">¿Con quién vienes?</option>
                                @foreach(['Familiar', 'Familia con niños', 'Pareja', 'Amigos', 'Escolar', 'Empresarial', 'Otro'] as $tipoGrupo)
                                    <option value="{{ $tipoGrupo }}" @selected(old('tipo_grupo') === $tipoGrupo)>{{ $tipoGrupo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="reserva-seccion">
                    <legend><span class="reserva-numero" aria-hidden="true">02</span> Tus datos de contacto</legend>
                    <p class="reserva-introduccion">Serás la persona responsable de la solicitud.</p>
                    <div class="reserva-campos">
                        <x-publico.campo-reserva nombre="nombre" etiqueta="Nombre completo" obligatorio autocomplete="name" maxlength="120" placeholder="Tu nombre y apellidos" />
                        <x-publico.campo-reserva nombre="telefono" etiqueta="Teléfono" tipo="tel" obligatorio autocomplete="tel" inputmode="tel" minlength="10" maxlength="20" placeholder="10 dígitos" />
                        <x-publico.campo-reserva nombre="edad" etiqueta="Tu edad al realizar la visita" tipo="number" obligatorio min="0" max="120" step="1" inputmode="numeric" autocomplete="off" placeholder="Años cumplidos" />
                    </div>
                </fieldset>

                <fieldset class="reserva-seccion">
                    <legend><span class="reserva-numero" aria-hidden="true">03</span> Quienes te acompañan</legend>
                    <p class="reserva-introduccion">Al aumentar el grupo aparecerá un espacio para cada acompañante.</p>
                    @php
                        // Recupera los datos después de una validación sin crear registros adicionales.
                        $anteriores = old('acompanantes', []);
                        $anteriores = is_array($anteriores) ? array_slice(array_values($anteriores), 0, max(0, (int) $programaciones->max('cupos_disponibles') - 1)) : [];
                    @endphp
                    <p class="reserva-sin-acompanantes" id="reserva-sin-acompanantes" @if(count($anteriores)) hidden @endif><x-icono nombre="usuario" /><span>¿Viajas por tu cuenta? Con tus datos es suficiente.</span></p>
                    <div id="contenedor-acompanantes">
                        @foreach($anteriores as $indice => $datosAcompanante)
                            <x-publico.acompanante :indice="$indice" :datos="is_array($datosAcompanante) ? $datosAcompanante : []" />
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="reserva-seccion">
                    <legend><span class="reserva-numero" aria-hidden="true">04</span> Cuéntanos sobre tu visita</legend>
                    <p class="reserva-introduccion">Estos detalles nos ayudan a conocer mejor a tu grupo.</p>
                    <div class="reserva-campos">
                        <div class="reserva-campo"><label for="proposito_visita">Propósito de la visita <span>Opcional</span></label><textarea id="proposito_visita" name="proposito_visita" rows="4" placeholder="Descansar, aprender, celebrar…">{{ is_string(old('proposito_visita')) ? old('proposito_visita') : '' }}</textarea></div>
                        <div class="reserva-campo"><label for="personalizacion">Comentarios para el recorrido <span>Opcional</span></label><textarea id="personalizacion" name="personalizacion" rows="4" placeholder="¿Hay algo más que debamos considerar?">{{ is_string(old('personalizacion')) ? old('personalizacion') : '' }}</textarea></div>
                    </div>
                </fieldset>
                @if(app(\App\Services\Contenido\WhatsAppPreReserva::class)->configurado())<label class="reserva-consentimiento"><input type="checkbox" name="whatsapp_acepta" value="1" @checked(old('whatsapp_acepta'))><span>Quiero recibir automáticamente el resumen de esta pre-reservación por WhatsApp en el teléfono que indiqué.</span></label>@endif
                <div class="reserva-enviar"><p><x-icono nombre="informacion" /><span>Enviar la solicitud genera una <strong>pre-reservación</strong>. La confirmación se acuerda después con Mil Cumbres.</span></p><button type="submit" class="boton boton-bosque">Enviar mi solicitud <x-icono nombre="flecha" /></button></div>
            </form>

            <aside class="reserva-resumen" aria-label="Resumen de la salida">
                <div class="reserva-resumen-icono"><x-icono nombre="ruta" /></div>
                <p class="numero-seccion">Un lugar para reconectar</p>
                <h3 data-reserva-nombre>{{ $salidaElegida?->servicioRegion?->servicio?->nombre_servicio ?? 'Tu próxima salida empieza aquí.' }}</h3>
                <dl>
                    <div><dt><x-icono nombre="calendario" /> Fecha y horario</dt><dd data-reserva-fecha>{{ $salidaElegida ? \Carbon\Carbon::parse($salidaElegida->fecha_inicio)->format('d/m/Y · H:i').' hrs' : 'Elige una fecha en el formulario' }}</dd></div>
                    <div><dt><x-icono nombre="paisaje" /> Territorio</dt><dd data-reserva-region>{{ $salidaElegida?->servicioRegion?->region?->nombre_region ?? 'Por seleccionar' }}</dd></div>
                    <div><dt><x-icono nombre="personas" /> Disponibilidad</dt><dd data-reserva-cupos>{{ $salidaElegida ? $salidaElegida->cupos_disponibles.' lugares disponibles' : 'Consulta los lugares al elegir tu salida' }}</dd></div>
                    <div><dt><x-icono nombre="pago" /> Tarifa publicada</dt><dd data-reserva-precio>@if($tarifaElegida['precio'] !== null){{ $tarifaElegida['tipo'] === 'desde' ? 'Desde ' : '' }}${{ \App\Services\Finanzas\Dinero::mostrar(\App\Services\Finanzas\Dinero::centavos($tarifaElegida['precio'])) }} {{ $tarifaElegida['moneda'] }} {{ \App\Services\Finanzas\TarifaReserva::UNIDADES[$tarifaElegida['unidad']] ?? '· unidad por confirmar' }}@else Por cotizar @endif</dd></div>
                    <div class="reserva-total"><dt>Total del grupo</dt><dd data-reserva-total role="status" aria-live="polite">{{ $tarifaElegida['importe'] !== null ? ($tarifaElegida['tipo'] === 'desde' ? 'Desde ' : '').'$'.\App\Services\Finanzas\Dinero::mostrar(\App\Services\Finanzas\Dinero::centavos($tarifaElegida['importe'])).' '.$tarifaElegida['moneda'] : 'Cotización personalizada' }}</dd></div>
                </dl>
                <p class="reserva-precio-nota" data-reserva-precio-nota>El importe publicado acompaña tu solicitud. Cualquier ajuste se acuerda contigo antes de confirmar.</p>
                <div class="reserva-resumen-pie"><x-icono nombre="hoja" /><p>Menos prisa.<br><em>Más conexión.</em></p></div>
                <p class="reserva-resumen-nota">Los lugares se comprueban nuevamente al enviar tu solicitud.</p>
            </aside>
        </div>
    @else
        <div class="reserva-estado"><x-icono nombre="calendario" /><h3>Estamos preparando las próximas experiencias</h3><p>Por el momento no hay fechas disponibles para solicitar una pre-reservación.</p></div>
    @endif
</section>
