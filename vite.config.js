import { defineConfig } from 'vite';
import path from 'path';
import terser from '@rollup/plugin-terser';
import postcss from 'rollup-plugin-postcss'


export default defineConfig({
    root: './',
    build: {
        outDir: 'webroot',
        emptyOutDir: true,
        minify: false,
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
                    plugins: []
                },
                {
                    dir: 'webroot',
                    entryFileNames: 'js/[name].min.js',
                    assetFileNames: 'css/[name].min[extname]',
                    chunkFileNames: 'js/[name].min.js',
                    format: 'es',
                    plugins: [
                        terser(),
                        postcss({
                            plugins: []
                        }),
                    ]
                }
            ],

        }
    }
});