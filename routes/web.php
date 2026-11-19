<?php
use App\Http\Controllers\AutenticacionController;
use App\Http\Controllers\Cliente\PreReservacionController;
use App\Http\Controllers\Cliente\ComprobantePreReservacionController;
use Illuminate\Support\Facades\Route;
// Página pública con acceso a las programaciones disponibles.
Route::get('/', [PreReservacionController::class, 'inicio'])
    ->name('inicio');
// Registro público de solicitudes de pre-reservación.
Route::post('/pre-reservaciones', [PreReservacionController::class, 'guardar'])
    ->name('pre-reservaciones.guardar');
// El acuse puede descargarse sin registro, únicamente con el enlace temporal de la solicitud.
Route::get('/pre-reservaciones/{reservacion}/comprobante.pdf', [ComprobantePreReservacionController::class, 'descargar'])
    ->whereNumber('reservacion')->middleware(['signed', 'throttle:10,1'])
    ->name('pre-reservaciones.pdf');
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
Route::post('/salir', [AutenticacionController::class, 'salir'])->name('salir');
require __DIR__.'/administracion.php';
// Catálogo, guías e identidad institucional del frontend público.
require __DIR__.'/publico.php';
