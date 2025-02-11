import { defineConfig } from 'vite';
import path from 'path';
import terser from '@rollup/plugin-terser'; // Plugin de minificación para JavaScript

export default defineConfig({
    root: './',
    build: {
        outDir: 'webroot',
        emptyOutDir: true,
        minify: true,
        rollupOptions: {
            input: {
                'bst-script': path.resolve(__dirname, 'resources/scripts/main.js'),
                'bst-style': path.resolve(__dirname, 'resources/styles/main.scss')
            },
            output: [
                {
                    // Salida sin minificar
                    dir: 'webroot',
                    entryFileNames: 'js/[name].js',
                    assetFileNames: 'css/[name][extname]', // unified naming pattern
                    chunkFileNames: 'js/[name].js',
                    format: 'es',
                    plugins: []
                },
                {
                    // Salida minificada
                    dir: 'webroot',
                    entryFileNames: 'js/[name].min.js',
                    assetFileNames: 'css/[name].min[extname]', // unified naming pattern
                    chunkFileNames: 'js/[name].min.js',
                    format: 'es',
                    plugins: [
                        terser(),
                    ]
                }
            ],
        }
    }
});