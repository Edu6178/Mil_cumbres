<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Define los servicios, su categoría, precio e itinerario.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('nombre_servicio', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_base', 12, 2);
            $table->text('itinerario')->nullable();
            $table->unsignedInteger('duracion_estimada')->nullable()->comment('Duración estimada en minutos; supuesto a confirmar');
            $table->string('estado_servicio', 50)->default('activo');
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
