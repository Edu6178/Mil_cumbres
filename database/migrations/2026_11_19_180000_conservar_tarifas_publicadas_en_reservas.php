<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->string('tipo_precio', 20)->default('fijo');
            $table->string('unidad_cobro', 20)->nullable();
            $table->string('moneda', 3)->default('MXN');
        });
        Schema::table('reservaciones', function (Blueprint $table) {
            // Conserva el precio visto al solicitar, separado del importe que después acuerde el administrador.
            $table->decimal('tarifa_publicada', 12, 2)->nullable();
            $table->decimal('importe_publicado', 12, 2)->nullable();
            $table->string('unidad_cobro', 20)->nullable();
            $table->string('tipo_precio', 20)->nullable();
            $table->string('moneda_publicada', 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservaciones', fn (Blueprint $table) => $table->dropColumn(['tarifa_publicada', 'importe_publicado', 'unidad_cobro', 'tipo_precio', 'moneda_publicada']));
        Schema::table('servicios', fn (Blueprint $table) => $table->dropColumn(['tipo_precio', 'unidad_cobro', 'moneda']));
    }
};
