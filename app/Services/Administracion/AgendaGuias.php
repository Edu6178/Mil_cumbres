<?php

namespace App\Services\Administracion;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgendaGuias
{
    private const ESTADOS_SIN_ACTIVIDAD = ['bloqueado', 'bloqueada', 'cancelado', 'cancelada', 'finalizado', 'finalizada'];

    public static function guias(): Builder
    {
        return DB::table('personal')->join('personas', 'personas.id', '=', 'personal.persona_id')
            ->whereIn(DB::raw('LOWER(personal.tipo_rol)'), ['guia', 'guía'])
            ->select('personal.*', 'personas.nombre', 'personas.apellido_paterno', 'personas.apellido_materno');
    }

    public static function programaciones(): Builder
    {
        return DB::table('programaciones')
            ->join('servicio_regiones', 'servicio_regiones.id', '=', 'programaciones.servicio_reg_id')
            ->join('servicios', 'servicios.id', '=', 'servicio_regiones.servicio_id')
            ->join('regiones', 'regiones.id', '=', 'servicio_regiones.region_id')
            ->select('programaciones.*', 'servicios.nombre_servicio', 'regiones.nombre_region')
            ->selectSub(DB::table('asigna_guias')->selectRaw('COUNT(*)')
                ->whereColumn('asigna_guias.programacion_id', 'programaciones.id')->where('disponibilidad', true), 'numero_guias');
    }

    public function asignar(int $programacion, array $identificadores): void
    {
        $identificadores = array_values(array_unique(array_map('intval', $identificadores)));
        sort($identificadores);

        // Se bloquean la programación y el personal para serializar asignaciones concurrentes.
        DB::transaction(function () use ($programacion, $identificadores) {
            $actividad = DB::table('programaciones')->where('id', $programacion)->lockForUpdate()->firstOrFail();
            if (in_array(mb_strtolower($actividad->estado), self::ESTADOS_SIN_ACTIVIDAD, true) || $actividad->fecha_inicio >= $actividad->fecha_fin) {
                throw ValidationException::withMessages(['guias' => 'Esta programación no admite asignaciones. Revisa su estado y sus fechas.']);
            }

            $actuales = DB::table('asigna_guias')->where('programacion_id', $programacion)->get()->keyBy('personal_id');
            $bloqueados = array_unique([...$identificadores, ...$actuales->keys()->all()]);
            $personal = DB::table('personal')->whereIn('id', $bloqueados)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($identificadores as $identificador) {
                $guia = $personal->get($identificador);
                if (! $guia || ! in_array(mb_strtolower($guia->tipo_rol), ['guia', 'guía'], true)) {
                    throw ValidationException::withMessages(['guias' => 'Selecciona únicamente personal registrado como guía.']);
                }
                if (in_array(mb_strtolower($guia->estado_laboral ?? ''), ['inactivo', 'inactiva', 'baja', 'suspendido', 'suspendida'], true)) {
                    throw ValidationException::withMessages(['guias' => 'Uno de los guías seleccionados no está activo laboralmente.']);
                }
            }

            // Los intervalos se consideran [inicio, fin): dos actividades consecutivas sí son compatibles.
            $traslape = DB::table('asigna_guias')->join('programaciones', 'programaciones.id', '=', 'asigna_guias.programacion_id')
                ->whereIn('asigna_guias.personal_id', $identificadores)->where('asigna_guias.disponibilidad', true)
                ->where('programaciones.id', '!=', $programacion)
                ->whereNotIn(DB::raw('LOWER(programaciones.estado)'), self::ESTADOS_SIN_ACTIVIDAD)
                ->where('fecha_inicio', '<', $actividad->fecha_fin)->where('fecha_fin', '>', $actividad->fecha_inicio)->exists();

            if ($traslape) {
                throw ValidationException::withMessages(['guias' => 'Uno de los guías ya tiene otra actividad en ese horario. No se guardaron cambios.']);
            }

            // Al retirar un guía se conserva su asignación histórica, marcada como no disponible.
            DB::table('asigna_guias')->where('programacion_id', $programacion)->whereNotIn('personal_id', $identificadores)
                ->update(['disponibilidad' => false, 'updated_at' => now()]);

            foreach ($identificadores as $identificador) {
                $anterior = $actuales->get($identificador);
                if ($anterior) {
                    DB::table('asigna_guias')->where('id', $anterior->id)->update([
                        'disponibilidad' => true, 'updated_at' => now(),
                        'fecha_asignacion' => $anterior->disponibilidad ? $anterior->fecha_asignacion : now(),
                    ]);
                } else {
                    DB::table('asigna_guias')->insert([
                        'personal_id' => $identificador, 'programacion_id' => $programacion,
                        'fecha_asignacion' => now(), 'disponibilidad' => true,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }, 3);
    }
}
