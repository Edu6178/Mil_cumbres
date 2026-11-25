<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AdministracionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('administrar', fn (User $usuario): bool => $usuario->accesoActivo()
            && in_array($usuario->rolActual(), ['super_admin', 'admin'], true));
        Gate::define('gestionar-accesos', fn (User $usuario): bool => $usuario->accesoActivo() && $usuario->rolActual() === 'super_admin');
        Gate::define('equipo', fn (User $usuario): bool => $usuario->accesoActivo()
            && in_array($usuario->rolActual(), ['super_admin', 'admin', 'empleado', 'guia'], true));
    }
}
