<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PerfilController extends Controller
{
    public function index(Request $request): View
    {
        return view('administracion.perfil', ['usuario' => $request->user()]);
    }

    public function guardarFoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2000,max_height=2000'],
        ], [
            'foto.file' => 'Selecciona un archivo de imagen válido.',
            'foto.image' => 'Selecciona una imagen válida.',
            'foto.mimes' => 'La foto debe ser JPG, PNG o WebP.',
            'foto.max' => 'La foto no debe superar los 2 MB.',
            'foto.dimensions' => 'La foto debe medir como máximo 2000 × 2000 píxeles.',
        ]);

        // La foto pertenece a users y se guarda en privado sin agregar columnas ni usar el nombre enviado.
        $guardada = Storage::disk('local')->put(
            'perfiles/'.$request->user()->id.'/foto',
            file_get_contents($request->file('foto')->getRealPath()),
        );
        if (! $guardada) {
            throw ValidationException::withMessages(['foto' => 'No fue posible guardar la foto. Inténtalo de nuevo.']);
        }

        return redirect()->route('administracion.perfil')->with('estado', 'Tu foto de perfil se actualizó.');
    }

    public function foto(Request $request): BinaryFileResponse
    {
        // La ruta solo entrega la foto de la sesión actual, nunca archivos de otros usuarios.
        $disco = Storage::disk('local');
        $ruta = 'perfiles/'.$request->user()->id.'/foto';
        abort_unless($disco->exists($ruta), 404);

        return response()->file($disco->path($ruta), [
            'Content-Type' => $disco->mimeType($ruta),
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
