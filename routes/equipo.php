<?php

use App\Http\Controllers\EquipoController;
use Illuminate\Support\Facades\Route;

Route::get('/equipo', [EquipoController::class, 'index'])->middleware(['auth', 'can:equipo', 'cache.headers:no_store;private;max_age=0'])->name('equipo.inicio');
