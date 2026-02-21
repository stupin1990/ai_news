import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

function devStatusLogger() {
    return {
        name: 'dev-status-logger',
        apply: 'serve',
        handleHotUpdate(context) {
            const timestamp = new Date().toLocaleTimeString('ru-RU', { hour12: false });
            const relativePath = context.file.replace(context.server.config.root + '/', '');

            console.log(`[${timestamp}] changed: ${relativePath}`);

            context.server.ws.send({
                type: 'custom',
                event: 'dev:hmr-finished',
                data: { file: relativePath, timestamp },
            });

            setTimeout(() => {
                console.log(`[${timestamp}] hmr done: ${relativePath}`);
            }, 0);
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/react/app.tsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
        devStatusLogger(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            usePolling: true,
            interval: 100,
            binaryInterval: 300,
            ignored: [
                '**/.git/**',
                '**/node_modules/**',
                '**/vendor/**',
                '**/storage/framework/views/**',
                '**/storage/logs/**',
            ],
        },
    },
});
