<?php

use App\Http\Controllers\AutenticacionController;
use App\Http\Controllers\Cliente\PreReservacionController;
use App\Http\Controllers\PagoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Página pública con acceso a las programaciones disponibles.
Route::get('/', [PreReservacionController::class, 'inicio'])
    ->name('inicio');

// Registro público de solicitudes de pre-reservación.
Route::post('/pre-reservaciones', [PreReservacionController::class, 'guardar'])
    ->name('pre-reservaciones.guardar');

// Los formularios públicos solo se muestran a visitantes sin una sesión iniciada.
Route::middleware(['guest', 'cache.headers:no_store;private;max_age=0'])->group(function () {
    Route::get('/acceso', [AutenticacionController::class, 'acceso'])->name('acceso');
    Route::post('/acceso', [AutenticacionController::class, 'entrar'])->name('acceso.entrar');
    Route::get('/registro', [AutenticacionController::class, 'registro'])->name('registro');
    Route::post('/registro', [AutenticacionController::class, 'registrar'])->name('registro.guardar');
});

// Esta pantalla es personal; no habilita funciones administrativas.
Route::view('/mi-cuenta', 'autenticacion.cuenta')
    ->middleware(['auth', 'cache.headers:no_store;private;max_age=0'])
    ->name('cuenta');

Route::post('/salir', [AutenticacionController::class, 'salir'])
    ->name('salir');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');

// =====================================================
// MÓDULO DE VENTAS Y PAGOS - MIL CUMBRES
// Responsable: desarrollo-longino
// =====================================================

// Solo los usuarios autenticados pueden acceder.
// Antes de habilitarlo en producción se deberá agregar
// la autorización específica para el personal de ventas.
Route::middleware(['auth', 'cache.headers:no_store;private;max_age=0'])
    ->group(function () {

        // Consultar los pagos registrados.
        Route::get('/pagos', [PagoController::class, 'index'])
            ->name('pagos.index');

        // Mostrar el formulario de registro de pagos.
        Route::get('/pagos/crear', [PagoController::class, 'create'])
            ->name('pagos.create');

        // Guardar la información del pago.
        Route::post('/pagos', [PagoController::class, 'store'])
            ->name('pagos.store');
    });

// =====================================================
// RUTAS ADMINISTRATIVAS EXISTENTES
// =====================================================

// El módulo administrativo mantiene sus rutas separadas
// del flujo de autenticación.
require __DIR__.'/administracion.php';
