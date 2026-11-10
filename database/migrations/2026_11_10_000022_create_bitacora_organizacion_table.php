<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Registra cuándo y desde qué dispositivo se sincronizó una reservación.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('bitacora_organizacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservacion_id')->constrained('reservaciones')->restrictOnDelete();
            $table->dateTime('fecha_sincronizacion');
            $table->string('dispositivo_origen', 200)->nullable();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('bitacora_organizacion');
    }
};
