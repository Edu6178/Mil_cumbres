<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Programa las fechas, el cupo y el estado de un servicio en una región.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('programaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_reg_id')->constrained('servicio_regiones')->restrictOnDelete();
            $table->unsignedInteger('cupo_maximo');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->string('estado', 50)->default('programada');
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('programaciones');
    }
};
