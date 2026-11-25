<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            // Un precio pendiente no equivale a una actividad gratuita.
            $table->decimal('precio_base', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('servicios')->whereNull('precio_base')->exists()) {
            throw new RuntimeException('Confirma los precios pendientes antes de revertir esta migración.');
        }
        Schema::table('servicios', function (Blueprint $table) {
            $table->decimal('precio_base', 12, 2)->nullable(false)->change();
        });
    }
};
