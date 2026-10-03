import { copyFile, mkdir, readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const directory = path.dirname(fileURLToPath(import.meta.url));
const target = path.resolve(directory, '../../tests/fixtures/frontend-v5/assets');
const manifest = JSON.parse(await readFile(path.join(directory, 'package.json'), 'utf8'));
const files = {
  bootstrap: ['dist/css/bootstrap.min.css', 'dist/js/bootstrap.bundle.min.js', 'LICENSE'],
  'admin-lte': ['dist/css/adminlte.min.css', 'dist/js/adminlte.min.js', 'LICENSE'],
  'datatables.net': ['js/dataTables.min.js', 'License.txt'],
  'datatables.net-bs5': ['js/dataTables.bootstrap5.min.js', 'css/dataTables.bootstrap5.min.css', 'License.txt']
};

// Check all packages before copying any file. This preview has its own lock
// and output directory; the application's active assets are never overwritten.
for (const name of Object.keys(files)) {
  const installed = JSON.parse(await readFile(path.join(directory, 'node_modules', name, 'package.json'), 'utf8'));
  if (installed.version !== manifest.dependencies[name]) throw new Error(`Unexpected ${name} version; run npm ci --ignore-scripts.`);
}
for (const [name, resources] of Object.entries(files)) {
  await mkdir(path.join(target, name), { recursive: true });
  for (const resource of resources) {
    await copyFile(path.join(directory, 'node_modules', name, resource), path.join(target, name, path.basename(resource)));
  }
}
console.log('Bootstrap/AdminLTE preview assets synchronized; application assets unchanged.');
