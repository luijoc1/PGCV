# Revisión visual de ventas y detalle de transacciones

Paso 93, 4 de octubre de 2026. Se revisaron la tabla de `admin/sales.php`
y el modal compartido de detalle en escritorio de 1280 px y móvil de
320 y 375 px. Se conserva el diseño habitual.

## Aviso histórico corregido

`admin/transact.php` añadía el aviso de venta antigua en una fila sin
la clase `prepend_items`. El cierre del modal retiraba las líneas de
producto que sí tenían esa clase, pero dejaba el aviso. Al abrir dos
veces una venta antigua ficticia aparecían dos avisos; al abrir después
una venta con instantánea seguían esos dos avisos incorrectos.

La fila del aviso recibe ahora la misma clase que las demás filas
temporales. El manejador de cierre existente la retira sin añadir otro
manejador ni modificar la plantilla o el texto visible. Se conserva la
advertencia sobre precios estimados en ventas antiguas. No se reconstruyen
instantáneas ni se cambian consultas, importes, descuentos o totales.
También se actualizó un comentario obsoleto del modal que aún mencionaba
Bootstrap 3; el código ya utilizaba Bootstrap 5.

Comprobación específica con el renderer real y datos ficticios:

- Antes: segunda apertura antigua, dos avisos; apertura posterior con
  instantánea, dos avisos y cinco filas, incluyendo el total.
- Después: cierre de venta antigua, cero avisos y solo la fila del total;
  segunda apertura antigua, un aviso y cuatro filas; cambio a venta con
  instantánea, cero avisos y tres filas. Cierre por botón y Escape comprobado.
- Dos productos de cantidad uno conservan $127,500.00 después de un
  descuento del 15 % sobre $150,000.00 y $80,000.00 sin descuento.
  Total guardado ficticio $207,500.00, intacto en todas las aperturas.

## Presentación y controles

Rango de fechas y botón Impresión caben en el encabezado móvil. La tabla
mantiene las nueve columnas visibles y la columna interna oculta; permite
desplazamiento horizontal dentro del contenedor. Los botones Ver y PDF
y los cuatro estilos de estado conservan sus medidas y distribución.
Se pasó por teclado de Ver a Estado y luego PDF sin cambiar selección
ni activar enlaces. La lectura final a 375 px mostró Estado y PDF dentro
del contenedor, con los diez estados visibles intactos.

El detalle conserva título, fecha, transacción, productos, precios y botón
Cerrar. A 375 px el modal mide aproximadamente 355.61 px y su región de
tabla 325.61 px; el contenido más ancho se desplaza dentro de esa región,
sin ensanchar el modal. A 320 px también conserva su distribución.
No cambian estilos ni recursos, por lo que no se recompiló Sass.

## Aislamiento y evidencias

La vista temporal solo admitía GET desde localhost. No importaba
configuración ni iniciaba sesión persistente. Ejecutaba el cuerpo real
de ventas, incluido su bloque de estilos, encabezado, menú, pie, modal
y scripts, con veinte ventas ficticias mediante una conexión que solo
admitía SELECT. Para las dos respuestas de detalle se ejecutó el bloque
real de `admin/transact.php`, omitiendo el guard de sesión en esta vista
aislada y utilizando el helper real de historial y consultas ficticias.
Una respuesta tenía instantánea y la otra no. Los identificadores,
personas y datos de facturación eran inventados.

Las peticiones AJAX al detalle se sustituían por esas respuestas locales;
cualquier otro destino se rechazaba. Formularios y enlaces fuera de la
vista estaban bloqueados. No se cambió ningún estado, imprimió un informe,
descargó una factura, confirmó un pedido o envió correo. No se abrió ni
recargó `admin/home.php`. Esta comprobación acredita presentación y limpieza
del modal, no una consulta real a la base ni la generación de PDF.

Capturas privadas en `storage/visual-review/`: `sales-table-desktop.png`,
`sales-table-320.png`, `transaction-stale-notices-before-320.png`,
`transaction-legacy-after-desktop.png` y `transaction-snapshot-after-320.png`.
Métricas en `storage/backups/sales-visual-review/metrics.json`; ambas
carpetas están excluidas de Git y del hosting. Una captura a 375 px agotó
el tiempo de espera de la herramienta; ese tamaño se verificó con métricas
DOM. Una captura adicional a 320 px también agotó el tiempo de espera;
su evidencia final se obtuvo mediante un recorte delimitado del modal.
Se obtuvieron capturas a 320 px y escritorio. No se usó un móvil físico.

Sintaxis PHP 8.4 de `admin/transact.php`, modal y vista temporal, y
`git diff --check` correctos. Sin pruebas permanentes, suites generales
ni auditoría npm repetida: no cambian dependencias. La última auditoría
del paso 89 informó cero vulnerabilidades en 37 dependencias y no cubre
recursos fuera de npm.

La vista temporal se retiró con HTTP 404 comprobado. Se restauró el
tamaño del navegador y se cerró la pestaña creada. No se borraron
respaldos, crearon commits, hicieron push ni publicó el proyecto.

Para revertir solo esta corrección, retirar la clase del aviso histórico.
La revisión visual continúa por casos concretos en el carrito administrativo.
