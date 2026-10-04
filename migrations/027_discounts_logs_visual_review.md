# Revisión visual de descuentos y actividad

Paso 92, 4 de octubre de 2026. Se revisaron `admin/ofertas.php` y
`admin/logs.php` en escritorio de 1280 px y móvil de 320 px, conservando
el diseño habitual. No se encontró un defecto visual que requiriera
cambiar estas plantillas, sus estilos o scripts.

## Descuentos

La tabla mantiene alineados producto, precio original, descuento, precio
final, stock y acción. Con datos ficticios de $150,000.00 y 15 %, presenta
$127,500.00; esta observación no repite la compra ni la comprobación real
del paso 48. Mostrar, Buscar y paginación caben en su contenedor.
En móvil la tabla mantiene desplazamiento horizontal sin desbordar la página.

Se abrió el modal desde Quitar descuento de una fila ficticia. En ambos
tamaños conserva encabezado, nombre del producto y botones. A 320 px el
modal mide aproximadamente 300.41 px; Cancelar mide 91.41 px y Sí, quitar
descuento 159.64 px, ambos en una fila y dentro del pie. Se cerró mediante
Cancelar. No se pulsó la confirmación ni se quitó ningún descuento real.

## Registro de actividad

Se abrieron Login, Productos, Ventas y Usuarios en ambos tamaños. El panel
y la pestaña seleccionada coinciden. Las pestañas caben en una fila en
escritorio y en dos filas a 320 px, sin solaparse. Campos de búsqueda,
selector de cantidad y paginación permanecen dentro de cada panel.

Las tablas visibles conservan cabeceras y filas alineadas después del
cambio de pestaña. En móvil los anchos de columnas coinciden; en escritorio
la última columna difiere menos de 0.04 px por redondeo de medidas, sin
desalineación visible. Las tablas anchas desplazan su contenido dentro del
contenedor, sin ensanchar la página. Se comprobó además flecha derecha de
Login a Productos: activa el panel correspondiente. Consola sin errores
ni avisos.

## Aislamiento y evidencias

Una vista temporal GET accesible únicamente desde localhost ejecutaba
los cuerpos reales de ambas plantillas con encabezados, menú, pie y
scripts compartidos. No importaba configuración ni iniciaba sesión
persistente. La conexión ficticia solo admitía SELECT y devolvía 20 filas
inventadas por tabla; correos de ejemplo `.invalid` e IP documental,
sin consultar registros privados. El cálculo de descuentos utilizaba
el helper real `includes/pricing.php`.

Formularios y navegación fuera de la vista estaban bloqueados, y cualquier
petición AJAX se rechazaba. No se enviaron formularios, consultaron carritos
reales, modificaron productos o registros, confirmaron pedidos ni enviaron
correos. Tampoco se abrió ni recargó `admin/home.php`.

Capturas privadas en `storage/visual-review/`: `discounts-desktop.png`,
`discounts-modal-desktop.png`, `discounts-modal-320.png`,
`discounts-table-320.png`, `logs-login-320.png`, `logs-products-320.png`
y `logs-users-desktop.png`. Métricas en
`storage/backups/secondary-visual-review/metrics.json`. No son recursos
para Git ni hosting. Una captura completa adicional de escritorio agotó
el tiempo de espera de la herramienta; las capturas previas y una captura
delimitada permitieron terminar la inspección. No se usó un móvil físico.

Sintaxis PHP 8.4 de la vista temporal y `git diff --check` correctos.
Sin cambios de runtime o dependencias, no se recompilaron estilos,
añadieron pruebas permanentes, repitieron suites ni ejecutó otra auditoría
npm. El último resultado del paso 89 sigue siendo cero vulnerabilidades
en 37 dependencias, sin cubrir recursos fuera de npm.

La vista temporal se retiró y se comprobó HTTP 404. Se restauró el tamaño
del navegador y se cerró la pestaña creada. No se borraron respaldos,
crearon commits, hicieron push ni publicó el proyecto. La revisión acredita
presentación con datos ficticios, no persistencia ni consulta de registros
reales. Continúa con ventas y detalle de transacciones por casos concretos.
