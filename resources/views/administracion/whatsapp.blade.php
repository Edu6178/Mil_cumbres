@extends('administracion.layout')
@section('titulo', 'Conexión de WhatsApp')
@section('contenido')
@php
    $lista = app(\App\Services\Contenido\WhatsAppPreReserva::class)->configurado();
@endphp
<header class="adm-encabezado"><div><p class="adm-antetitulo">Mensajes de pre-reservación</p><h1>Conexión de WhatsApp</h1><p>El servidor envía el aviso automáticamente al registrar una solicitud nueva con consentimiento.</p></div><span class="adm-insignia {{ $lista ? 'adm-insignia-verde' : '' }}">{{ $lista ? 'Datos de API completos' : 'API pendiente de configurar' }}</span></header>
<div class="adm-roles-resumen">
<section class="adm-panel adm-whatsapp-panel"><h2>Pruebas con tu WhatsApp personal</h2><p>Tu número personal puede recibir las pruebas. Meta proporciona un remitente de prueba para la API; añade tu teléfono como destinatario en su panel. La web enviará el aviso al guardar la solicitud, sin abrir WhatsApp.</p><a class="adm-enlace" href="https://developers.facebook.com/apps/" target="_blank" rel="noopener noreferrer">Abrir el panel de Meta</a></section>
<section class="adm-panel adm-whatsapp-panel"><h2>Envío automático</h2><p>Requiere un número de WhatsApp Business conectado a Meta, su token, el identificador del teléfono y una plantilla de utilidad aprobada. Completar estos datos no verifica la entrega: el resultado se registra al enviar una solicitud nueva con consentimiento.</p><p>Solo se intenta una vez para cada solicitud nueva. No se recorren ni se reenvían las pre-reservaciones anteriores.</p></section>
</div>
<section class="adm-panel adm-whatsapp-panel"><h2>Configuración en el VPS</h2><p>El responsable del servidor debe completar estas variables en su archivo <code>.env</code>. El token permanece en el servidor y no se muestra en este panel.</p>
<pre class="adm-configuracion-ejemplo">WHATSAPP_API_HABILITADA=true
WHATSAPP_TOKEN=TOKEN_DE_META
WHATSAPP_TELEFONO_ID=ID_DEL_TELEFONO_BUSINESS
WHATSAPP_API_VERSION=v27.0
WHATSAPP_PLANTILLA=NOMBRE_DE_PLANTILLA_APROBADA
WHATSAPP_IDIOMA=es_MX</pre>
<p>Después de guardar: <code>php artisan config:cache</code>. <code>APP_URL</code> debe ser la dirección pública HTTPS del sitio.</p>
<h3>Contenido esperado de la plantilla</h3>
@verbatim<p>Hola {{1}}, recibimos tu pre-reserva {{2}} para {{3}}, el {{4}}. Grupo: {{5}} personas. Está pendiente de confirmación por Mil Cumbres. Consulta los detalles en el botón.</p>@endverbatim
<p>Las cinco variables son nombre, folio, experiencia, fecha y tamaño del grupo, en ese orden. Añade un botón URL «Ver pre-reserva» con la dirección <code>{{ rtrim(config('app.url'), '/') }}/@{{1}}</code>; su variable recibe la ruta firmada del resumen. El idioma debe coincidir con el aprobado en Meta.</p>
<a class="adm-enlace" href="https://www.postman.com/meta/whatsapp-business-platform/request/lwtlz1k/send-message-template-interactive" target="_blank" rel="noopener noreferrer">Documentación oficial de Meta</a>
</section>
@endsection
