@extends('administracion.layout')
@section('titulo', 'Servicios y precios')

@section('contenido')
    <div class="adm-encabezado"><div><p class="adm-antetitulo">El punto de partida de cada viaje</p><h1>Servicios <em>y precios.</em></h1><p>Gestiona las experiencias, sus itinerarios y la tarifa base.</p></div><a class="adm-boton adm-boton-principal" href="{{ route('administracion.servicios.crear') }}"><x-icono nombre="mas" /> Crear servicio</a></div>
    <div class="adm-resumen-linea"><span><strong>{{ $total }}</strong> servicios registrados</span><span><i class="adm-punto-verde"></i><strong>{{ $activos }}</strong> activos</span><span>Ocultar un servicio conserva su historial.</span></div>
    <section class="adm-panel adm-revelar">
        <form method="GET" action="{{ route('administracion.servicios.indice') }}" class="adm-filtros">
            <div class="adm-campo adm-busqueda"><label for="buscar">Buscar servicio</label><div><x-icono nombre="buscar" /><input type="search" id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre de la experiencia" maxlength="100"></div></div>
            <div class="adm-campo"><label for="categoria">Categoría</label><select id="categoria" name="categoria"><option value="">Todas las categorías</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}" @selected((string)request('categoria') === (string)$categoria->id)>{{ $categoria->nombre_categoria }}</option>@endforeach</select></div>
            <div class="adm-campo"><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos los estados</option><option value="activo" @selected(request('estado') === 'activo')>Activo</option><option value="oculto" @selected(request('estado') === 'oculto')>Oculto</option></select></div>
            <button type="submit" class="adm-boton adm-boton-contorno">Filtrar</button>@if(request()->filled('buscar') || request()->filled('categoria') || request()->filled('estado'))<a class="adm-enlace" href="{{ route('administracion.servicios.indice') }}">Limpiar</a>@endif
        </form>
        @if($servicios->isEmpty())
            <x-administracion.vacio :titulo="$total ? 'No encontramos servicios con estos filtros' : 'Un catálogo lleno de posibilidades'" :descripcion="$total ? 'Prueba otro nombre, categoría o estado para consultar tus servicios.' : 'Aún no hay servicios registrados. Crea el primero con su descripción, precio base e itinerario.'">
                @if(!$total)<a class="adm-boton adm-boton-contorno" href="{{ route('administracion.servicios.crear') }}"><x-icono nombre="mas" /> Crear servicio</a>@endif
            </x-administracion.vacio>
        @else
            <div class="adm-tabla-contenedor"><table class="adm-tabla"><thead><tr><th>Servicio</th><th>Categoría</th><th>Precio base</th><th>Duración</th><th>Estado</th><th class="adm-alinear-derecha">Acciones</th></tr></thead><tbody>
                @foreach($servicios as $servicio)
                    <tr><td><a class="adm-servicio-nombre" href="{{ route('administracion.servicios.editar', $servicio) }}">{{ $servicio->nombre_servicio }}</a>@if($servicio->created_at?->gt(now()->subMinutes(10)))<small class="adm-reciente">Recientemente creado</small>@endif</td><td>{{ $servicio->categoria->nombre_categoria }}</td><td class="adm-precio">${{ number_format((float)$servicio->precio_base, 2) }}</td><td>{{ $servicio->duracion_estimada ? $servicio->duracion_estimada.' min' : 'Sin definir' }}</td><td><span class="adm-insignia {{ $servicio->estado_servicio === 'activo' ? 'adm-insignia-verde' : '' }}">{{ $servicio->estado_servicio === 'activo' ? 'Activo' : ucfirst($servicio->estado_servicio) }}</span></td><td><div class="adm-acciones-fila"><a href="{{ route('administracion.servicios.editar', $servicio) }}" aria-label="Editar {{ $servicio->nombre_servicio }}">Editar</a><form method="POST" action="{{ route('administracion.servicios.visibilidad', $servicio) }}">@csrf @method('PATCH')<input type="hidden" name="estado_servicio" value="{{ $servicio->estado_servicio === 'activo' ? 'oculto' : 'activo' }}"><button type="submit">{{ $servicio->estado_servicio === 'activo' ? 'Ocultar' : 'Activar' }}</button></form></div></td></tr>
                @endforeach
            </tbody></table></div>
            <x-administracion.paginacion :registros="$servicios" />
        @endif
    </section>
@endsection
