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

function nodeShimsPlugin() {
  return {
    name: 'node-shims',
    enforce: 'pre',
    resolveId(id) {
      if (id === 'os' || id === 'path' || id === 'util' || id === 'buffer') {
        return '\0' + id;
      }
    },
    load(id) {
      if (id === '\0os') {
        return 'export function hostname() { return "localhost"; }; export default { hostname };';
      }
      if (id === '\0path') {
        return 'export function normalize(p) { return String(p || ""); }; export default { normalize };';
      }
      if (id === '\0util') {
        return `
          export function formatWithOptions(options, ...args) {
            return args.map(a => {
              if (typeof a === "object" && a !== null) {
                try { return JSON.stringify(a); } catch (_) { return String(a); }
              }
              return String(a);
            }).join(" ");
          }
          export const types = {
            isNativeError: (e) => e instanceof Error,
          };
          export default { formatWithOptions, types };
        `;
      }
      if (id === '\0buffer') {
        return `
          const _Buffer = {
            isBuffer: (obj) => Boolean(obj && (obj._isBuffer || (obj.constructor && obj.constructor.isBuffer && obj.constructor.isBuffer(obj)))),
            from: (arr) => new Uint8Array(arr),
            alloc: (s) => new Uint8Array(s)
          };
          export const Buffer = (typeof window !== "undefined" && window.Buffer) ? window.Buffer : _Buffer;
          export default { Buffer };
        `;
      }
    }
  };
}

async function bundleMeshAdmin() {
  console.log('Compilando mesh-admin.bundle.js autónomo con Vite (IIFE)...');

  await build({
    configFile: false,
    publicDir: false,
    plugins: [nodeShimsPlugin()],
    define: {
      'process.env.NODE_ENV': JSON.stringify('production'),
      'process.env': '{}',
      'process.cwd': '(() => "/")',
      'process': '({ env: { NODE_ENV: "production" }, cwd: () => "/" })',
      'Buffer.isBuffer': '((obj) => Boolean(obj && (obj._isBuffer || (obj.constructor && obj.constructor.isBuffer && obj.constructor.isBuffer(obj)))))',
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
          banner: 'if (typeof window !== "undefined") { window.global = window.global || window; window.process = window.process || { env: { NODE_ENV: "production" }, cwd: function() { return "/"; } }; if (!window.Buffer) { window.Buffer = { isBuffer: function(obj) { return Boolean(obj && (obj._isBuffer || (obj.constructor && obj.constructor.isBuffer && obj.constructor.isBuffer(obj)))); }, from: function(arr) { return new Uint8Array(arr); }, alloc: function(s) { return new Uint8Array(s); } }; globalThis.Buffer = window.Buffer; } }',
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
