<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccesoActivo
{
    public function handle(Request $request, Closure $siguiente)
    {
        if ($request->user() && ! $request->user()->accesoActivo()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('acceso')->with('estado', 'Tu acceso al equipo está pendiente de aprobación o fue desactivado.');
        }
        return $siguiente($request);
    }
}
