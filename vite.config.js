import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ command, mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devHost = env.VITE_DEV_HOST || '127.0.0.1';

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
        ],

        server: command === 'serve'
            ? {
                  host: '0.0.0.0',
                  port: 5173,
                  strictPort: true,
                  origin: `http://${devHost}:5173`,
                  cors: true,
                  hmr: {
                      host: devHost,
                      port: 5173,
                      clientPort: 5173,
                      protocol: 'ws',
                  },
              }
            : undefined,
    };
});