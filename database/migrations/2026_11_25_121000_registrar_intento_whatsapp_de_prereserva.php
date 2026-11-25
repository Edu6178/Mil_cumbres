<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservaciones', function (Blueprint $table) {
            $table->string('whatsapp_estado', 30)->default('sin_solicitar');
            $table->timestamp('whatsapp_consentido_at')->nullable();
            $table->timestamp('whatsapp_intentado_at')->nullable();
            $table->string('whatsapp_mensaje_id', 255)->nullable();
            $table->string('whatsapp_error_codigo', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservaciones', fn (Blueprint $table) => $table->dropColumn([
            'whatsapp_estado', 'whatsapp_consentido_at', 'whatsapp_intentado_at', 'whatsapp_mensaje_id', 'whatsapp_error_codigo',
        ]));
    }
};
