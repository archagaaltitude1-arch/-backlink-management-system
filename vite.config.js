import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Manrope', {
                    weights: [400, 500, 600, 700, 800],
                    optimizedFallbacks: false,
                }),
                bunny('Ubuntu', {
                    weights: [400, 500, 700],
                    optimizedFallbacks: false,
                }),
                bunny('Montserrat', {
                    weights: [500, 600, 700],
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
