@props(['fechas'])
<section class="visitas-calendario" id="calendario-visitas" data-calendario-visitas aria-labelledby="titulo-calendario-visitas">
    <div class="reserva-encabezado"><div><p class="antetitulo"><span></span> Encuentra tu momento</p><h2 id="titulo-calendario-visitas">Un día para<br><em>reconectar.</em></h2></div><p>Consulta las salidas y sus lugares disponibles. Las fechas bloqueadas se muestran para que puedas planear tu visita.</p></div>
    @if($fechas->isEmpty())
        <div class="reserva-estado"><x-icono nombre="calendario" /><h3>Próximas fechas por publicar</h3><p>Las salidas disponibles y los bloqueos aparecerán aquí cuando se programen.</p></div>
    @else
        <script type="application/json" data-fechas-visitas>@json($fechas)</script>
        <div class="visitas-distribucion" data-calendario-interactivo hidden>
            <div class="visitas-mes">
                <div class="visitas-mes-cabecera"><button type="button" data-mes-anterior aria-label="Mes anterior"><x-icono nombre="atras" /></button><h3 data-titulo-mes aria-live="polite"></h3><button type="button" data-mes-siguiente aria-label="Mes siguiente"><x-icono nombre="flecha" /></button></div>
                <div class="visitas-semana" aria-hidden="true">@foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dia)<span>{{ $dia }}</span>@endforeach</div>
                <div class="visitas-dias" data-dias-visitas></div>
                <div class="visitas-leyenda"><span><i class="es-disponible"></i> Disponible</span><span><i class="es-bloqueada"></i> Bloqueada</span><span><i class="es-completa"></i> Cupo completo</span></div>
            </div>
            <aside class="visitas-detalle"><p class="numero-seccion">Tu próxima experiencia</p><h3 data-dia-elegido>Elige un día del calendario.</h3><div data-salidas-del-dia aria-live="polite"><p>Los días marcados muestran las salidas publicadas. Selecciona uno para conocer sus horarios y disponibilidad.</p></div></aside>
        </div>
        <details class="visitas-listado"><summary>Ver todas las fechas publicadas</summary><ul>
            @foreach($fechas as $fecha)<li><span><strong>{{ $fecha['nombre'] }}</strong><small>{{ \Carbon\Carbon::parse($fecha['dia'])->format('d/m/Y') }} · {{ $fecha['hora'] }} · {{ $fecha['region'] }}</small></span>
                @if($fecha['estado'] === 'disponible')<a href="{{ route('inicio', ['programacion' => $fecha['id']]) }}#pre-reservacion">{{ $fecha['cupos'] }} lugares · Solicitar</a>@else<span class="visitas-estado">{{ $fecha['estado'] === 'bloqueada' ? 'Fecha bloqueada' : 'Cupo completo' }}</span>@endif
            </li>@endforeach
        </ul></details>
    @endif
</section>
