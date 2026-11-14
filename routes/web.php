<?php

use App\Http\Controllers\AutenticacionController;
use Illuminate\Support\Facades\Route;

// La presentación pública funciona sin consultar la base mientras se prepara el catálogo.
Route::view('/', 'welcome')->name('inicio');

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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// El módulo administrativo mantiene sus rutas separadas del flujo de autenticación.
require __DIR__.'/administracion.php';
