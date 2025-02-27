import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    root: './',
    build: {
        outDir: 'webroot',
        minify: false,
        emptyOutDir: false,
        rollupOptions: {
            input: {
                'bst-script': path.resolve(__dirname, 'resources/scripts/main.js'),
                'bst-style': path.resolve(__dirname, 'resources/styles/main.scss')
            },
            output: [
                {
                    dir: 'webroot',
                    entryFileNames: 'js/[name].js',
                    assetFileNames: 'css/[name][extname]',
                    chunkFileNames: 'js/[name].js',
                    format: 'es',
                },
            ],
        }
    }
});