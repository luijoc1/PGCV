import * as sass from 'sass';
import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
export async function buildAdminStyles() {
  const manifest = JSON.parse(await readFile(path.join(root, 'package.json'), 'utf8'));
  for (const name of ['bootstrap5', 'sass']) {
    const expected = (manifest.dependencies?.[name] ?? manifest.devDependencies?.[name]).replace(/^npm:[^@]+@/, '');
    const installed = JSON.parse(await readFile(path.join(root, 'node_modules', name, 'package.json'), 'utf8'));
    if (installed.version !== expected) throw new Error(`Unexpected ${name} version; run npm ci --ignore-scripts.`);
  }
  let deprecations = 0;
  const result = sass.compile(path.join(root, 'build/scss/pgcv-admin.scss'), {
    loadPaths: [path.join(root, 'node_modules')], style: 'compressed', quietDeps: true,
    logger: {warn(message, options) { if (options.deprecation) deprecations++; else console.warn(message); }}
  });
  await writeFile(path.join(root, 'dist/css/pgcv-admin.min.css'), result.css + '\n');
  console.log(`Administrative Bootstrap 5 styles generated (${deprecations} Sass deprecation notices).`);
}
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) await buildAdminStyles();
