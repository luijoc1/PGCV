# Revisión visual de productos, usuarios y categorías

Paso 91, 4 de octubre de 2026. Comprobación específica de listas y modales
administrativos tras la migración de estilos. Se conserva el diseño habitual.

## Corrección aplicada

El selector nativo de foto tenía un ancho de unos 301.20 px, mayor que su
columna en pantallas de 320 px. En el alta de producto la columna disponible
medía 247.61 px y el contenido del modal desbordaba: 316 px de contenido
frente a 278 px de ancho. En el alta de usuario la columna medía 270.41 px
y el modal también tenía 316 px de contenido frente a 300 px de ancho.

La fuente `build/scss/pgcv-admin.scss` limita a `max-width: 100%` los campos
de archivo dentro de modales. El control nativo conserva su aspecto y su
ancho habitual cuando hay espacio; el navegador abrevia el texto del nombre
de archivo cuando hace falta. No cambia la lógica de subida ni las plantillas.
Se regeneró `dist/css/pgcv-admin.min.css` con
`node tools/build_admin_styles.mjs`, sin editar directamente el resultado.
La compilación pasa con el aviso conocido de Sass por `@import`.

Después del ajuste, el campo mide 247.61 px en producto y 270.41 px en
usuario a 320 px. Ambos modales dejan de desbordar horizontalmente.
Los botones Cerrar y Guardar conservan posición y tamaño en estos casos.
En escritorio de 1280 px, campos y botones de ambas altas mantienen
exactamente sus medidas y posiciones anteriores; el campo de foto sigue
midiendo 301.20 px. No se trata de un rediseño.

## Alcance de la comprobación

Se utilizó una vista temporal accesible únicamente por GET desde localhost,
con los cuerpos reales de `admin/products.php`, `admin/users.php` y
`admin/category.php`, sus encabezados, menú, modales, pie y scripts.
No importaba configuración de base ni iniciaba sesión persistente.
La conexión ficticia admitía únicamente SELECT sobre registros inventados:
20 productos, 20 usuarios y siete categorías. El helper de consulta de
productos se sustituyó en la vista por un adaptador ficticio: esta revisión
no acredita el filtro SQL de categoría.

Las consultas AJAX de filas y categorías devolvían respuestas ficticias;
otros destinos se rechazaban. Formularios y enlaces fuera de la vista
estaban bloqueados. No se guardaron datos, escogieron archivos, cambiaron
estados, pulsaron acciones finales de borrado ni abrieron carritos reales.

Se comprobaron la lista de productos a 375 y 320 px, la de usuarios a
320 y 1280 px y la de categorías a 375 y 1280 px. Los controles de tabla
permanecen dentro de su contenedor, con desplazamiento horizontal en móvil.
En productos, Nuevo y el selector de categoría caben en el encabezado.
Las altas de productos y usuarios se revisaron a 1280 y 320 px; el alta
de producto también a 375 px antes del ajuste. Los modales de edición
se abrieron con filas ficticias: producto a 375 y 1280 px, usuario a
320 px y categoría a 375 px. Campos, editor y botones quedan dentro
del ancho del modal; los formularios largos requieren desplazamiento vertical.
El alta de categoría conserva su disposición a 375 y 1280 px.
Se comprobó el cierre por X y Escape en las vistas revisadas.

Consola sin errores ni avisos. Las capturas completas de modales largos
en móvil fallaron por tiempo de espera de la herramienta; se utilizaron
capturas recortadas del campo de foto y métricas DOM. Las capturas de
escritorio y del modal corto de categoría sí se obtuvieron completas.
La revisión usa tamaños de navegador, no un dispositivo móvil físico.

Evidencias privadas en `storage/visual-review/`: `admin-product-photo-before-320.png`,
`admin-product-photo-after-320.png`, `admin-user-photo-after-320.png`,
`admin-product-new-desktop.png`, `admin-user-new-desktop.png`,
`admin-category-new-mobile.png` y `admin-products-list-mobile.png`.
Las métricas están en `storage/backups/admin-visual-review/metrics.json`.
Estas carpetas permanecen excluidas de Git y del hosting.

Sintaxis PHP 8.4 de la vista temporal, compilación y `git diff --check`
correctos. No se añadieron pruebas permanentes, repitieron suites generales
ni ejecutó otra auditoría npm: no cambian dependencias. La última auditoría
del paso 89 informó cero vulnerabilidades en 37 dependencias, sin cubrir
recursos fuera de npm.

La vista temporal se retiró y se comprobó HTTP 404; se restauró el tamaño
del navegador y se cerró la pestaña creada. No se abrió ni recargó
`admin/home.php`, confirmaron pedidos, enviaron correos, borraron respaldos,
crearon commits o hicieron push.

## Reversión y continuación

Para revertir únicamente este ajuste, retirar la regla de ancho máximo
del campo de archivo y recompilar la hoja administrativa. Conservar las
demás reglas de las migraciones anteriores. Esta fase acredita presentación
con datos ficticios, no persistencia de formularios ni subida real de fotos.
Continúa la revisión por casos concretos en las pantallas restantes.
