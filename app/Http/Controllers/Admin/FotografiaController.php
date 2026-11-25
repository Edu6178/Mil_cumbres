<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Services\Contenido\BibliotecaFotografica;
use App\Services\Administracion\AgendaGuias;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FotografiaController extends Controller
{
    public function index(Request $request, BibliotecaFotografica $biblioteca)
    {
        $datos = $request->validate(['album' => ['nullable', Rule::in(array_keys(config('fotografias.albumes')))], 'buscar' => ['nullable', 'string', 'max:100']]);

        return view('administracion.fotografias', ['editor' => $this->datosEditor($biblioteca)]);
    }

    public function subir(Request $request, BibliotecaFotografica $biblioteca)
    {
        $datos = $request->validate([
            'album' => ['required', Rule::in(array_keys(config('fotografias.albumes')))],
            'fotos' => ['required', 'array', 'min:1', 'max:10'],
            'fotos.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=200,min_height=200,max_width=4000,max_height=4000'],
        ]);
        $biblioteca->subir($datos['fotos'], $datos['album']);
        if ($request->expectsJson()) return response()->json($this->datosEditor($biblioteca), 201);

        return to_route('administracion.fotografias')->with('estado', 'Fotografías cargadas como borradores. Revisa el título y la descripción antes de publicarlas.');
    }

    public function actualizar(string $foto, Request $request, BibliotecaFotografica $biblioteca)
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:100'], 'alternativo' => ['required', 'string', 'max:240'],
            'album' => ['required', Rule::in(array_keys(config('fotografias.albumes')))],
            'foco_x' => ['required', 'integer', 'between:0,100'], 'foco_y' => ['required', 'integer', 'between:0,100'],
            'posicion' => ['required', 'integer', 'between:0,99999'], 'publicada' => ['required', 'boolean'], 'destacada' => ['required', 'boolean'],
        ]);
        $biblioteca->editar($foto, [...$datos, 'publicada' => (bool) $datos['publicada'], 'destacada' => (bool) $datos['destacada']]);

        return back()->with('estado', 'La fotografía se actualizó. El encuadre, el orden y la publicación ya se reflejan en el sitio.');
    }

    public function asignar(Request $request, BibliotecaFotografica $biblioteca)
    {
        $ids = array_keys($biblioteca->todas());
        $datos = $request->validate([
            'espacios' => ['required', 'array:'.implode(',', array_keys(config('fotografias.espacios')))],
            'espacios.*' => ['required', Rule::in($ids)],
            'servicios' => ['sometimes', 'array', 'max:500'], 'servicios.*' => ['nullable', Rule::in($ids)],
        ]);
        // Solo se aceptan asignaciones a servicios que existen actualmente.
        $servicios = $datos['servicios'] ?? [];
        abort_if(count(array_diff(array_keys($servicios), Servicio::whereIn('id', array_keys($servicios))->pluck('id')->all())) > 0, 422);
        $biblioteca->asignar($datos['espacios'], $servicios);

        return back()->with('estado', 'Portadas y fotografías de los servicios actualizadas.');
    }

    public function aplicar(Request $request, BibliotecaFotografica $biblioteca)
    {
        $campos = 'titulo,alternativo,album,foco_x,foco_y,posicion,publicada,destacada';
        $datos = $request->validate([
            'revision' => ['required', 'string', 'size:64'],
            'fotos' => ['present', 'array', 'max:500'], 'fotos.*' => ['array:'.$campos],
            'fotos.*.titulo' => ['sometimes', 'required', 'string', 'max:100'],
            'fotos.*.alternativo' => ['sometimes', 'required', 'string', 'max:240'],
            'fotos.*.album' => ['sometimes', 'required', Rule::in(array_keys(config('fotografias.albumes')))],
            'fotos.*.foco_x' => ['sometimes', 'required', 'integer', 'between:0,100'],
            'fotos.*.foco_y' => ['sometimes', 'required', 'integer', 'between:0,100'],
            'fotos.*.posicion' => ['sometimes', 'required', 'integer', 'between:0,99999'],
            'fotos.*.publicada' => ['sometimes', 'required', 'boolean'], 'fotos.*.destacada' => ['sometimes', 'required', 'boolean'],
            'espacios' => ['present', 'array:'.implode(',', array_keys(config('fotografias.espacios')))],
            'espacios.*' => ['required', 'string', 'max:100'],
            'servicios' => ['present', 'array', 'max:500'], 'servicios.*' => ['nullable', 'string', 'max:100'],
            'guias' => ['present', 'array', 'max:500'], 'guias.*' => ['nullable', 'string', 'max:100'],
        ]);
        $servicios = array_keys($datos['servicios']);
        $guias = array_keys($datos['guias']);
        abort_if(count(array_diff($servicios, Servicio::whereIn('id', $servicios)->pluck('id')->all())) > 0, 422);
        abort_if(count(array_diff($guias, AgendaGuias::guias()->whereIn('personal.id', $guias)->pluck('personal.id')->all())) > 0, 422);
        $biblioteca->aplicar($datos);

        return response()->json($this->datosEditor($biblioteca));
    }

    private function datosEditor(BibliotecaFotografica $biblioteca): array
    {
        $estado = $biblioteca->estado();
        $espacios = collect(config('fotografias.espacios'))->map(fn ($espacio, $clave) => [
            'nombre' => $espacio['nombre'], 'foto' => $biblioteca->espacio($clave)['id'] ?? null,
        ])->all();
        $servicios = Servicio::orderBy('nombre_servicio')->get(['id', 'nombre_servicio'])->mapWithKeys(fn ($servicio) => [
            $servicio->id => ['nombre' => $servicio->nombre_servicio, 'foto' => $biblioteca->servicio($servicio->toArray())['id'] ?? null],
        ])->all();
        $registro = app(\App\Services\Demostracion\RegistroDemostracion::class);
        $perfiles = $registro->activo() ? app(\App\Services\Demostracion\PresentacionDemostracion::class)->perfiles($registro)->keyBy('id') : collect();
        $guias = AgendaGuias::guias()->orderBy('personas.nombre')->get()->mapWithKeys(fn ($guia) => [
            $guia->id => ['nombre' => trim($guia->nombre.' '.$guia->apellido_paterno.' '.$guia->apellido_materno), 'foto' => $biblioteca->guia($guia->id)['id'] ?? null,
                'url' => $biblioteca->guia($guia->id)['url'] ?? $perfiles->get($guia->id)['fotografia'] ?? asset('imagenes/perfil.svg'), 'publicado' => $perfiles->has($guia->id)],
        ])->all();

        return ['revision' => $biblioteca->revision(), 'fotos' => $biblioteca->todas(true), 'albumes' => config('fotografias.albumes'),
            'espacios' => $espacios, 'servicios' => $servicios, 'guias' => $guias,
            'asignaciones' => ['espacios' => $estado['espacios'] ?? [], 'servicios' => $estado['servicios'] ?? [], 'guias' => $estado['guias'] ?? []],
            'paginas' => [
                'inicio' => ['nombre' => 'Página principal', 'url' => route('inicio')],
                'catalogo' => ['nombre' => 'Experiencias y sus fotografías', 'url' => route('publico.catalogo')],
                'guias' => ['nombre' => 'Equipo de guías', 'url' => route('publico.guias')],
                'comunidades' => ['nombre' => 'Territorio y comunidades', 'url' => route('publico.comunidades')],
                'nosotros' => ['nombre' => 'Nuestra esencia', 'url' => route('publico.nosotros')],
                'metodologia' => ['nombre' => 'Turismo regenerativo', 'url' => route('publico.metodologia')],
                'consultoria' => ['nombre' => 'Talleres y cursos', 'url' => route('publico.consultoria')],
                'operadora' => ['nombre' => 'Operadora turística', 'url' => route('publico.operadora')],
                'galeria' => ['nombre' => 'Galería publicada', 'url' => route('publico.galeria')],
            ]];
    }
}
