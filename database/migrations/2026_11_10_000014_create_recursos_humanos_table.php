<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Identifica al personal que se puede asignar como recurso humano.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('recursos_humanos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_id')->unique()->constrained('personal')->restrictOnDelete();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('recursos_humanos');
    }
};
