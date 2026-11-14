<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AdministracionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Sin una cuenta autorizada expresamente, el panel permanece cerrado.
        Gate::define('administrar', fn (User $usuario): bool => in_array(
            (int) $usuario->getKey(), config('administracion.usuarios', []), true
        ));
    }
}
