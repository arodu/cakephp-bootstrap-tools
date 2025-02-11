import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    root: './', // Carpeta raíz del proyecto
    build: {
        outDir: 'webroot', // Carpeta donde se guardarán los archivos compilados
        emptyOutDir: true, // Limpia la carpeta de salida antes de compilar
        rollupOptions: {
            input: {
                mainJs: path.resolve(__dirname, 'resources/scripts/main.js'),
                mainCss: path.resolve(__dirname, 'resources/styles/main.scss')
            },
            output: [
                // Salida sin minificar
                {
                    dir: 'webroot',
                    entryFileNames: 'js/[name].js',       // Archivo sin minificar
                    assetFileNames: 'css/[name].css',    // Archivo sin minificar
                    chunkFileNames: 'js/[name].js',      // Archivo sin minificar
                    format: 'es',
                    plugins: [] // No aplicar ningún plugin de minificación
                },
                // Salida minificada
                {
                    dir: 'webroot',
                    entryFileNames: 'js/[name].min.js',  // Archivo minificado
                    assetFileNames: 'css/[name].min.css',// Archivo minificado
                    chunkFileNames: 'js/[name].min.js', // Archivo minificado
                    format: 'es',
                    plugins: [
                        {
                            name: 'minify',
                            renderChunk(code) {
                                // Aquí puedes usar Terser u otro minificador
                                return code; // Por defecto, Rollup ya minifica en producción
                            }
                        }
                    ]

                }
            ]
        }
    }
});