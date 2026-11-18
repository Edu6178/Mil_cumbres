<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
            'Link de pago',
            'Transferencia bancaria',
            'Depósito bancario',
            'Efectivo',
        ];

        foreach ($metodos as $metodo) {
            // Completa únicamente los métodos faltantes, sin reemplazar los del equipo.
            if (!DB::table('metodo_pago')->where('metodo', $metodo)->exists()) {
                DB::table('metodo_pago')->insert(['metodo' => $metodo, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }
}
