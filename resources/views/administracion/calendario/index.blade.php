@extends('administracion.layout')
@section('titulo', 'Calendario y disponibilidad')

@section('contenido')
    <div class="adm-encabezado">
        <div>
            <p class="adm-antetitulo">Control de salidas y territorios</p>
            <h1>Calendario y <em>disponibilidad.</em></h1>
            <p>Programa fechas, define cupos y bloquea horarios o territorios para mantener el orden operativo.</p>
        </div>
        <div>
            <a class="adm-boton adm-boton-principal" href="{{ route('administracion.canales.indice') }}">
                <x-icono nombre="brujula" />
                <span>Canales externos </span>
            </a>
        </div>
    </div>

    @if(session('correcto'))
        <div class="adm-aviso adm-aviso-correcto">
            <x-icono nombre="correcto" />
            <div><strong>Listo.</strong><p>{{ session('correcto') }}</p></div>
        </div>
    @endif

    @if($errors->any())
        <div class="adm-aviso adm-aviso-atencion">
            <x-icono nombre="informacion" />
            <div>
                <strong>Revisa los datos capturados:</strong>
                <p>{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <div class="adm-dos-columnas adm-calendario-distribucion">
        {{-- COLUMNA PRINCIPAL: LISTADO DE PROGRAMACIONES Y BLOQUEOS POR TERRITORIO --}}
        <div class="adm-formulario-principal adm-revelar">
            {{-- BLOQUE 1: TABLA DE PROGRAMACIONES (FECHAS Y CUPOS) --}}
            <section class="adm-panel">
                <div class="adm-panel-titulo">
                    <div>
                        <h2>La agenda de tus recorridos</h2>
                        <p>{{ $programaciones->total() }} {{ $programaciones->total() == 1 ? 'fecha registrada' : 'fechas registradas' }}</p>
                    </div>
                    <x-icono nombre="calendario" />
                </div>

                @if($programaciones->isEmpty())
                    <x-administracion.vacio
                        titulo="Aún no hay salidas programadas"
                        descripcion="Crea tu primera programación en el panel lateral para habilitar fechas en el portal público."
                        icono="calendario"
                    />
                @else
                    <div class="adm-calendario-leyenda"><span><i></i> Fechas y horarios</span><span><x-icono nombre="personas" /> Cupos actualizados con cada solicitud</span></div>
                    <div class="adm-calendario-agenda">
                        @foreach($programaciones as $prog)
                            @php
                                $regionBloqueada = !($prog->servicioRegion?->region?->disponibilidad ?? true);
                                $bloqueada = $prog->estado === 'bloqueada' || $regionBloqueada;
                                $disponibles = $prog->cupos_disponibles;
                                $ocupados = $prog->cupos_ocupados;
                                $porcentaje = $prog->cupo_maximo > 0 ? min(100, round($ocupados / $prog->cupo_maximo * 100)) : 0;
                            @endphp
                            <article class="adm-salida {{ $bloqueada ? 'adm-salida-bloqueada' : '' }}">
                                <div class="adm-salida-fecha" aria-label="{{ $prog->fecha_inicio->translatedFormat('j \d\e F \d\e Y') }}">
                                    <span>{{ $prog->fecha_inicio->translatedFormat('M') }}</span>
                                    <strong>{{ $prog->fecha_inicio->format('d') }}</strong>
                                    <small>{{ $prog->fecha_inicio->format('Y') }}</small>
                                </div>
                                <div class="adm-salida-contenido">
                                    <div class="adm-salida-titulo">
                                        <h3>{{ $prog->servicioRegion?->servicio?->nombre_servicio ?? 'Servicio' }}</h3>
                                        <span class="adm-salida-estado {{ $bloqueada ? 'es-bloqueada' : '' }}">
                                            {{ $prog->estado === 'cancelada' ? 'Cancelada' : ($bloqueada ? 'Bloqueada' : ($disponibles === 0 ? 'Cupo completo' : 'Programada')) }}
                                        </span>
                                    </div>
                                    <p class="adm-salida-territorio"><x-icono nombre="ruta" /> {{ $prog->servicioRegion?->region?->nombre_region ?? 'Sin región' }} @if($regionBloqueada) · Territorio bloqueado @endif</p>
                                    <div class="adm-salida-detalles">
                                        <p><x-icono nombre="calendario" /><span>{{ $prog->fecha_inicio->translatedFormat('l') }} · <strong>{{ $prog->fecha_inicio->format('H:i') }} — {{ $prog->fecha_fin->format('H:i') }}</strong>@if(!$prog->fecha_inicio->isSameDay($prog->fecha_fin))<small>Termina el {{ $prog->fecha_fin->format('d/m/Y') }}</small>@endif</span></p>
                                        <div class="adm-salida-cupo">
                                            <div><strong>{{ $disponibles }} de {{ $prog->cupo_maximo }} disponibles</strong><span>({{ $ocupados }} ocupados)</span></div>
                                            <meter min="0" max="{{ max(1, $prog->cupo_maximo) }}" value="{{ $ocupados }}" aria-label="Lugares ocupados: {{ $ocupados }} de {{ $prog->cupo_maximo }}">{{ $porcentaje }}%</meter>
                                        </div>
                                    </div>
                                    <div class="adm-salida-acciones">
                                        <a href="{{ route('administracion.calendario.editar', $prog) }}"><x-icono nombre="editar" /> Editar salida</a>
                                        <form method="POST" action="{{ route('administracion.calendario.bloquear', $prog) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit">{{ $prog->estado === 'bloqueada' ? 'Habilitar fecha' : 'Bloquear fecha' }}</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <x-administracion.paginacion :registros="$programaciones" />
                @endif
            </section>

            {{-- BLOQUE 2: BLOQUEOS POR TERRITORIO / REGIÓN (RF-10) --}}
            <section class="adm-panel" style="margin-top: 24px;">
                <div class="adm-panel-titulo">
                    <div>
                        <h2>Bloqueo por territorio o región</h2>
                        <p>Suspende temporalmente todas las solicitudes de una zona (ej. veda, clima o mantenimiento de senderos).</p>
                    </div>
                    <x-icono nombre="ruta" />
                </div>

                @if($regiones->isEmpty())
                    <p style="padding: 16px; color: var(--texto-suave);">No hay regiones registradas en el catálogo.</p>
                @else
                    <div class="adm-tabla-contenedor">
                        <table class="adm-tabla">
                            <thead>
                                <tr>
                                    <th>Territorio / Región</th>
                                    <th>Estado operativo</th>
                                    <th class="adm-alinear-derecha">Control</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($regiones as $region)
                                    <tr>
                                        <td>
                                            <strong>{{ $region->nombre_region }}</strong>
                                            @if($region->descripcion)
                                                <small class="adm-reciente">{{ \Illuminate\Support\Str::limit($region->descripcion, 70) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($region->disponibilidad)
                                                <span style="color: #193e34; font-weight: 600;">Activo para reservas</span>
                                            @else
                                                <span style="color: #9d432c; font-weight: 600;">Territorio bloqueado</span>
                                            @endif
                                        </td>
                                        <td class="adm-alinear-derecha">
                                            <form method="POST" action="{{ route('administracion.calendario.region.bloquear', $region) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="adm-enlace" style="background:none; border:0; padding:0; color: {{ $region->disponibilidad ? '#b95634' : '#193e34' }};">
                                                    {{ $region->disponibilidad ? 'Bloquear territorio' : 'Reactivar territorio' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        {{-- COLUMNA LATERAL: FORMULARIO DE CREACIÓN / EDICIÓN DE PROGRAMACIÓN --}}
        <aside>
            <section class="adm-panel adm-panel-formulario adm-calendario-formulario" id="programar-salida">
                <div class="adm-panel-titulo adm-calendario-formulario-titulo">
                    <x-icono nombre="calendario" />
                    <div>
                        <span>El próximo encuentro</span>
                        <h2>{{ $programacionEdicion->exists ? 'Editar programación' : 'Programa una salida' }}</h2>
                        <p>Una experiencia, una fecha, un nuevo camino.</p>
                    </div>
                </div>

                <form method="POST"
                      action="{{ $programacionEdicion->exists ? route('administracion.calendario.actualizar', $programacionEdicion) : route('administracion.calendario.guardar') }}"
                      class="adm-campos"
                      data-formulario-administracion>
                    @csrf
                    @if($programacionEdicion->exists) @method('PUT') @endif

                    <div class="adm-campo">
                        <label for="servicio_reg_id">Experiencia y territorio <span>*</span></label>
                        <select id="servicio_reg_id" name="servicio_reg_id" required>
                            <option value="">Selecciona servicio y región</option>
                            @foreach($serviciosRegiones as $sr)
                                <option value="{{ $sr->id }}" data-cupo="{{ $sr->cupo_maximo }}" @selected((string)old('servicio_reg_id', $programacionEdicion->servicio_reg_id) === (string)$sr->id)>
                                    {{ $sr->servicio?->nombre_servicio }} — {{ $sr->region?->nombre_region }} (Máx. {{ $sr->cupo_maximo }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="adm-campo">
                        <label for="fecha_inicio">Fecha y hora de inicio <span>*</span></label>
                        <input id="fecha_inicio"
                               name="fecha_inicio"
                               type="datetime-local"
                               value="{{ old('fecha_inicio', $programacionEdicion->fecha_inicio?->format('Y-m-d\TH:i')) }}"
                               required>
                        <small>Elige el día y escribe la hora en formato de 24 horas.</small>
                    </div>

                    <div class="adm-campo">
                        <label for="fecha_fin">Fecha y hora de cierre <span>*</span></label>
                        <input id="fecha_fin"
                               name="fecha_fin"
                               type="datetime-local"
                               value="{{ old('fecha_fin', $programacionEdicion->fecha_fin?->format('Y-m-d\TH:i')) }}"
                               required>
                    </div>

                    <div class="adm-campo">
                        <label for="cupo_maximo">Cupo máximo de participantes <span>*</span></label>
                        <input id="cupo_maximo"
                               name="cupo_maximo"
                               type="number"
                               min="1"
                               value="{{ old('cupo_maximo', $programacionEdicion->cupo_maximo) }}"
                               required
                               placeholder="Ej. 15">
                        <small data-cupo-territorial>El límite depende del territorio seleccionado.</small>
                    </div>

                    @if($programacionEdicion->exists)
                        <div class="adm-campo">
                            <label for="estado">Estado de la fecha <span>*</span></label>
                            <select id="estado" name="estado" required>
                                <option value="programada" @selected(old('estado', $programacionEdicion->estado) === 'programada')>Programada (Abierta)</option>
                                <option value="bloqueada" @selected(old('estado', $programacionEdicion->estado) === 'bloqueada')>Bloqueada</option>
                                <option value="cancelada" @selected(old('estado', $programacionEdicion->estado) === 'cancelada')>Cancelada</option>
                            </select>
                        </div>
                    @endif

                    <button type="submit" class="adm-boton adm-boton-principal" @disabled($serviciosRegiones->isEmpty())>
                        <x-icono nombre="correcto" />
                        <span>{{ $programacionEdicion->exists ? 'Guardar cambios' : 'Publicar fecha' }}</span>
                    </button>

                    @if($programacionEdicion->exists)
                        <a class="adm-enlace" href="{{ route('administracion.calendario.indice') }}">Cancelar edición</a>
                    @endif
                </form>
            </section>

            <div class="adm-ayuda">
                <x-icono nombre="informacion" />
                <p>Las fechas bloqueadas o pertenecientes a un territorio deshabilitado dejan de mostrarse en el portal público de inmediato.</p>
            </div>
        </aside>
    </div>
@endsection
