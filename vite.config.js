import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // Bloque agregado para solucionar la conexión con Live Share
    server: {
        host: true,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
    },
});