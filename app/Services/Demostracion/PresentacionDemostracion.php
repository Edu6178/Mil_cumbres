<?php

namespace App\Services\Demostracion;

use App\Models\Programacion;
use App\Models\Servicio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PresentacionDemostracion
{
    public function servicios(RegistroDemostracion $registro): Collection
    {
        $datos = $registro->leer();
        $programaciones = Programacion::with(['servicioRegion.region', 'reservaciones.estado'])
            ->whereIn('estado', ['programada', 'activo'])->where('fecha_inicio', '>', now())
            ->whereHas('servicioRegion.region', fn ($consulta) => $consulta->where('disponibilidad', true))
            ->orderBy('fecha_inicio')->get()->groupBy('servicioRegion.servicio_id');

        return Servicio::with('categoria')->where('estado_servicio', 'activo')->orderBy('id')->get()->map(function ($servicio) use ($datos, $programaciones) {
            $clave = array_search($servicio->id, $datos['ids']['servicios'], false);
            $ficha = $clave !== false ? config('demostracion.servicios.'.$clave) : [];
            $fechas = $programaciones->get($servicio->id, collect());
            $proxima = $fechas->first(fn ($fecha) => $fecha->cupos_disponibles > 0);

            return [...$servicio->toArray(),
                'linea_clave' => $servicio->categoria?->nombre_categoria === 'Senderismo' ? 'senderismo' : '',
                'linea_nombre' => $servicio->categoria?->nombre_categoria ?? 'Experiencia',
                'fotografia' => isset($ficha['fotografia']) ? $this->fotografia($ficha['fotografia']) : null,
                'demostracion' => $clave !== false, 'unidad_cobro' => $clave !== false ? 'MXN por persona' : null,
                'nombre_region' => $fechas->pluck('servicioRegion.region.nombre_region')->unique()->join(' · ') ?: 'Territorio por confirmar',
                'disponibilidad' => $proxima ? 'Próxima salida: '.$proxima->fecha_inicio->format('d/m') : ($fechas->isEmpty() ? 'Fechas por publicar' : 'Sin cupos por ahora'),
                'url_detalle' => route('publico.experiencia.mostrar', $servicio->id),
                'url_reservacion' => route('inicio', $proxima ? ['programacion' => $proxima->id] : []).'#pre-reservacion',
                'programaciones' => $fechas->map(fn ($fecha) => [
                    'fecha' => $fecha->fecha_inicio->copy()->locale('es')->isoFormat('dddd D [de] MMMM'),
                    'horario' => $fecha->fecha_inicio->format('H:i').' a '.$fecha->fecha_fin->format('H:i'),
                    'cupos_disponibles' => $fecha->cupos_disponibles,
                ])->values()->all(),
            ];
        });
    }

    public function perfiles(RegistroDemostracion $registro): Collection
    {
        $ids = $registro->leer()['ids']['personal'];
        // La tabla no tiene autorización pública: solo se muestran los perfiles ficticios registrados en este lote.
        return DB::table('personal')->join('personas', 'personas.id', '=', 'personal.persona_id')
            ->whereIn('personal.id', array_values($ids))->where('tipo_rol', 'guia')->where('estado_laboral', 'activo')
            ->select('personal.id', 'personas.nombre', 'apellido_paterno', 'apellido_materno', 'formacion', 'talentos')
            ->orderBy('personal.id')->get()->map(function ($guia) use ($ids) {
                $clave = array_search($guia->id, $ids, false);
                $ficha = config('demostracion.guias.'.$clave);

                return [
                    'id' => $guia->id, 'nombre' => trim($guia->nombre.' '.$guia->apellido_paterno.' '.$guia->apellido_materno),
                    'formacion' => $guia->formacion, 'talentos' => $guia->talentos, 'publicado' => true, 'demostracion' => true,
                    'especialidad' => $ficha['especialidad'], 'presentacion' => $ficha['presentacion'],
                    'fotografia' => $this->fotografia($ficha['fotografia']), 'url_perfil' => route('publico.guia.mostrar', $guia->id),
                ];
            });
    }

    private function fotografia(string $clave): string
    {
        return asset('imagenes/demostracion/'.config('demostracion.fotografias.'.$clave.'.archivo'));
    }
}
