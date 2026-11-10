<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Relaciona cada comunidad con su región y con una persona de contacto opcional.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        // Las relaciones con nullOnDelete conservan el registro y dejan vacía la referencia eliminada.
        Schema::create('comunidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regiones_id')->constrained('regiones')->restrictOnDelete();
            $table->string('nombre_comunidad', 180);
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('comunidades');
    }
};
