<?php

namespace App\Http\Controllers;

use App\Services\Contenido\BibliotecaFotografica;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class FotografiaPublicaController extends Controller
{
    public function mostrar(string $foto, BibliotecaFotografica $biblioteca)
    {
        $ficha = $biblioteca->foto($foto, true);
        abort_unless($ficha && ! empty($ficha['subida']) && ($ficha['publicada'] || Gate::allows('administrar')), 404);
        abort_unless(preg_match('~^fotografias-sitio/foto-[a-f0-9-]+\.(jpg|png|webp)$~D', $ficha['archivo']), 404);
        $disco = Storage::disk('local');
        abort_unless($disco->exists($ficha['archivo']), 404);

        return response()->file($disco->path($ficha['archivo']), ['Content-Type' => $disco->mimeType($ficha['archivo']),
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => $ficha['publicada'] ? 'public, max-age=3600' : 'private, no-store']);
    }
}
