<?php

namespace App\Services\Contenido;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BibliotecaFotografica
{
    public function archivo(): string
    {
        return config('fotografias.archivo') ?: storage_path('app/private/contenido/fotografias.json');
    }

    public function estado(): array
    {
        if (! is_file($this->archivo())) return ['fotos' => [], 'espacios' => [], 'servicios' => []];

        return json_decode(File::get($this->archivo()), true, 512, JSON_THROW_ON_ERROR);
    }

    public function todas(bool $ocultas = false): array
    {
        $estado = $this->estado();
        $fotos = array_replace_recursive(config('fotografias.fotos', []), $estado['fotos'] ?? []);
        if (! $ocultas) $fotos = array_filter($fotos, fn ($foto) => $foto['publicada']);
        uasort($fotos, fn ($a, $b) => [$a['posicion'], $a['id']] <=> [$b['posicion'], $b['id']]);

        return array_map(function ($foto) {
            $url = ! empty($foto['subida']) ? route('publico.fotografia', $foto['id']) : asset($foto['archivo']);
            return $foto + ['url' => $url, 'mini' => ! empty($foto['subida']) ? $url : asset($foto['miniatura']),
                'foco' => $foto['foco_x'].'% '.$foto['foco_y'].'%'];
        }, $fotos);
    }

    public function foto(?string $id, bool $ocultas = false): ?array
    {
        return $this->todas($ocultas)[$id] ?? null;
    }

    public function revision(): string
    {
        return hash('sha256', json_encode($this->estado(), JSON_THROW_ON_ERROR));
    }

    public function guia(int $id): ?array
    {
        return $this->foto($this->estado()['guias'][(string) $id] ?? null);
    }

    public function aplicar(array $cambios): void
    {
        // Un único reemplazo atómico publica el lote completo; un conflicto no guarda una parte.
        $this->mutar(function (&$estado) use ($cambios) {
            abort_if(!hash_equals($this->revision(), $cambios['revision']), 409, 'Otra sesión cambió las fotografías. Recarga para revisar su versión antes de guardar.');
            $fotos = array_replace_recursive(config('fotografias.fotos', []), $estado['fotos'] ?? []);
            foreach ($cambios['fotos'] ?? [] as $id => $datos) {
                abort_unless(isset($fotos[$id]), 422, 'La fotografía ya no existe.');
                $estado['fotos'][$id] = array_replace($estado['fotos'][$id] ?? [], $datos);
                $fotos[$id] = array_replace($fotos[$id], $datos);
            }
            foreach (['espacios', 'servicios', 'guias'] as $tipo) {
                foreach ($cambios[$tipo] ?? [] as $clave => $id) {
                    if ($id !== null && (!isset($fotos[$id]) || !$fotos[$id]['publicada'])) {
                        throw ValidationException::withMessages([$tipo => 'Publica la fotografía elegida dentro de este lote o selecciona una ya publicada.']);
                    }
                    if ($id === null) unset($estado[$tipo][(string) $clave]);
                    else $estado[$tipo][(string) $clave] = $id;
                }
            }
        });
    }

    public function espacio(string $clave): ?array
    {
        $estado = $this->estado();
        $id = $estado['espacios'][$clave] ?? config('fotografias.espacios.'.$clave.'.foto');

        return $this->foto($id) ?? (array_values($this->todas())[0] ?? null);
    }

    public function servicio(array $servicio): ?array
    {
        $estado = $this->estado();
        $clave = Str::slug($servicio['nombre_servicio'] ?? '');
        $id = $estado['servicios'][(string) ($servicio['id'] ?? '')] ?? config('fotografias.servicios.'.$clave);
        if ($foto = $this->foto($id)) return $foto;
        $album = match ($servicio['linea_clave'] ?? '') {
            'pedagogia-de-naturaleza', 'talleres-y-cursos', 'consultoria-turistica' => 'aprendizaje',
            'ecovoluntariado', 'terapia-de-naturaleza' => 'biodiversidad',
            'regeneracion-organizacional' => 'encuentros',
            default => 'recorridos',
        };

        return array_values(array_filter($this->todas(), fn ($foto) => $foto['album'] === $album))[0] ?? null;
    }

    public function editar(string $id, array $datos): void
    {
        abort_unless($this->foto($id, true), 404);
        $this->mutar(function (&$estado) use ($id, $datos) {
            $estado['fotos'][$id] = array_replace($estado['fotos'][$id] ?? [], $datos);
        });
    }

    public function asignar(array $espacios, array $servicios): void
    {
        $this->mutar(function (&$estado) use ($espacios, $servicios) {
            foreach ([...array_values($espacios), ...array_values($servicios)] as $id) {
                if ($id && ! $this->foto($id)) throw ValidationException::withMessages(['espacios' => 'Selecciona fotografías publicadas.']);
            }
            $estado['espacios'] = array_replace($estado['espacios'] ?? [], $espacios);
            // Vacío restaura la selección editorial, sin alterar los datos del servicio.
            foreach ($servicios as $id => $foto) {
                if ($foto) $estado['servicios'][(string) $id] = $foto;
                else unset($estado['servicios'][(string) $id]);
            }
        });
    }

    public function subir(array $archivos, string $album): void
    {
        $nuevas = [];
        try {
            foreach ($archivos as $archivo) {
                /** @var UploadedFile $archivo */
                $id = 'foto-'.Str::uuid();
                $extension = match ($archivo->getMimeType()) { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp' };
                $ruta = 'fotografias-sitio/'.$id.'.'.$extension;
                if (! Storage::disk('local')->put($ruta, File::get($archivo->getRealPath()))) throw new \RuntimeException('No se pudo guardar la fotografía.');
                [$ancho, $alto] = getimagesize($archivo->getRealPath());
                $titulo = Str::ucfirst(trim(str_replace(['_', '-'], ' ', pathinfo(basename($archivo->getClientOriginalName()), PATHINFO_FILENAME))));
                $nuevas[$id] = ['id' => $id, 'subida' => true, 'archivo' => $ruta, 'miniatura' => $ruta,
                    'titulo' => Str::limit($titulo, 100, '') ?: 'Nueva fotografía', 'alternativo' => 'Fotografía de una experiencia de Mil Cumbres.',
                    'album' => $album, 'foco_x' => 50, 'foco_y' => 50, 'ancho' => $ancho, 'alto' => $alto,
                    'publicada' => false, 'destacada' => false, 'posicion' => 1000 + count($nuevas),
                    'originales' => [mb_substr(basename($archivo->getClientOriginalName()), 0, 180)],
                    'huella' => hash_file('sha256', $archivo->getRealPath())];
            }
            $this->mutar(function (&$estado) use ($nuevas) { $estado['fotos'] = array_replace($estado['fotos'] ?? [], $nuevas); });
        } catch (\Throwable $error) {
            foreach ($nuevas as $foto) Storage::disk('local')->delete($foto['archivo']);
            throw $error;
        }
    }

    private function mutar(callable $cambio): void
    {
        // La biblioteca editorial no modifica tablas de reservas, pagos ni personal.
        File::ensureDirectoryExists(dirname($this->archivo()));
        $bloqueo = fopen($this->archivo().'.lock', 'c');
        if (! $bloqueo || ! flock($bloqueo, LOCK_EX)) throw new \RuntimeException('La biblioteca está ocupada. Inténtalo de nuevo.');
        try {
            $estado = $this->estado();
            $cambio($estado);
            File::replace($this->archivo(), json_encode($estado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } finally {
            flock($bloqueo, LOCK_UN);
            fclose($bloqueo);
        }
    }
}
