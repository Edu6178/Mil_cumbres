<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Registra los pagos de una reservación y sus referencias de comprobación.
    public function up(): void
    {
        // Las relaciones con restrictOnDelete impiden borrar registros que todavía se utilizan.
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservacion_id')->constrained('reservaciones')->restrictOnDelete();
            $table->foreignId('metodo_pago_id')->constrained('metodo_pago')->restrictOnDelete();
            $table->decimal('monto', 12, 2);
            $table->dateTime('fecha_pago')->nullable();
            $table->string('referencia_comprobante', 180)->nullable()->unique();
            $table->string('proveedor', 120)->nullable();
            $table->string('proveedor_referencia', 180)->nullable();
            $table->text('url_pago')->nullable();
            // Laravel registra aquí las fechas de creación y última actualización.
            $table->timestamps();
        });
    }

    // Elimina esta tabla al revertir la migración; Laravel revierte primero sus dependencias.
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
