@extends('administracion.layout')
@section('titulo', $servicio->exists ? 'Editar servicio' : 'Crear servicio')

@section('contenido')
    <a class="adm-enlace adm-volver" href="{{ route('administracion.servicios.indice') }}"><x-icono nombre="atras" /> Volver al catálogo</a>
    <div class="adm-encabezado"><div><p class="adm-antetitulo">Los detalles hacen la experiencia</p><h1>{{ $servicio->exists ? 'Una experiencia' : 'Una nueva' }} <em>{{ $servicio->exists ? 'en evolución.' : 'experiencia.' }}</em></h1><p>{{ $servicio->exists ? 'Actualiza la información sin alterar el historial de reservaciones.' : 'Define la información que dará forma a tu servicio.' }}</p></div></div>
    @if($categorias->isEmpty())<div class="adm-aviso adm-aviso-atencion"><x-icono nombre="informacion" /><div><strong>Primero necesitas una categoría.</strong><p>Organiza tu catálogo creando la categoría de este servicio.</p><a class="adm-enlace" href="{{ route('administracion.categorias.indice') }}">Crear una categoría <x-icono nombre="flecha" /></a></div></div>@endif
    <form method="POST" action="{{ $servicio->exists ? route('administracion.servicios.actualizar', $servicio) : route('administracion.servicios.guardar') }}" class="adm-formulario-servicio" data-formulario-administracion>
        @csrf @if($servicio->exists) @method('PUT') @endif
        <div class="adm-formulario-principal adm-revelar">
            <section class="adm-panel adm-panel-formulario"><div class="adm-panel-titulo"><div><h2>Lo esencial</h2><p>Los campos con asterisco son obligatorios.</p></div><x-icono nombre="brujula" /></div><div class="adm-campos">
                <div class="adm-campo"><label for="nombre_servicio">Nombre del servicio <span>*</span></label><input id="nombre_servicio" name="nombre_servicio" value="{{ old('nombre_servicio', $servicio->nombre_servicio) }}" maxlength="200" required placeholder="Nombre de la experiencia" @error('nombre_servicio') aria-invalid="true" @enderror></div>
                <div class="adm-campo"><label for="categoria_id">Categoría <span>*</span></label><select id="categoria_id" name="categoria_id" required><option value="">Selecciona una categoría</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}" @selected((string)old('categoria_id', $servicio->categoria_id) === (string)$categoria->id)>{{ $categoria->nombre_categoria }}</option>@endforeach</select><small>Usa las categorías de tu catálogo. <a href="{{ route('administracion.categorias.indice') }}">Gestionar categorías</a></small></div>
                <div class="adm-campo"><label for="descripcion">Descripción</label><textarea id="descripcion" name="descripcion" rows="5" maxlength="10000" placeholder="¿Qué hace especial a esta experiencia?">{{ old('descripcion', $servicio->descripcion) }}</textarea></div>
            </div></section>
            <section class="adm-panel adm-panel-formulario"><div class="adm-panel-titulo"><div><h2>El recorrido</h2><p>Describe las actividades y el orden en que se realizarán.</p></div><x-icono nombre="ruta" /></div><div class="adm-campos"><div class="adm-campo"><label for="itinerario">Itinerario</label><textarea id="itinerario" name="itinerario" rows="7" maxlength="20000" placeholder="Punto de encuentro, actividades y cierre del recorrido…">{{ old('itinerario', $servicio->itinerario) }}</textarea><small>Puedes separar cada actividad en una línea.</small></div></div></section>
        </div>
        <aside class="adm-formulario-aside">
            <section class="adm-panel adm-panel-formulario"><div class="adm-panel-titulo"><div><h2>Precio y disponibilidad</h2><p>La tarifa base de este servicio.</p></div></div><div class="adm-campos">
                <div class="adm-campo"><label for="precio_base">Precio base <span>*</span></label><div class="adm-campo-moneda"><span>$</span><input id="precio_base" name="precio_base" type="number" value="{{ old('precio_base', $servicio->precio_base) }}" min="0" max="9999999999.99" step="0.01" required placeholder="0.00" inputmode="decimal"></div><small>Hasta dos decimales. No modifica importes ya pactados.</small></div>
                <div class="adm-campo"><label for="duracion_estimada">Duración estimada</label><input id="duracion_estimada" name="duracion_estimada" type="number" value="{{ old('duracion_estimada', $servicio->duracion_estimada) }}" min="1" max="2147483647" step="1" placeholder="Minutos" inputmode="numeric"><small>Duración expresada en minutos.</small></div>
                <div class="adm-campo"><label for="estado_servicio">Estado <span>*</span></label><select id="estado_servicio" name="estado_servicio" required><option value="activo" @selected(old('estado_servicio', $servicio->estado_servicio) === 'activo')>Activo</option><option value="oculto" @selected(old('estado_servicio', $servicio->estado_servicio) === 'oculto')>Oculto</option></select><small>Ocultar conserva sus programaciones y reservaciones.</small></div>
            </div></section>
            <div class="adm-ayuda"><x-icono nombre="hoja" /><p>Una descripción clara ayuda a elegir mejor y prepara a las personas para el recorrido.</p></div>
            <div class="adm-guardar"><button type="submit" class="adm-boton adm-boton-principal" @disabled($categorias->isEmpty())><x-icono nombre="correcto" /><span>{{ $servicio->exists ? 'Guardar cambios' : 'Crear servicio' }}</span></button><a href="{{ route('administracion.servicios.indice') }}">Cancelar</a></div>
        </aside>
    </form>
@endsection
