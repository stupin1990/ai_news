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

export default defineConfig(({ mode }) => {
    const isDevProfile = mode === 'dev' || mode === 'development';

    return {
        esbuild: {
            sourcemap: isDevProfile,
        },
        css: {
            devSourcemap: isDevProfile,
        },
        plugins: [
            laravel({
                input: ['resources/react/app.tsx'],
                refresh: ['resources/**'],
            }),
            tailwindcss(),
            react(),
            devStatusLogger(),
        ],
        build: {
            sourcemap: isDevProfile,
        },
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
                ignored: (filePath) => {
                    const normalizedPath = filePath.replace(/\\/g, '/');

                    return !normalizedPath.includes('/resources/');
                },
            },
        },
    };
});
