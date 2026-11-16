<?php

namespace Database\Seeders;

use App\Models\User;
use Dotenv\Dotenv;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class CuentaPruebaSeeder extends Seeder
{
    // Credenciales compartidas para revisar el proyecto durante el desarrollo.
    private const CORREO = 'admin@milcumbres.test';
    private const CONTRASENA = 'MilCumbres2026!';

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('La cuenta de prueba solo se prepara en desarrollo.');

            return;
        }

        // Repetir el seeder conserva la cuenta y los cambios que ya le hayan hecho.
        $usuario = User::firstOrCreate(['email' => self::CORREO], [
            'name' => 'Administrador de prueba',
            'password' => self::CONTRASENA,
        ]);

        $autorizados = config('administracion.usuarios', []);
        if (app()->environment('local')) {
            $autorizados = $this->autorizarEnConfiguracionLocal((int) $usuario->id, $autorizados);
        }

        config(['administracion.usuarios' => array_values(array_unique([...$autorizados, (int) $usuario->id]))]);
        $this->command?->info('Cuenta de prueba lista: '.self::CORREO.' · Acceso administrativo habilitado.');
    }

    private function autorizarEnConfiguracionLocal(int $identificador, array $autorizados): array
    {
        $archivo = app()->environmentFilePath();
        if (! is_file($archivo) || ! is_readable($archivo) || ! is_writable($archivo)) {
            throw new RuntimeException('La cuenta está creada, pero falta autorizarla: revisa los permisos del archivo .env y repite el seeder.');
        }

        $contenido = file_get_contents($archivo);
        if ($contenido === false) {
            throw new RuntimeException('No fue posible leer la configuración local.');
        }

        // Cada base puede asignar un ID distinto; se usa el ID real y se conservan los demás administradores.
        $variables = Dotenv::parse($contenido);
        foreach (explode(',', (string) ($variables['ADMIN_USUARIOS'] ?? '')) as $valor) {
            if (ctype_digit(trim($valor)) && (int) $valor > 0) {
                $autorizados[] = (int) $valor;
            }
        }
        $autorizados = array_values(array_unique([...$autorizados, $identificador]));
        $linea = 'ADMIN_USUARIOS='.implode(',', $autorizados);
        $patron = '/^\h*(?:export\h+)?ADMIN_USUARIOS\h*=[^\r\n]*/m';
        $salto = str_contains($contenido, "\r\n") ? "\r\n" : "\n";
        $actualizado = preg_match($patron, $contenido)
            ? preg_replace($patron, $linea, $contenido)
            : rtrim($contenido, "\r\n").$salto.$linea.$salto;

        if ($actualizado !== $contenido && file_put_contents($archivo, $actualizado, LOCK_EX) === false) {
            throw new RuntimeException('La cuenta está creada, pero no se pudo guardar su autorización en .env.');
        }

        // Las pruebas nunca escriben .env; en desarrollo se descarta únicamente la configuración en caché.
        if (app()->configurationIsCached()) {
            Artisan::call('config:clear');
        }

        return $autorizados;
    }
}
