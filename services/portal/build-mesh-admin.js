// ==============================================================================
// build-mesh-admin.js (services/portal/)
//
// Script de compilación para empaquetar mesh-admin.js en un bundle IIFE autónomo
// (sin dependencias externas de CDN) listo para ejecutarse en la consola de
// gestión de routers de Filament de forma síncrona y port-agnóstica.
// ==============================================================================

import { build } from 'vite';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

async function bundleMeshAdmin() {
  console.log('Compilando mesh-admin.bundle.js autónomo con Vite (IIFE)...');

  await build({
    configFile: false,
    publicDir: false,
    define: {
      'process.env.NODE_ENV': JSON.stringify('production'),
      'process.env': '{}',
      'process.cwd': '(() => "/")',
      'process': '({ env: { NODE_ENV: "production" }, cwd: () => "/" })',
    },
    build: {
      lib: {
        entry: resolve(__dirname, 'resources/js/mesh-admin.js'),
        name: 'MeshAdminBundle',
        formats: ['iife'],
        fileName: () => 'mesh-admin.bundle.js'
      },
      rollupOptions: {
        output: {
          banner: 'if (typeof window !== "undefined") { window.process = window.process || { env: { NODE_ENV: "production" }, cwd: function() { return "/"; } }; if (!window.global) { window.global = window; } }',
        }
      },
      outDir: resolve(__dirname, 'public/js'),
      emptyOutDir: false,
      minify: true,
    }
  });

  console.log('✓ mesh-admin.bundle.js generado con éxito en public/js/mesh-admin.bundle.js');
}

bundleMeshAdmin().catch((err) => {
  console.error('Error al compilar mesh-admin.bundle.js:', err);
  process.exit(1);
});
