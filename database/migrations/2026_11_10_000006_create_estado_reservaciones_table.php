<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Crea el catálogo de estados que puede tener una reservación.
    public function up(): void
    {
        Schema::create('estado_reservaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_estado', 100)->unique();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('estado_reservaciones');
    }
};
