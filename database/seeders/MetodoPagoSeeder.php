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
        ];

        foreach ($metodos as $metodo) {
            DB::table('metodo_pago')->updateOrInsert(
                ['metodo' => $metodo],
                [
                    'updated_at' => now(),
                ]
            );
        }
    }
}
