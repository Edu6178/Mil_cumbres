<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Guarda los datos personales que comparten clientes, empleados e invitados.
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('apellido_paterno', 120);
            $table->string('apellido_materno', 120)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('correo')->nullable()->index();
            $table->date('fecha_nacimiento')->nullable();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
