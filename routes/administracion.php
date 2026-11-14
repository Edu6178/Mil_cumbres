<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\GuiaController;
use App\Http\Controllers\Admin\PanelController;
use App\Http\Controllers\Admin\ServicioController;
use Illuminate\Support\Facades\Route;

// Todas las lecturas y escrituras requieren la misma autorización del servidor.
Route::prefix('administracion')->name('administracion.')
    ->middleware(['auth', 'can:administrar', 'cache.headers:no_store;private;max_age=0'])
    ->group(function () {
        Route::get('/', [PanelController::class, 'index'])->name('inicio');
        Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios.indice');
        Route::get('/servicios/crear', [ServicioController::class, 'crear'])->name('servicios.crear');
        Route::post('/servicios', [ServicioController::class, 'guardar'])->name('servicios.guardar');
        Route::get('/servicios/{servicio}/editar', [ServicioController::class, 'editar'])->name('servicios.editar');
        Route::put('/servicios/{servicio}', [ServicioController::class, 'actualizar'])->name('servicios.actualizar');
        Route::patch('/servicios/{servicio}/visibilidad', [ServicioController::class, 'visibilidad'])->name('servicios.visibilidad');
        Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.indice');
        Route::post('/categorias', [CategoriaController::class, 'guardar'])->name('categorias.guardar');
        Route::get('/categorias/{categoria}/editar', [CategoriaController::class, 'editar'])->name('categorias.editar');
        Route::put('/categorias/{categoria}', [CategoriaController::class, 'actualizar'])->name('categorias.actualizar');
        Route::get('/guias', [GuiaController::class, 'index'])->name('guias');
        Route::get('/programaciones', [GuiaController::class, 'programaciones'])->name('programaciones');
        Route::get('/programaciones/{programacion}/guias', [GuiaController::class, 'editar'])->whereNumber('programacion')->name('asignacion.editar');
        Route::put('/programaciones/{programacion}/guias', [GuiaController::class, 'guardar'])->whereNumber('programacion')->name('asignacion.guardar');
        Route::get('/asistencia', [PanelController::class, 'asistencia'])->name('asistencia');
        Route::get('/asistencia/{programacion}', [PanelController::class, 'participantes'])->whereNumber('programacion')->name('participantes');
    });
