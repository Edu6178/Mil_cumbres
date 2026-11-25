<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('equipo:superadmin {correo : Correo de una cuenta existente}', function () {
    $usuario = \App\Models\User::where('email', mb_strtolower(trim($this->argument('correo'))))->first();
    if (! $usuario) { $this->error('No existe una cuenta con ese correo.'); return 1; }
    $usuario->forceFill(['rol' => 'super_admin', 'estado_acceso' => 'activo', 'acceso_revisado_at' => now()])->save();
    $this->info('La cuenta existente ya tiene acceso de superadministrador.');
})->purpose('Habilitar expresamente un superadministrador existente desde la consola del servidor');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sin --confirmar solo muestra el lote; su retiro nunca incluye cuentas de acceso.
Artisan::command('demostracion:limpiar {--confirmar : Retirar el lote ficticio de esta base}', function () {
    $registro = app(\App\Services\Demostracion\RegistroDemostracion::class);
    $datos = $registro->leer();
    if (!$datos) {
        $this->info('No hay un lote de demostración registrado en esta base.');
        return;
    }
    $this->info('Registros del lote: '.count($datos['creados']));
    if (!$this->option('confirmar')) {
        $this->comment('Se conservaron los datos. Para retirarlos, utiliza --confirmar.');
        return;
    }
    try {
        $registro->limpiar();
        $this->info('Se retiró el lote ficticio. Las cuentas existentes se conservaron.');
    } catch (\Throwable $error) {
        report($error);
        $this->error('No se retiró el lote: hay registros relacionados o una falla de conexión. Se conservaron los datos.');
        return 1;
    }
})->purpose('Consultar o retirar los datos ficticios de la presentación');
