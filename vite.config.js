import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',           //lubab telefoni ligipääsu
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: '192.168.0.226', //sinu IP (väga oluline!)
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});