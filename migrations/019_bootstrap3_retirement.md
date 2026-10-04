# Retirada de Bootstrap 3 del runtime y de npm

## Cambios

Bootstrap 3.4.1 ya no está en `package.json`, `package-lock.json` ni en
`node_modules/bootstrap`. Se retiró mediante `npm uninstall bootstrap
--ignore-scripts --no-audit --no-fund`. El alias `bootstrap5` conserva
`npm:bootstrap@5.3.8`; no se actualizó ninguna otra biblioteca.

`tools/sync_frontend_libraries.mjs` deja de copiar Bootstrap 3 y de generar
`dist/js/bootstrap-pgcv.js`. Las páginas públicas y administrativas ya
utilizan Bootstrap 5 y los temas compilados descritos en las fases
[017](017_pgcv_blue_skin.md) y [018](018_pgcv_theme_styles.md).

La vista existente `tests/fixtures/bootstrap5_public_modals.php` usa ahora
únicamente los temas público y principal actuales, versionados mediante
`filemtime`. Se retiró la comparación opcional con el CSS Bootstrap 3.
No se añadió una prueba nueva ni se modificó el formulario de producción.

## Copias históricas conservadas y bloqueadas

La revisión automática rechazó borrar conjuntamente las carpetas antiguas,
porque incluían fuentes útiles para reversión y excedían una retirada
acotada. Esa eliminación no se ejecutó. Se adoptó la alternativa de
conservar los archivos originales y bloquear su acceso HTTP:

- `bower_components/bootstrap/`: toda la copia Bootstrap 3, incluidas sus
  fuentes, CSS, JavaScript, metadatos y licencia.
- `dist/js/bootstrap-pgcv.js`: distribución reducida histórica del paso 50.
- `build/less/` y `build/bootstrap-less/`: fuentes heredadas inactivas.
- `tests/fixtures/bootstrap_runtime.html`: comprobación histórica del
  runtime reducido, retirada del uso y conservada como antecedente.

El `.htaccess` de Bootstrap deniega toda su carpeta. Las reglas de la raíz
bloquean los otros recursos históricos. Se comprobó HTTP 403 para seis
rutas representativas; el bundle Bootstrap 5 y el tema principal actual
devuelven 200. Los archivos permanecen localmente y en Git, pero no deben
incluirse en el paquete del hosting. En otro servidor, aplicar las reglas
equivalentes mientras existan esas copias.

## Verificación y auditoría

`npm run sync:frontend` termina correctamente sin Bootstrap 3. Las cuatro
hojas regeneradas conservan exactamente sus hashes SHA-256 anteriores:
pública, administrativa, tema principal y tema azul. Persisten únicamente
los dos avisos conocidos de Sass por `@import` en las bases Bootstrap 5.

`npm ls --depth=0` confirma las versiones fijadas y ausencia del paquete
antiguo. Pasan sintaxis Node del sincronizador, PHP 8.4 de la vista de
modales y `git diff --check`.

La primera consulta de auditoría no pudo acceder al endpoint npm desde el
entorno restringido. La consulta posterior con acceso de red aprobado y
caché dentro del proyecto terminó con código 0. `npm audit --json
--ignore-scripts` informa **cero vulnerabilidades** de cualquier severidad,
incluidas las dependencias de desarrollo; el informe registra 32
dependencias totales. Informe y caché privados en `storage/backups/`.
No se ejecutó `npm audit fix`, instalaron actualizaciones sugeridas o
repitieron suites completas.

Este resultado resuelve el aviso npm moderado de Bootstrap 3 documentado
en el paso 50. No certifica toda la aplicación ni las bibliotecas copiadas
manualmente fuera del manifiesto; esos consumidores y versiones requieren
su revisión específica. El tema conserva reglas derivadas de AdminLTE 2,
no una actualización upstream de AdminLTE.

En la vista local existente, sin configuración, conexión a base o correo,
se verificaron apertura de modales de cuenta/transacción, cierre de cuenta
con Escape y controles visibles. El diálogo de cuenta conserva 600 px en
escritorio de 1280 px y unos 356 px en móvil de 375 px CSS. El DOM solo
carga recursos actuales; consola sin avisos/errores. No se envió ningún
formulario. No acredita transacciones AJAX, carrito/facturación real o
todas las páginas.

Captura privada: `storage/visual-review/bootstrap3-retirement-modal-mobile.png`.
Tamaño del navegador restaurado y pestaña creada cerrada. No se abrió ni
recargó `admin/home.php`, modificaron datos, confirmaron pedidos, enviaron
correos, crearon commits o hicieron push. No se borró ningún respaldo.

## Reversión y continuación

No hay migración de base de datos. Para revisar una reversión, restaurar
juntos manifiesto/lock y sincronizador anteriores, reconstruir dependencias
y revisar las reglas HTTP. No volver a cargar Bootstrap 3 junto a Bootstrap
5 en la misma página. Las copias de archivos anteriores se conservan en
sus rutas, pero desbloquearlas sería una acción separada que requiere una
razón concreta.

Quedan revisar los plugins copiados manualmente y sus consumidores,
completar la auditoría de esos recursos y comprobar las páginas reales que
corresponda. La eliminación física de las copias históricas no se realizó;
su conservación y exclusión del hosting permiten continuar la preparación
sin ejecutar la eliminación rechazada.
