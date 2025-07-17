            import { defineConfig } from 'vite';
            import laravel from 'laravel-vite-plugin';
            import react from '@vitejs/plugin-react';

            export default defineConfig({
                plugins: [
                    laravel({
                        input: 'resources/js/app.jsx',
                        refresh: true,
                    }),
                    react(),
                ],
                base: '/build/',
                build: {
                    outDir: 'public/build',
                    assetsDir: 'assets',
                    manifest: 'manifest.json',
                    rollupOptions: {
                        output: {
                            assetFileNames: 'assets/[name]-[hash][extname]',
                            chunkFileNames: 'assets/[name]-[hash].js',
                            entryFileNames: 'assets/[name]-[hash].js',
                        },
                    },
                },
            });
