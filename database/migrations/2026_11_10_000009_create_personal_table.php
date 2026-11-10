<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Registra la información laboral del personal y la vincula con sus datos personales.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->text('formacion')->nullable();
            $table->string('tipo_rol', 100);
            $table->string('estado_laboral', 60)->nullable();
            $table->text('talentos')->nullable();
            $table->text('virtudes')->nullable();
            $table->foreignId('persona_id')->constrained('personas')->restrictOnDelete();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
