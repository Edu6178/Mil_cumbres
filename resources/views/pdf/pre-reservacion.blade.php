<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pre-reserva {{ $solicitud->folio }} | Mil Cumbres</title>
    <style>
        @page { margin: 28pt 30pt 42pt; }
        body { margin: 0; color: #263d33; font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; line-height: 1.4; }
        h1, h2, p { margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        .cabecera { margin-bottom: 10pt; }
        .simbolo { width: 36pt; height: 36pt; }
        .marca { font-family: 'DejaVu Serif', serif; font-size: 22pt; color: #193e34; }
        .lema { color: #657168; font-size: 6.5pt; letter-spacing: 1pt; text-transform: uppercase; }
        .folio { text-align: right; }
        .folio strong { display: block; color: #193e34; font-size: 12pt; }
        .etiqueta { color: #657168; font-size: 6.5pt; letter-spacing: .6pt; text-transform: uppercase; }
        .bienvenida { padding: 10pt 18pt; background: #193e34; color: #f8f7f2; border-radius: 5px; }
        .bienvenida .etiqueta { color: #e5d2ab; }
        h1 { margin-top: 5pt; font-family: 'DejaVu Serif', serif; font-size: 21pt; font-weight: normal; line-height: 1.2; }
        .bienvenida p { margin-top: 6pt; font-size: 8pt; color: #e2e7d9; }
        .resumen { margin-top: 10pt; background: #efede4; }
        .resumen td { width: 33%; padding: 7pt 12pt; vertical-align: top; }
        .resumen strong { display: block; margin-top: 3pt; font-size: 9pt; }
        .seccion { margin-top: 10pt; padding-bottom: 5pt; border-bottom: 1px solid #d9ddd2; font-size: 10pt; color: #193e34; page-break-after: avoid; }
        .numero { margin-right: 7pt; color: #b95634; font-size: 8pt; }
        .preguntas { margin-top: 4pt; table-layout: fixed; }
        .preguntas td { padding: 4pt 8pt 4pt 0; vertical-align: top; overflow-wrap: break-word; word-wrap: break-word; }
        .pregunta { display: block; margin-bottom: 2pt; color: #657168; font-size: 7.5pt; }
        .respuesta { font-size: 9pt; }
        .detalle { margin-top: 8pt; page-break-inside: avoid; }
        .texto-largo { margin-top: 3pt; font-size: 9pt; overflow-wrap: break-word; word-wrap: break-word; }
        .participantes { margin-top: 8pt; table-layout: fixed; }
        .participantes thead { display: table-header-group; }
        .participantes th { padding: 6pt 7pt; background: #efede4; text-align: left; font-size: 7.5pt; font-weight: normal; color: #193e34; }
        .participantes td { padding: 5pt 7pt; border-bottom: 1px solid #e5e6dd; vertical-align: top; word-wrap: break-word; }
        .participantes tr { page-break-inside: avoid; }
        .sin-datos { margin-top: 8pt; color: #657168; }
        .nota { margin-top: 10pt; padding: 8pt 12pt; background: #f3f4ed; border-left: 2pt solid #b95634; font-size: 8pt; page-break-inside: avoid; }
        .nota strong { color: #193e34; }
        footer { position: fixed; bottom: -26pt; left: 0; right: 0; padding-top: 7pt; border-top: 1px solid #d9ddd2; color: #657168; font-size: 6.5pt; }
        .pagina { float: right; }
        .pagina:after { content: counter(page); }
    </style>
</head>
<body>
    @php
        // El acuse reproduce los datos guardados; las edades corresponden al día del recorrido.
        $responsable = $solicitud->cliente?->persona;
        $programacion = $solicitud->programacion;
        $fechaExperiencia = $programacion?->fecha_inicio ?? $solicitud->fecha_solicitud;
        $nombreCompleto = fn ($persona) => trim(implode(' ', array_filter([$persona?->nombre, $persona?->apellido_paterno, $persona?->apellido_materno])));
        $edad = fn ($persona) => $persona?->fecha_nacimiento ? (int) $persona->fecha_nacimiento->diffInYears($fechaExperiencia) : null;
    @endphp
    <footer><span>Mil Cumbres · Turismo regenerativo | {{ $solicitud->folio }}</span><span class="pagina">Página </span></footer>
    <table class="cabecera"><tr>
        <td style="width: 55px"><img class="simbolo" src="{{ $simbolo }}" alt="Mil Cumbres"></td>
        <td><div class="marca">Mil Cumbres</div><div class="lema">Turismo regenerativo</div></td>
        <td class="folio"><span class="etiqueta">Folio de solicitud</span><strong>{{ $solicitud->folio }}</strong><span>{{ $solicitud->fecha_solicitud->format('d/m/Y · H:i') }} hrs</span></td>
    </tr></table>

    <div class="bienvenida"><span class="etiqueta">Naturaleza. Comunidad. Propósito.</span><h1>Acuse de pre-reservación</h1><p>Tu próxima experiencia comienza aquí. Conserva este documento para dar seguimiento a tu solicitud.</p></div>
    <table class="resumen"><tr>
        <td><span class="etiqueta">Solicitud registrada</span><strong>{{ $solicitud->fecha_solicitud->format('d/m/Y') }}</strong></td>
        <td><span class="etiqueta">Personas en el grupo</span><strong>{{ $solicitud->numero_personas }} {{ $solicitud->numero_personas === 1 ? 'persona' : 'personas' }}</strong></td>
        <td><span class="etiqueta">Estado al descargar</span><strong>{{ $solicitud->estado?->nombre_estado ?? 'Pre-reserva' }}</strong></td>
    </tr></table>

    <h2 class="seccion"><span class="numero">01</span> Tu experiencia</h2>
    <table class="preguntas"><tr>
        <td colspan="2"><span class="pregunta">Experiencia solicitada</span><span class="respuesta">{{ $programacion?->servicioRegion?->servicio?->nombre_servicio ?? 'Sin información disponible' }}</span></td>
        <td><span class="pregunta">Tipo de grupo</span><span class="respuesta">{{ $solicitud->tipo_grupo ?: 'No especificado' }}</span></td>
    </tr><tr>
        <td><span class="pregunta">Fecha y hora de inicio</span><span class="respuesta">{{ $programacion?->fecha_inicio?->format('d/m/Y · H:i') ?? 'Por confirmar' }} hrs</span></td>
        <td><span class="pregunta">Fecha y hora de término</span><span class="respuesta">{{ $programacion?->fecha_fin?->format('d/m/Y · H:i') ?? 'Por confirmar' }} hrs</span></td>
        <td><span class="pregunta">Territorio</span><span class="respuesta">{{ $programacion?->servicioRegion?->region?->nombre_region ?? 'Por confirmar' }}</span></td>
    </tr></table>

    <h2 class="seccion"><span class="numero">02</span> Datos de la persona responsable</h2>
    <table class="preguntas"><tr>
        <td><span class="pregunta">Nombre(s)</span><span class="respuesta">{{ $responsable?->nombre ?? 'Sin información' }}</span></td>
        <td><span class="pregunta">Apellido paterno</span><span class="respuesta">{{ $responsable?->apellido_paterno ?? 'Sin información' }}</span></td>
        <td><span class="pregunta">Apellido materno</span><span class="respuesta">{{ $responsable?->apellido_materno ?: 'No proporcionado' }}</span></td>
    </tr><tr>
        <td><span class="pregunta">Teléfono</span><span class="respuesta">{{ $responsable?->telefono ?: 'No proporcionado' }}</span></td>
        <td><span class="pregunta">Correo electrónico (opcional)</span><span class="respuesta">{{ $responsable?->correo ?: 'No proporcionado' }}</span></td>
        <td><span class="pregunta">Nacimiento / edad al recorrido</span><span class="respuesta">{{ $responsable?->fecha_nacimiento?->format('d/m/Y') ?? 'No proporcionada' }}{{ $edad($responsable) !== null ? ' · '.$edad($responsable).' años' : '' }}</span></td>
    </tr></table>

    <h2 class="seccion"><span class="numero">03</span> Quienes te acompañan</h2>
    @if($solicitud->acompanantes->isEmpty())
        <p class="sin-datos">Sin acompañantes registrados. La persona responsable está incluida en el total del grupo.</p>
    @else
        <table class="participantes"><thead><tr><th style="width: 7%">Núm.</th><th style="width: 48%">Nombre completo</th><th style="width: 27%">Fecha de nacimiento</th><th style="width: 18%">Edad al recorrido</th></tr></thead><tbody>
            @foreach($solicitud->acompanantes as $acompanante)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $nombreCompleto($acompanante) }}</td><td>{{ $acompanante->fecha_nacimiento?->format('d/m/Y') ?? 'No proporcionada' }}</td><td>{{ $edad($acompanante) !== null ? $edad($acompanante).' años' : 'Sin información' }}</td></tr>
            @endforeach
        </tbody></table>
    @endif

    <h2 class="seccion"><span class="numero">04</span> Cuéntanos sobre tu visita</h2>
    <div class="detalle"><span class="pregunta">Propósito de la visita</span></div>
    @foreach(explode("\n", $solicitud->proposito_visita ?: 'No especificado') as $parrafo)<p class="texto-largo">{{ $parrafo }}</p>@endforeach
    <div class="detalle"><span class="pregunta">Comentarios para el recorrido</span></div>
    @foreach(explode("\n", $solicitud->personalizacion ?: 'Sin comentarios adicionales') as $parrafo)<p class="texto-largo">{{ $parrafo }}</p>@endforeach

    <div class="nota"><p><strong>¿Qué sigue?</strong> Mil Cumbres revisará tu solicitud y acordará la confirmación. Este acuse no es un comprobante de pago ni confirma lugares. Conserva tu folio para dar seguimiento.</p></div>
</body>
</html>
