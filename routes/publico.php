<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicoController;

// El catálogo público muestra la demostración sin exponer datos privados ni guardar solicitudes.
Route::get('/catalogo', [PublicoController::class, 'catalogo'])->name('publico.catalogo');
Route::get('/guias', [PublicoController::class, 'guias'])->name('publico.guias');
Route::view('/nosotros', 'publico.nosotros')->name('publico.nosotros');
Route::view('/turismo-regenerativo', 'publico.metodologia')->name('publico.metodologia');
Route::view('/operadora', 'publico.operadora')->name('publico.operadora');
Route::view('/consultoria', 'publico.consultoria')->name('publico.consultoria');
Route::get('/comunidades', [PublicoController::class, 'comunidades'])->name('publico.comunidades');
Route::view('/creditos-fotograficos', 'publico.creditos')->name('publico.creditos');

// Previsualización de las plantillas de detalle: permanecen sin indexar mientras no haya registros publicados.
Route::view('/experiencias/detalle', 'publico.experiencia')->name('publico.experiencia');
Route::view('/guias/perfil', 'publico.guia')->name('publico.guia');
Route::get('/experiencias/{servicio}', [PublicoController::class, 'experiencia'])->whereNumber('servicio')->name('publico.experiencia.mostrar');
Route::get('/guias/{personal}', [PublicoController::class, 'guia'])->whereNumber('personal')->name('publico.guia.mostrar');
