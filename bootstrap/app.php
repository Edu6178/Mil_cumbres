<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trimStrings(except: ['contrasena', 'contrasena_confirmation']);
        $middleware->redirectGuestsTo(fn (Request $request) => route('acceso'));
        $middleware->redirectUsersTo(fn (Request $request) => route(
            $request->user()?->can('administrar') ? 'administracion.inicio' : 'cuenta'
        ));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Los nombres de los campos están en español; sus contraseñas tampoco deben guardarse en mensajes de sesión.
        $exceptions->dontFlash(['contrasena', 'contrasena_confirmation']);

        // Una conexión pendiente o interrumpida se presenta sin revelar consultas ni datos internos.
        $exceptions->render(function (QueryException $error, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'El servicio no está disponible en este momento. Inténtalo más tarde.'], 503);
            }

            return response()->view('errors.503', [], 503);
        });
    })->create();
