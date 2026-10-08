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

async function bundleConfigurador() {
  console.log('Compilando configurador.js autónomo con Vite...');
  
  const tempOut = resolve(__dirname, 'resources/configurador/dist');
  
  await build({
    configFile: false,
    build: {
      lib: {
        entry: resolve(__dirname, 'resources/configurador/src/configurador.js'),
        name: 'Configurador',
        formats: ['es'],
        fileName: () => 'configurador.js'
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
