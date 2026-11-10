<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Crea el catálogo de tipos de organización.
    public function up(): void
    {
        Schema::create('tipo_organizacion', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 150);
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('tipo_organizacion');
    }
};
