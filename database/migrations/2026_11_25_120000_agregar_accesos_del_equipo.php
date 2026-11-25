<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol', 30)->nullable();
            $table->string('estado_acceso', 20)->default('activo');
            $table->foreignId('personal_id')->nullable()->unique()->constrained('personal')->nullOnDelete();
            $table->string('motivo_acceso', 500)->nullable();
            $table->timestamp('acceso_solicitado_at')->nullable();
            $table->timestamp('acceso_revisado_at')->nullable();
            $table->foreignId('acceso_revisado_por')->nullable()->constrained('users')->nullOnDelete();
        });

        // Conserva los accesos existentes. El primer administrador configurado puede revisar solicitudes.
        $superAsignado = false;
        foreach (config('administracion.usuarios', []) as $id) {
            $actualizados = DB::table('users')->where('id', $id)->update(['rol' => $superAsignado ? 'admin' : 'super_admin']);
            if ($actualizados > 0) $superAsignado = true;
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['personal_id']);
            $table->dropConstrainedForeignId('acceso_revisado_por');
            $table->dropConstrainedForeignId('personal_id');
            $table->dropColumn(['rol', 'estado_acceso', 'motivo_acceso', 'acceso_solicitado_at', 'acceso_revisado_at']);
        });
    }
};
