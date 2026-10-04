import * as sass from 'sass';
import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
export async function buildThemeStyles() {
  const manifest = JSON.parse(await readFile(path.join(root, 'package.json'), 'utf8'));
  const installed = JSON.parse(await readFile(path.join(root, 'node_modules/sass/package.json'), 'utf8'));
  if (installed.version !== manifest.devDependencies.sass) {
    throw new Error('Unexpected Sass version; run npm ci --ignore-scripts.');
  }
  const result = sass.compile(path.join(root, 'build/scss/pgcv-theme.scss'), {
    style: 'compressed'
  });
  await writeFile(path.join(root, 'dist/css/pgcv-theme.min.css'), result.css + '\n');
  console.log('Tema habitual de PGCV compilado sin estilos de plugins inactivos.');
}
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) await buildThemeStyles();
