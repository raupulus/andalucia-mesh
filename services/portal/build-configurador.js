// ==============================================================================
// build-configurador.js (services/portal/)
//
// Script de compilación para empaquetar configurador.js en un único bundle
// JavaScript autónomo (sin dependencias externas de CDN) y distribuirlo
// tanto en portal como en la integración independiente integrations/meshconfig/.
// ==============================================================================

import { build } from 'vite';
import { copyFileSync, existsSync, mkdirSync } from 'node:fs';
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

async function bundleConfigurador() {
  console.log('Compilando configurador.js autónomo con Vite...');
  
  const tempOut = resolve(__dirname, 'resources/configurador/dist');
  
  await build({
    configFile: false,
    plugins: [nodeShimsPlugin()],
    define: {
      'process.env.NODE_ENV': JSON.stringify('production'),
      'process.env': '{}',
      'process.version': '""',
      'process.versions': '{}',
      'process.platform': '"browser"',
      'process.cwd': '(() => "")',
      'Buffer.isBuffer': '((obj) => Boolean(obj && (obj._isBuffer || (obj.constructor && obj.constructor.isBuffer && obj.constructor.isBuffer(obj)))))',
    },
    build: {
      lib: {
        entry: resolve(__dirname, 'resources/configurador/src/configurador.js'),
        name: 'Configurador',
        formats: ['es'],
        fileName: () => 'configurador.js'
      },
      rollupOptions: {
        output: {
          banner: 'if (typeof window !== "undefined") { window.global = window.global || window; window.process = window.process || { env: { NODE_ENV: "production" }, version: "", versions: {}, platform: "browser", cwd: function() { return ""; } }; if (!window.Buffer) { window.Buffer = { isBuffer: function(obj) { return Boolean(obj && (obj._isBuffer || (obj.constructor && obj.constructor.isBuffer && obj.constructor.isBuffer(obj)))); }, from: function(arr) { return new Uint8Array(arr); }, alloc: function(s) { return new Uint8Array(s); } }; globalThis.Buffer = window.Buffer; } }',
        }
      },
      outDir: tempOut,
      emptyOutDir: true,
      minify: true,
    }
  });

  const bundledFile = resolve(tempOut, 'configurador.js');
  const targetPortal = resolve(__dirname, 'resources/configurador/custom/configurador.js');
  const targetMeshconfig = resolve(__dirname, '../../integrations/meshconfig/custom/configurador.js');

  copyFileSync(bundledFile, targetPortal);
  console.log(`✓ Copiado a ${targetPortal}`);

  if (existsSync(dirname(targetMeshconfig))) {
    copyFileSync(bundledFile, targetMeshconfig);
    console.log(`✓ Copiado a ${targetMeshconfig}`);
  }

  console.log('¡Compilación de configurador.js completada con éxito!');
}

bundleConfigurador().catch((err) => {
  console.error('Error al compilar configurador.js:', err);
  process.exit(1);
});
