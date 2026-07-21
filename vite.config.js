import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const host = '192.168.0.127';

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

    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,

        origin: `http://${host}:5173`,

        cors: {
            origin: `http://${host}:8000`,
            credentials: true,
        },

        hmr: {
            host: host,
            port: 5173,
            clientPort: 5173,
            protocol: 'ws',
        },
    },
});