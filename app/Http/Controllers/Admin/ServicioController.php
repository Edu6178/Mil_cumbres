<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['activo', 'oculto'])],
            'categoria' => ['nullable', 'integer', 'exists:categorias,id'],
        ]);

        $servicios = Servicio::with('categoria')
            ->when($filtros['buscar'] ?? null, fn ($consulta, $texto) => $consulta->whereRaw('LOWER(nombre_servicio) LIKE ?', ['%'.mb_strtolower($texto).'%']))
            ->when($filtros['estado'] ?? null, fn ($consulta, $estado) => $consulta->where('estado_servicio', $estado))
            ->when($filtros['categoria'] ?? null, fn ($consulta, $categoria) => $consulta->where('categoria_id', $categoria))
            ->latest()->paginate(12)->withQueryString();

        return view('administracion.servicios.indice', [
            'servicios' => $servicios, 'categorias' => Categoria::orderBy('nombre_categoria')->get(),
            'total' => Servicio::count(), 'activos' => Servicio::where('estado_servicio', 'activo')->count(),
        ]);
    }

    public function crear(): View
    {
        return $this->formulario(new Servicio(['estado_servicio' => 'activo']));
    }

    public function editar(Servicio $servicio): View
    {
        return $this->formulario($servicio);
    }

    public function guardar(Request $request): RedirectResponse
    {
        $servicio = Servicio::create($this->validar($request));

        return redirect()->route('administracion.servicios.indice')
            ->with('estado', 'El servicio se creó correctamente.')->with('servicio_reciente', $servicio->id);
    }

    public function actualizar(Request $request, Servicio $servicio): RedirectResponse
    {
        // Cambiar la tarifa base no modifica los importes pactados de reservaciones existentes.
        $servicio->update($this->validar($request));

        return redirect()->route('administracion.servicios.indice')->with('estado', 'Los cambios del servicio se guardaron correctamente.');
    }

    public function visibilidad(Request $request, Servicio $servicio): RedirectResponse
    {
        $datos = $request->validate(['estado_servicio' => ['required', Rule::in(['activo', 'oculto'])]]);
        // Ocultar conserva los vínculos con regiones, programaciones y reservaciones.
        $servicio->update($datos);

        return back()->with('estado', $datos['estado_servicio'] === 'oculto' ? 'El servicio se ocultó y conserva su historial.' : 'El servicio se activó correctamente.');
    }

    private function formulario(Servicio $servicio): View
    {
        return view('administracion.servicios.formulario', [
            'servicio' => $servicio, 'categorias' => Categoria::orderBy('nombre_categoria')->get(),
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre_servicio' => ['required', 'string', 'max:200'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'descripcion' => ['nullable', 'string', 'max:10000'],
            'precio_base' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'duracion_estimada' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
            'itinerario' => ['nullable', 'string', 'max:20000'],
            'estado_servicio' => ['required', Rule::in(['activo', 'oculto'])],
        ]);
    }
}
