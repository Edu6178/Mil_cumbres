<?php

namespace App\Services\Demostracion;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class RegistroDemostracion
{
    public function ruta(): string
    {
        if ($ruta = config('demostracion.registro')) {
            return $ruta;
        }

        $conexion = DB::connection()->getConfig();
        $identidad = array_intersect_key($conexion, array_flip(['driver', 'host', 'port', 'database', 'username', 'prefix', 'search_path']));

        return storage_path('app/private/demostracion/'.hash('sha256', json_encode($identidad)).'.json');
    }

    public function leer(): array
    {
        if (!app()->environment(['local', 'testing']) || !File::exists($this->ruta())) {
            return [];
        }

        return json_decode(File::get($this->ruta()), true, 512, JSON_THROW_ON_ERROR);
    }

    public function activo(): bool
    {
        try {
            return ($this->leer()['version'] ?? null) === 1;
        } catch (\JsonException $error) {
            report($error);
            return false;
        }
    }

    public function guardar(array $registro): void
    {
        File::ensureDirectoryExists(dirname($this->ruta()));
        if (File::put($this->ruta(), json_encode($registro, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), true) === false) {
            throw new RuntimeException('No se pudo guardar el registro privado de la demostración.');
        }
    }

    public function limpiar(): array
    {
        $registro = $this->leer();
        if (!$registro) {
            return [];
        }

        $permitidas = ['categorias', 'estado_reservaciones', 'regiones', 'servicios', 'servicio_regiones', 'programaciones', 'personas', 'personal', 'comunidades', 'asigna_guias', 'clientes', 'metodo_pago', 'reservaciones', 'asigna_reservacion_invitados', 'pagos'];
        $ids = collect($registro['creados'])->groupBy('tabla')->map(fn ($filas) => $filas->pluck('id')->all())->all();
        // Verifica el lote y sus dependencias antes de retirar nada, incluso si la base fue recreada.
        DB::transaction(function () use ($registro, $permitidas, $ids) {
            foreach ($registro['creados'] as $creado) {
                if (!in_array($creado['tabla'], $permitidas, true)) {
                    throw new RuntimeException('El registro del lote contiene una tabla no permitida.');
                }
                $fila = DB::table($creado['tabla'])->where('id', $creado['id'])->first(['created_at']);
                if ($fila && (string) $fila->created_at !== (string) $creado['creado_en']) {
                    throw new RuntimeException('Los registros actuales no coinciden con el lote original.');
                }
            }
            foreach (Schema::getTables() as $tabla) {
                foreach (Schema::getForeignKeys($tabla['name']) as $relacion) {
                    if (!isset($ids[$relacion['foreign_table']]) || count($relacion['columns']) !== 1) continue;
                    $dependientes = DB::table($tabla['name'])->whereIn($relacion['columns'][0], $ids[$relacion['foreign_table']]);
                    if (isset($ids[$tabla['name']])) $dependientes->whereNotIn('id', $ids[$tabla['name']]);
                    if ($dependientes->exists()) throw new RuntimeException('Hay registros nuevos que dependen de la demostración.');
                }
            }
            foreach (array_reverse($registro['creados']) as $creado) {
                DB::table($creado['tabla'])->where('id', $creado['id'])->delete();
            }
        });
        File::delete($this->ruta());

        return $registro;
    }
}
