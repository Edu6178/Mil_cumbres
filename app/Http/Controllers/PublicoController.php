<?php

namespace App\Http\Controllers;

use App\Services\Demostracion\PresentacionDemostracion;
use App\Services\Demostracion\RegistroDemostracion;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicoController extends Controller
{
    public function __construct(private PresentacionDemostracion $presentacion) {}

    public function catalogo(RegistroDemostracion $registro): View
    {
        if (!$registro->activo()) {
            return view('publico.catalogo');
        }

        return view('publico.catalogo', ['servicios' => $this->presentacion->servicios($registro)]);
    }

    public function experiencia(int $servicio, RegistroDemostracion $registro): View
    {
        abort_unless($registro->activo(), 404);
        $publico = $this->presentacion->servicios($registro)->firstWhere('id', $servicio);
        abort_unless($publico, 404);

        return view('publico.experiencia', ['servicio' => $publico]);
    }

    public function guias(RegistroDemostracion $registro): View
    {
        return $registro->activo() ? view('publico.guias', ['guias' => $this->presentacion->perfiles($registro)]) : view('publico.guias');
    }

    public function guia(int $personal, RegistroDemostracion $registro): View
    {
        abort_unless($registro->activo(), 404);
        $publico = $this->presentacion->perfiles($registro)->firstWhere('id', $personal);
        abort_unless($publico, 404);

        return view('publico.guia', ['guia' => $publico]);
    }

    public function comunidades(RegistroDemostracion $registro): View
    {
        if (!$registro->activo()) {
            return view('publico.comunidades');
        }
        $ids = $registro->leer()['ids']['comunidades'];
        $comunidades = DB::table('comunidades')->join('regiones', 'regiones.id', '=', 'comunidades.regiones_id')
            ->whereIn('comunidades.id', array_values($ids))
            ->select('comunidades.id', 'nombre_comunidad', 'nombre_region')->get()->map(function ($comunidad) use ($ids) {
                $clave = array_search($comunidad->id, $ids, false);

                return [...(array) $comunidad, 'publicada' => true, 'descripcion' => config('demostracion.comunidades.'.$clave.'.descripcion')];
            });

        return view('publico.comunidades', compact('comunidades'));
    }

}
