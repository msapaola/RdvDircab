import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
            buildDirectory: 'build', // Spécifier le dossier de build
        }),
        react(),
    ],
    build: {
        outDir: 'public/build', // Définir le dossier de sortie
        assetsDir: 'assets', // Définir le dossier des assets
        manifest: true, // Générer le manifest
        rollupOptions: {
            output: {
                assetFileNames: 'assets/[name]-[hash][extname]',
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
            },
        },
    },
});
