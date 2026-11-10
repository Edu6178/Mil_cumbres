<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Asigna integrantes del personal como guías de una programación.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('asigna_guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_id')->constrained('personal')->restrictOnDelete();
            $table->foreignId('programacion_id')->constrained('programaciones')->restrictOnDelete();
            $table->dateTime('fecha_asignacion');
            $table->boolean('disponibilidad')->default(true);
            $table->unique(['personal_id', 'programacion_id'], 'asig_guias_personal_prog_uniq');
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('asigna_guias');
    }
};
