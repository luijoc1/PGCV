import { copyFile, mkdir, readFile, readdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { buildPublicStyles } from './build_public_styles.mjs';
import { buildAdminStyles } from './build_admin_styles.mjs';
import { buildSkinStyles } from './build_skin_styles.mjs';
import { buildThemeStyles } from './build_theme_styles.mjs';
import { buildDateRangeStyles } from './build_daterange_styles.mjs';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const manifest = JSON.parse(await readFile(path.join(root, 'package.json'), 'utf8'));
const libraries = {
  jquery: ['dist', 'src'],
  'datatables.net': ['js/dataTables.min.js'],
  'datatables.net-bs5': ['js/dataTables.bootstrap5.min.js', 'css/dataTables.bootstrap5.min.css'],
  'chart.js': ['dist/chart.umd.min.js', 'dist/chart.umd.min.js.map'],
  jodit: ['es2021/jodit.min.js', 'es2021/jodit.min.css'],
  select2: ['dist/js/select2.full.min.js', 'dist/css/select2.min.css', 'dist/js/i18n/es.js'],
  daterangepicker: ['daterangepicker.js', 'daterangepicker.css'],
  sweetalert2: ['dist/sweetalert2.all.min.js'],
  magnify: ['dist/js/jquery.magnify.js', 'dist/css/magnify.css'],
  'font-awesome': ['css/font-awesome.min.css', 'fonts/fontawesome-webfont.eot',
    'fonts/fontawesome-webfont.svg', 'fonts/fontawesome-webfont.ttf',
    'fonts/fontawesome-webfont.woff', 'fonts/fontawesome-webfont.woff2'],
  bootstrap5: ['dist/js/bootstrap.bundle.min.js', 'dist/js/bootstrap.bundle.min.js.map'],
  moment: ['locale', 'moment.js', 'min/locales.js', 'min/locales.min.js', 'min/moment.min.js',
    'min/moment-with-locales.js', 'min/moment-with-locales.min.js',
    'min/moment.min.js.map', 'min/moment-with-locales.min.js.map']
};

async function copyTree(source, target) {
  await mkdir(target, { recursive: true });
  for (const entry of await readdir(source, { withFileTypes: true })) {
    const input = path.join(source, entry.name);
    const output = path.join(target, entry.name);
    if (entry.isDirectory()) await copyTree(input, output);
    else if (entry.isFile()) await copyFile(input, output);
  }
}

// Check all installed versions before replacing any served assets.
for (const name of Object.keys(libraries)) {
  const installed = JSON.parse(await readFile(path.join(root, 'node_modules', name, 'package.json'), 'utf8'));
  const expectedVersion = manifest.dependencies[name].replace(/^npm:[^@]+@/, '');
  if (installed.version !== expectedVersion) {
    throw new Error(`Unexpected ${name} version. Run npm ci --ignore-scripts first.`);
  }
}

// Compile the base styles, shared theme and blue skin before copying libraries.
await buildPublicStyles();
await buildAdminStyles();
await buildThemeStyles();
await buildSkinStyles();
await buildDateRangeStyles();

for (const [name, assets] of Object.entries(libraries)) {
  const source = path.join(root, 'node_modules', name);
  const target = path.join(root, 'bower_components', name === 'select2' ? 'select2-v4' : name);
  await mkdir(target, { recursive: true });
  for (const asset of assets) {
    if (['dist', 'src', 'locale'].includes(asset)) await copyTree(path.join(source, asset), path.join(target, asset));
    else {
      await mkdir(path.dirname(path.join(target, asset)), { recursive: true });
      await copyFile(path.join(source, asset), path.join(target, asset));
    }
  }
  if (name.startsWith('datatables.net') || ['select2', 'daterangepicker', 'sweetalert2', 'magnify', 'font-awesome'].includes(name)) {
    await writeFile(path.join(target, 'pgcv-assets.json'), JSON.stringify({
      name, version: manifest.dependencies[name],
      source: `npm:${name}@${manifest.dependencies[name]}`, assets
    }, null, 2) + '\n');
  }
  for (const filename of ['.bower.json', 'bower.json', 'package.json']) {
    const destination = path.join(target, filename);
    try {
      const metadata = JSON.parse(await readFile(destination, 'utf8'));
      metadata.version = manifest.dependencies[name];
      delete metadata._resolution;
      delete metadata._release;
      metadata.pgcvAssetSource = `npm:${name}@${manifest.dependencies[name]}`;
      await writeFile(destination, JSON.stringify(metadata, null, 2) + '\n');
    } catch (error) {
      if (error.code !== 'ENOENT') throw error;
    }
  }
  // Preserve the upstream license for redistributed runtime files.
  if (name === 'font-awesome') {
    // El paquete publica los avisos de licencia en README, no en LICENSE.
    const readme = await readFile(path.join(source, 'README.md'), 'utf8');
    const start = readme.indexOf('## License');
    const end = readme.indexOf('\n## ', start + 1);
    if (start < 0 || end < 0) throw new Error('Font Awesome license notice missing.');
    await writeFile(path.join(target, 'LICENSE-NOTICE.md'), readme.slice(start, end) + '\n');
  }
  if (name === 'daterangepicker') {
    // Este paquete publica la licencia MIT dentro de README, sin archivo LICENSE separado.
    const readme = await readFile(path.join(source, 'README.md'), 'utf8');
    const marker = 'The MIT License (MIT)';
    const start = readme.indexOf(marker);
    if (start < 0) throw new Error('Daterangepicker license notice missing.');
    await writeFile(path.join(target, 'LICENSE'), readme.slice(start));
  }
  for (const license of ['LICENSE', 'LICENSE.txt', 'LICENSE.md']) {
    try { await copyFile(path.join(source, license), path.join(target, license)); }
    catch (error) { if (error.code !== 'ENOENT') throw error; }
  }
  console.log(`${name} ${manifest.dependencies[name]}: runtime assets synchronized`);
}
