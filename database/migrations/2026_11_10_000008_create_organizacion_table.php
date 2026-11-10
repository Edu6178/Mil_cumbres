<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Guarda las organizaciones y las relaciona con su tipo.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('organizacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_organizacion', 200);
            $table->string('rfc', 13)->nullable()->unique();
            $table->foreignId('tipo_organizacion_id')->constrained('tipo_organizacion')->restrictOnDelete();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('organizacion');
    }
};
