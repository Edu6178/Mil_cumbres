<?php

use Illuminate\Support\Facades\Route;

// Vistas de Dev 1: no guardan solicitudes ni consultan datos privados.
// Al conectar el catálogo, el controlador podrá entregar los arreglos documentados en los componentes.
Route::view('/catalogo', 'publico.catalogo')->name('publico.catalogo');
Route::view('/guias', 'publico.guias')->name('publico.guias');
Route::view('/nosotros', 'publico.nosotros')->name('publico.nosotros');
Route::view('/turismo-regenerativo', 'publico.metodologia')->name('publico.metodologia');
Route::view('/operadora', 'publico.operadora')->name('publico.operadora');
Route::view('/consultoria', 'publico.consultoria')->name('publico.consultoria');
Route::view('/comunidades', 'publico.comunidades')->name('publico.comunidades');

// Previsualización de las plantillas de detalle: permanecen sin indexar mientras no haya registros publicados.
Route::view('/experiencias/detalle', 'publico.experiencia')->name('publico.experiencia');
Route::view('/guias/perfil', 'publico.guia')->name('publico.guia');
