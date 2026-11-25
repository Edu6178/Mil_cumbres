<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CatalogoWixSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Esta carga del catálogo Wix solo está habilitada en local o pruebas.');
        }
        DB::transaction(function () {
            foreach (config('catalogo-wix.servicios') as $ficha) {
                $categoria = Categoria::firstOrCreate(['nombre_categoria' => $ficha['categoria']]);
                Servicio::firstOrCreate(['categoria_id' => $categoria->id, 'nombre_servicio' => $ficha['nombre']], [
                    'descripcion' => $ficha['descripcion'], 'precio_base' => $ficha['precio'],
                    'tipo_precio' => 'desde', 'unidad_cobro' => null, 'moneda' => 'MXN',
                    'estado_servicio' => 'activo',
                ]);
            }
        });
    }
}
