<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        return $this->pantalla(new Categoria);
    }

    public function editar(Categoria $categoria): View
    {
        return $this->pantalla($categoria);
    }

    private function pantalla(Categoria $categoria): View
    {
        return view('administracion.categorias', [
            'categorias' => Categoria::withCount('servicios')->orderBy('nombre_categoria')->paginate(12),
            'categoriaEdicion' => $categoria,
        ]);
    }

    public function guardar(Request $request): RedirectResponse
    {
        return $this->persistir($request, new Categoria);
    }

    public function actualizar(Request $request, Categoria $categoria): RedirectResponse
    {
        return $this->persistir($request, $categoria);
    }

    private function persistir(Request $request, Categoria $categoria): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_categoria' => ['required', 'string', 'max:150', Rule::unique('categorias', 'nombre_categoria')->ignore($categoria->id)],
        ], ['nombre_categoria.unique' => 'Ya existe una categoría con ese nombre.']);

        try {
            $categoria->fill($datos)->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['nombre_categoria' => 'Ya existe una categoría con ese nombre.']);
        }

        return redirect()->route('administracion.categorias.indice')->with('estado', 'La categoría se guardó correctamente.');
    }
}
