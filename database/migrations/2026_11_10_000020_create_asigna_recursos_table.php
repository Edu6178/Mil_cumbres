<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Relaciona los recursos humanos o materiales con una reservación.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('asigna_recursos', function (Blueprint $table) {
            $table->id();
            // Ambas referencias siguen siendo opcionales según el diseño recibido; revisar esta regla si cambia el esquema.
            $table->foreignId('recurso_humano_id')->nullable()->constrained('recursos_humanos')->restrictOnDelete();
            $table->foreignId('recurso_material_id')->nullable()->constrained('recursos_materiales')->restrictOnDelete();
            $table->foreignId('reservacion_id')->constrained('reservaciones')->restrictOnDelete();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('asigna_recursos');
    }
};
