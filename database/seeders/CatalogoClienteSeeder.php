<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CatalogoClienteSeeder extends Seeder
{
    public function run(): void
    {
        // Carga explícita local. No se incluye en DatabaseSeeder ni se habilita en producción.
        if (!app()->environment(['local', 'testing'])) {
            throw new RuntimeException('El catálogo de la entrevista solo se carga en local o pruebas.');
        }

        // Fuente: transcripciones del cliente del 22/10/2026. Sin tarifas, fechas o rutas inventadas.
        $catalogo = [
            'Experiencias turísticas' => [
                'Senderismo' => 'Recorridos por senderos para conocer el territorio mediante la metodología de turismo regenerativo de Mil Cumbres.',
                'Avistamiento de mariposa monarca' => 'Una experiencia de encuentro con la mariposa monarca, vinculada con el conocimiento del territorio y la contribución a su conservación.',
                'Avistamiento de luciérnagas' => 'Un acercamiento a las luciérnagas desde el turismo regenerativo, el conocimiento del territorio y su conservación.',
                'Recolección de hongos silvestres comestibles' => 'Experiencia de recolección de hongos silvestres comestibles acompañada por Mil Cumbres y su conocimiento del entorno.',
                'Avistamiento de aves' => 'Observación de aves para encontrarse con la naturaleza y conocer el territorio a través de una experiencia facilitada.',
            ],
            'Terapia de naturaleza' => [
                'Terapia de naturaleza' => 'Una experiencia centrada en la relación conmigo mismo y el encuentro con la naturaleza, dentro del modelo de turismo regenerativo.',
            ],
            'Regeneración organizacional' => [
                'Actividades para equipos' => 'Encuentros para equipos y organizaciones que trabajan la relación con los demás. La propuesta se construye mediante atención personalizada, según el propósito del grupo.',
            ],
            'Pedagogía de naturaleza' => [
                'Pedagogía de naturaleza' => 'Aprendizaje a través del encuentro con la naturaleza, con el conocimiento y la facilitación de Mil Cumbres.',
            ],
            'Ecovoluntariado' => [
                'Ecovoluntariado' => 'Participación orientada a la regeneración natural y a la contribución en el territorio, como parte de la metodología de turismo regenerativo.',
            ],
            'Talleres y cursos de turismo' => [
                'Talleres y cursos de turismo' => 'Formación en turismo, conservación y desarrollo regenerativo, con especial énfasis en el trabajo con núcleos agrarios. Esta es la segunda vertiente de trabajo de Mil Cumbres.',
            ],
        ];

        DB::transaction(function () use ($catalogo) {
            foreach ($catalogo as $nombreCategoria => $servicios) {
                $categoria = Categoria::firstOrCreate(['nombre_categoria' => $nombreCategoria]);
                foreach ($servicios as $nombre => $descripcion) {
                    // Repetir la carga conserva los precios y textos que se hayan editado después.
                    Servicio::firstOrCreate(['categoria_id' => $categoria->id, 'nombre_servicio' => $nombre], [
                        'descripcion' => $descripcion, 'precio_base' => null,
                        'duracion_estimada' => null, 'itinerario' => null, 'estado_servicio' => 'activo',
                    ]);
                }
            }
        });
    }
}
