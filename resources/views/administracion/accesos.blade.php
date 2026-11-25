@extends('administracion.layout')
@section('titulo', 'Accesos del equipo')
@section('contenido')
<header class="adm-encabezado"><div><p class="adm-antetitulo">Equipo · Roles provisionales</p><h1>Accesos y solicitudes</h1><p>{{ $pendientes }} solicitudes pendientes. Solo el superadministrador puede aprobar cuentas y cambiar permisos.</p></div></header>
<section class="adm-panel adm-accesos-intro">
    <div class="adm-roles-resumen">
        <p><strong>Superadministrador</strong> Administra el sistema, las solicitudes y los roles.</p>
        <p><strong>Administrador</strong> Gestiona servicios, fotografías, reservas, agenda y finanzas.</p>
        <p><strong>Empleado / Guía</strong> Consulta su espacio y las actividades vinculadas a su ficha laboral.</p>
    </div>
    <form method="GET" class="adm-filtros"><label>Buscar cuenta<input type="search" name="buscar" value="{{ request('buscar') }}" maxlength="100" placeholder="Nombre o correo"></label><label>Estado<select name="estado"><option value="">Todos</option>@foreach(['pendiente' => 'Pendientes', 'activo' => 'Activos', 'rechazado' => 'Rechazados o desactivados'] as $valor => $nombre)<option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $nombre }}</option>@endforeach</select></label><button type="submit" class="adm-boton adm-boton-contorno">Buscar</button></form>
</section>
<div class="adm-accesos-lista">
@forelse($usuarios as $usuario)
    <article class="adm-panel adm-acceso-cuenta">
        <div><h2>{{ $usuario->name }}</h2><p>{{ $usuario->email }}</p><span class="adm-insignia {{ $usuario->estado_acceso === 'activo' ? 'adm-insignia-verde' : '' }}">{{ $usuario->estado_acceso === 'pendiente' ? 'Solicitud pendiente' : ($usuario->estado_acceso === 'rechazado' ? 'Acceso desactivado' : $usuario->nombreRol()) }}</span>
        @if($usuario->acceso_solicitado_at)<p class="adm-texto-ayuda">Solicitud del {{ $usuario->acceso_solicitado_at->format('d/m/Y H:i') }}</p>@endif
        @if($usuario->motivo_acceso)<p>{{ $usuario->motivo_acceso }}</p>@endif</div>
        <form method="POST" action="{{ route('administracion.accesos.actualizar', $usuario) }}" class="adm-acceso-formulario" data-formulario-administracion>
            @csrf @method('PATCH')
            <label for="rol-{{ $usuario->id }}">Rol<select id="rol-{{ $usuario->id }}" name="rol" required>
            @foreach(\App\Models\User::ROLES as $valor => $nombre)
                @if($usuario->estado_acceso !== 'pendiente' || in_array($valor, ['admin', 'empleado', 'guia'], true))<option value="{{ $valor }}" @selected($usuario->rolActual() === $valor)>{{ $nombre }}</option>@endif
            @endforeach</select></label>
            <label for="personal-{{ $usuario->id }}">Ficha laboral <small>Opcional</small><select id="personal-{{ $usuario->id }}" name="personal_id"><option value="">Sin vincular</option>@foreach($personal as $ficha)@if(!isset($vinculados[$ficha->id]) || $vinculados[$ficha->id] === $usuario->id)<option value="{{ $ficha->id }}" @selected($usuario->personal_id === $ficha->id)>{{ $ficha->nombre }} {{ $ficha->apellido_paterno }} · {{ $ficha->tipo_rol }}</option>@endif @endforeach</select></label>
            <label for="estado-{{ $usuario->id }}">Acceso<select id="estado-{{ $usuario->id }}" name="estado_acceso"><option value="activo" @selected($usuario->estado_acceso === 'activo')>Aprobado y activo</option><option value="pendiente" @selected($usuario->estado_acceso === 'pendiente')>Pendiente</option><option value="rechazado" @selected($usuario->estado_acceso === 'rechazado')>Rechazado / desactivado</option></select></label>
            <button type="submit" class="adm-boton adm-boton-principal">{{ $usuario->estado_acceso === 'pendiente' ? 'Aplicar decisión' : 'Guardar acceso' }}</button>
        </form>
    </article>
@empty <section class="adm-panel adm-vacio"><h2>No hay cuentas para mostrar</h2><p>Las solicitudes nuevas aparecerán aquí para su revisión.</p></section> @endforelse
</div>
<x-administracion.paginacion :registros="$usuarios" />
@endsection
