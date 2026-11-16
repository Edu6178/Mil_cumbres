<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // migrate --seed prepara el acceso compartido de desarrollo sin inventar datos del catálogo.
        $this->call(CuentaPruebaSeeder::class);
    }
}
