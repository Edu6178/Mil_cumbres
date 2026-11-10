<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Registra las solicitudes de los clientes para una programación.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('programacion_id')->constrained('programaciones')->restrictOnDelete();
            $table->foreignId('estado_reservacion_id')->constrained('estado_reservaciones')->restrictOnDelete();
            $table->dateTime('fecha_solicitud');
            $table->unsignedInteger('numero_personas');
            $table->string('tipo_grupo', 100)->nullable();
            $table->text('proposito_visita')->nullable();
            $table->text('personalizacion')->nullable();
            $table->decimal('monto_total', 12, 2)->nullable();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('reservaciones');
    }
};
