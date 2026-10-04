# Tema principal compilado conservando el diseño

## Alcance

Los encabezados público y administrativo cargan `dist/css/pgcv-theme.min.css`
con `filemtime`, en la misma posición que antes ocupaba `AdminLTE.min.css`.
Se conserva el tema habitual: distribución, barra lateral, cabecera,
formularios, botones, cajas, tablas, modales, Select2, utilidades e impresión.
El tema azul continúa en su hoja separada de la [fase 017](017_pgcv_blue_skin.md).

Es una adaptación de las reglas existentes de AdminLTE 2.4.0, con autor y
licencia MIT conservados. No instala otra versión de AdminLTE ni propone
un rediseño. La referencia utilizada fue el CSS minificado que realmente
cargaban las páginas, para conservar también sus declaraciones exactas.

La fuente `build/scss/pgcv-theme.scss` reúne tres módulos mantenibles:

- `theme/_layout-components.scss`: distribución y componentes.
- `theme/_select2.scss`: adaptación visual del selector utilizado en administración.
- `theme/_utilities-print.scss`: colores, utilidades y reglas de impresión.

Se retiraron únicamente los bloques de Bootstrap Social, FullCalendar y
Datepicker, sin consumidores encontrados en las plantillas o JavaScript
propio. Datepicker ya no se cargaba desde la fase 074; Daterangepicker es
otro plugin y conserva su hoja e inicialización. Las bibliotecas antiguas,
fuentes LESS y distribuciones anteriores permanecen para su limpieza
coordinada posterior. La nueva fuente no importa Bootstrap 3 ni LESS.

## Compilación

```powershell
node tools/build_theme_styles.mjs
```

El generador comprueba la versión fijada de Sass, genera la salida
minificada y está integrado en `npm run sync:frontend`. No editar el CSS
generado. El hosting necesita las hojas compiladas, sin Node ni Sass.
No cambiaron dependencias npm en esta fase.

El archivo anterior mide 106.347 bytes y el nuevo 82.165 bytes. Después
de excluir exclusivamente los tres bloques retirados y normalizar con
Sass, las reglas conservadas son idénticas: 81.830 caracteres en ambas
versiones, sin comentarios. No se modificó la ruta relativa de la imagen
del modo boxed; la salida permanece en `dist/css`.

## Comprobaciones y límites

Al comenzar no había listener MariaDB en el puerto 3306. No se arrancó ni
reinició el servicio. Se compararon dos vistas temporales locales GET con
encabezados, navegación y scripts reales, categorías y cuenta ficticias,
sin sesión persistente, configuración, base o correo. Formularios
interceptados y `getCart` público neutralizado antes de la inicialización.

Coinciden exactamente las nueve zonas administrativas y cinco públicas
medidas en escritorio de 1280 px y móvil de 375 px CSS. Se comprobaron
filtro de categorías, apertura/cierre del modal y navegación pública con
sus siete categorías; el contenido comienza debajo del encabezado abierto.

Se incluyó temporalmente el modal real `admin/includes/cart_modal.php`
en la vista aislada, con una opción ficticia Suzuki. Campo Select2,
buscador, desplegable, diálogo y pie conservan medidas, colores y bordes.
La búsqueda encuentra la opción y Escape cierra el diálogo/desplegable;
no se seleccionó ni añadió un producto ni se envió el formulario. No se
encontraron diferencias visuales nuevas en las vistas comprobadas.

Sin errores propios PGCV en las consolas revisadas; se excluyeron dos
errores procedentes de una extensión del navegador. Pasa compilación,
sintaxis Node del generador/sincronizador, PHP 8.4 de los dos encabezados y
`git diff --check`. No se añadieron pruebas permanentes ni se repitieron
suites o auditorías npm. Esta revisión no acredita todas las páginas,
panel real, AJAX con datos reales, carrito/facturación o impresión.

Ambas vistas temporales retiradas, tamaño del navegador restaurado y
pestañas creadas cerradas. Evidencias privadas: `theme-select2-mobile.png`
y `theme-metrics.json` en `storage/visual-review/`, y referencia CSS en
`storage/backups/pgcv-theme-review/`. Las métricas JSON de revisión visual
quedan también ignoradas por Git; excluir todos estos recursos del hosting.
No se abrió ni recargó `admin/home.php`, guardaron datos, confirmaron
pedidos, enviaron correos, crearon commits o hicieron push.

## Reversión y siguiente fase

Restaurar en ambos encabezados la carga `AdminLTE.min.css`, que se conserva
en su ruta anterior. No requiere restaurar datos. Los estilos nuevos
conservan clases y reglas del tema heredado; su procedencia y limitaciones
siguen siendo relevantes aunque cambie el nombre del archivo servido.

Quedan revisar y retirar las fuentes, copias y consumidores de desarrollo
antiguos, eliminar Bootstrap 3 del manifiesto/sincronizador cuando ya no
sea necesario y ejecutar la auditoría final. El aviso de Bootstrap 3
continúa pendiente; esta fase no lo declara resuelto.
