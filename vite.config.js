import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Compila los recursos públicos y administrativos.
            input: [
                'resources/sass/app.scss',
                'resources/css/app.css',
                'resources/css/publico.css',
                'resources/css/administracion.css',
                'resources/js/app.js',
                'resources/js/publico.js',
                'resources/js/administracion.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});