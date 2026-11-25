<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La edad se conserva para esta visita, sin inventar una fecha de nacimiento.
        Schema::table('reservaciones', fn (Blueprint $table) => $table->unsignedTinyInteger('edad_responsable')->nullable());
        Schema::table('asigna_reservacion_invitados', fn (Blueprint $table) => $table->unsignedTinyInteger('edad')->nullable());
    }

    public function down(): void
    {
        Schema::table('asigna_reservacion_invitados', fn (Blueprint $table) => $table->dropColumn('edad'));
        Schema::table('reservaciones', fn (Blueprint $table) => $table->dropColumn('edad_responsable'));
    }
};
