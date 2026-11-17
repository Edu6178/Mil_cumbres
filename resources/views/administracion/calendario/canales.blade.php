@extends('administracion.layout')
@section('titulo', 'Canales externos')

@section('contenido')
    <a class="adm-enlace adm-volver" href="{{ route('administracion.calendario.indice') }}">
        <x-icono nombre="atras" /> Volver al calendario
    </a>

    <div class="adm-encabezado">
        <div>
            <p class="adm-antetitulo">Integración de plataformas aliadas</p>
            <h1>Canales <em>externos.</em></h1>
            <p>Incorpora reservaciones provenientes de Booking, TripAdvisor u otros portales descontando el cupo global.</p>
        </div>
    </div>

    @if(session('correcto'))
        <div class="adm-aviso adm-aviso-correcto">
            <x-icono nombre="correcto" />
            <div><strong>Sincronizado.</strong><p>{{ session('correcto') }}</p></div>
        </div>
    @endif

    @if($errors->any())
        <div class="adm-aviso adm-aviso-atencion">
            <x-icono nombre="informacion" />
            <div><strong>No se pudo incorporar la reserva:</strong><p>{{ $errors->first() }}</p></div>
        </div>
    @endif

    <div class="adm-dos-columnas">
        <section class="adm-panel adm-revelar">
            <div class="adm-panel-titulo">
                <div>
                    <h2>Reservaciones externas incorporadas</h2>
                    <p>{{ $reservasExternas->total() }} {{ $reservasExternas->total() == 1 ? 'registro externo' : 'registros externos' }}</p>
                </div>
                <x-icono nombre="brujula" />
            </div>

            @if($reservasExternas->isEmpty())
                <x-administracion.vacio
                    titulo="Sin reservas externas registradas"
                    descripcion="Cuando incorpores una reservación de Booking o TripAdvisor aparecerá aquí con su referencia."
                    icono="brujula"
                />
            @else
                <div class="adm-tabla-contenedor">
                    <table class="adm-tabla">
                        <thead>
                            <tr>
                                <th>Origen / Referencia</th>
                                <th>Experiencia y fecha</th>
                                <th>Titular</th>
                                <th>Personas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservasExternas as $res)
                                <tr>
                                    <td><strong>{{ $res->personalizacion }}</strong></td>
                                    <td>
                                        <div>{{ $res->programacion?->servicioRegion?->servicio?->nombre_servicio }}</div>
                                        <small>{{ $res->programacion?->fecha_inicio?->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>{{ $res->cliente?->persona?->nombre }} {{ $res->cliente?->persona?->apellido_paterno }}</td>
                                    <td><strong>{{ $res->numero_personas }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-administracion.paginacion :registros="$reservasExternas" />
            @endif
        </section>

        <aside>
            <section class="adm-panel adm-panel-formulario">
                <div class="adm-panel-titulo">
                    <div>
                        <h2>Incorporar reserva externa</h2>
                        <p>Valida cupo global y evita referencias duplicadas.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('administracion.canales.guardar') }}" class="adm-campos" data-formulario-administracion>
                    @csrf

                    <div class="adm-campo">
                        <label for="canal_origen">Plataforma de origen <span>*</span></label>
                        <select id="canal_origen" name="canal_origen" required>
                            <option value="Booking" @selected(old('canal_origen') === 'Booking')>Booking</option>
                            <option value="TripAdvisor" @selected(old('canal_origen') === 'TripAdvisor')>TripAdvisor</option>
                            <option value="Airbnb" @selected(old('canal_origen') === 'Airbnb')>Airbnb</option>
                            <option value="Otro" @selected(old('canal_origen') === 'Otro')>Otro canal aliado</option>
                        </select>
                    </div>

                    <div class="adm-campo">
                        <label for="referencia_externa">Folio o referencia externa <span>*</span></label>
                        <input id="referencia_externa" name="referencia_externa" value="{{ old('referencia_externa') }}" required placeholder="Ej. BK-9823412">
                    </div>

                    <div class="adm-campo">
                        <label for="programacion_id">Fecha programada <span>*</span></label>
                        <select id="programacion_id" name="programacion_id" required>
                            <option value="">Selecciona fecha con cupo</option>
                            @foreach($programacionesDisponibles as $prog)
                                <option value="{{ $prog->id }}" @selected((string)old('programacion_id') === (string)$prog->id)>
                                    {{ $prog->servicioRegion?->servicio?->nombre_servicio }} — {{ $prog->fecha_inicio->format('d/m/Y H:i') }} ({{ $prog->cupos_disponibles }} disp.)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="adm-campo">
                        <label for="numero_personas">Número de personas <span>*</span></label>
                        <input id="numero_personas" name="numero_personas" type="number" min="1" value="{{ old('numero_personas', 1) }}" required>
                    </div>

                    <div class="adm-campo">
                        <label for="nombre">Nombre del titular <span>*</span></label>
                        <input id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    </div>

                    <div class="adm-campo">
                        <label for="apellido_paterno">Apellido paterno <span>*</span></label>
                        <input id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                    </div>

                    <div class="adm-campo">
                        <label for="telefono">Teléfono de contacto</label>
                        <input id="telefono" name="telefono" value="{{ old('telefono') }}">
                    </div>

                    <div class="adm-campo">
                        <label for="monto_total">Importe reportado (opcional)</label>
                        <div class="adm-campo-moneda">
                            <span>$</span>
                            <input id="monto_total" name="monto_total" type="number" step="0.01" min="0" value="{{ old('monto_total') }}" placeholder="0.00">
                        </div>
                    </div>

                    <button type="submit" class="adm-boton adm-boton-principal" @disabled($programacionesDisponibles->isEmpty())>
                        <x-icono nombre="correcto" />
                        <span>Incorporar y descontar cupo</span>
                    </button>
                </form>
            </section>
        </aside>
    </div>
@endsection