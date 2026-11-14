import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/app.css',
                'resources/css/administracion.css',
                'resources/js/app.js',
                'resources/js/administracion.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
