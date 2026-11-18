<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Conserva importes e historial; los registros anteriores necesitan revisión manual.
        Schema::table('reservaciones', function (Blueprint $table) {
            $table->string('moneda', 3)->default('MXN');
            $table->dateTime('fecha_confirmacion')->nullable()->index();
            $table->foreignId('confirmada_por')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->string('moneda', 3)->default('MXN');
            $table->string('estado', 20)->default('pendiente')->index();
            $table->dateTime('fecha_confirmacion')->nullable();
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('clave_registro')->nullable()->unique();
            $table->unique(['proveedor', 'proveedor_referencia'], 'pagos_proveedor_referencia_unica');
        });
    }

    public function down(): void
    {
        // SQLite reconstruye tablas al quitar columnas; difiere sus referencias hasta terminar la operación.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA defer_foreign_keys = ON');
        }
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['confirmado_por']);
            $table->dropUnique('pagos_proveedor_referencia_unica');
            $table->dropUnique(['clave_registro']);
            $table->dropIndex(['estado']);
            $table->dropColumn(['moneda', 'estado', 'fecha_confirmacion', 'confirmado_por', 'clave_registro']);
        });
        Schema::table('reservaciones', function (Blueprint $table) {
            $table->dropForeign(['confirmada_por']);
            $table->dropIndex(['fecha_confirmacion']);
            $table->dropColumn(['moneda', 'fecha_confirmacion', 'confirmada_por']);
        });
    }
};
