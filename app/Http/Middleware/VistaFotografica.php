<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VistaFotografica
{
    public function handle(Request $request, Closure $siguiente)
    {
        $editor = $request->query('vista_fotografias') === '1';
        if ($editor) abort_unless($request->user()?->can('administrar'), 403);
        $respuesta = $siguiente($request);
        if ($editor) {
            $respuesta->headers->set('Cache-Control', 'private, no-store, max-age=0');
            $respuesta->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $respuesta->headers->set('Referrer-Policy', 'same-origin');
            $respuesta->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }
        return $respuesta;
    }
}
