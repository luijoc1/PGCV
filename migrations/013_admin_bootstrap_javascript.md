# JavaScript administrativo de Bootstrap 5 conservando el diseño

## Alcance

Esta guía conserva el contexto de la primera fase JavaScript. La fase
[014](014_admin_bootstrap_styles.md) reemplaza después la base CSS
administrativa y retira la hoja transitoria descrita a continuación.

Administración carga ahora el mismo bundle local de Bootstrap 5.3.8 con
Popper utilizado por la tienda. Se retiró de sus plantillas la carga de
`dist/js/bootstrap-pgcv.js`; no se cargan ambos runtimes en una página.
No se cambiaron las dependencias npm ni se ejecutó otra auditoría.

Esta es una fase transitoria propia de PGCV, no una combinación certificada
por Bootstrap. Se conservan la base CSS de Bootstrap 3, AdminLTE 2, skins,
Select2 y la integración antigua de DataTables. Bootstrap 3 sigue en el
manifiesto y el sincronizador porque todavía proporciona los estilos
administrativos. Su aviso moderado continúa pendiente de eliminación.

## Cambios

- Modales, desplegable del perfil, pestañas y cierres de alertas utilizan
  `data-bs-*`. Se mantiene `data-toggle="push-menu"`, perteneciente a AdminLTE.
- Las aperturas propias usan `bootstrap.Modal.getOrCreateInstance(...).show()`.
  Los eventos jQuery `shown.bs.modal`, `hidden.bs.modal` y `shown.bs.tab`
  conservan los ajustes de tablas y la limpieza existente.
- Los modales tienen `tabindex="-1"` y nombre tomado de su título. Los cierres
  mantienen la X habitual y su etiqueta accesible está en español.
- Las pestañas de logs tienen roles y relaciones accesibles. El estado
  activo pasa del contenedor al enlace, conforme al runtime nuevo, con
  navegación mediante flechas, Home y End.
- Select2 dentro de un modal usa ese modal como `dropdownParent`. El campo
  de búsqueda queda dentro del control de foco de Bootstrap sin desactivarlo.
- `dist/css/bootstrap5-admin-compat.css` adapta únicamente los estados
  `show`, transiciones, desplegable y pestaña activa a los estilos existentes.
  Se versiona con `filemtime`. No es un archivo generado y no reemplaza ni
  modifica el CSS público compilado.

Se conservan tamaños, colores, formularios, identificadores de datos, CSRF,
consultas y cálculos. En escritorio Bootstrap 5 centra el diálogo de 600 px;
la compensación anterior de la barra de desplazamiento lo colocaba unos
11 px a la derecha. En móvil las medidas del modal de categorías coinciden:
unos 356 px de ancho y 226.5 px de alto, con margen de 10 px.

Referencias oficiales consultadas:
[API y eventos JavaScript](https://getbootstrap.com/docs/5.3/getting-started/javascript/),
[modales](https://getbootstrap.com/docs/5.3/components/modal/),
[desplegables](https://getbootstrap.com/docs/5.3/components/dropdowns/) y
[pestañas](https://getbootstrap.com/docs/5.3/components/navs-tabs/).

## Comprobaciones específicas

Revisión con la sesión administrativa existente y MariaDB disponible,
entrando directamente en cada página. No se abrió ni recargó `admin/home.php`.
Ese archivo solo recibió la conversión de los tres cierres de alertas y se
comprobó mediante lectura y sintaxis, sin ejecutar sus scripts de correo.

En escritorio de 1280 px y móvil de 375 px CSS efectivos:

- Categorías: comparación del alta antes/después; cierre con Escape y botón
  inferior, recuperación del foco al abrir desde el enlace y edición por AJAX.
- Menú lateral: apertura y submenú Usuarios. Perfil: desplegable, cierre con
  Escape, apertura del formulario y cierre sin guardar. Se comprobó después
  de finalizar la transición, como requiere la API asíncrona de Bootstrap.
- Logs: las cuatro pestañas mediante clic/flechas/Home/End, un panel activo
  cada vez, ajuste de columnas y estilo del enlace seleccionado.
- Ventas: consulta AJAX de una venta existente, total $580,000.00, cierre del
  modal y apertura/cancelación del selector de fechas. No se imprimió ni se
  descargó factura o cambió el estado.
- Productos: alta, foco en el editor Jodit y cierre inferior sin guardar.
- Usuarios: alta y cierre con Escape sin guardar.
- Carrito administrativo: apertura, búsqueda «Suzuki», foco dentro del modal,
  selección temporal y cierre sin pulsar Guardar. No se añadió ningún artículo.
- Descuentos: apertura y cancelación de la confirmación, botones dentro de
  la vista móvil. No se retiró ningún descuento.

No se detectaron errores propios de la aplicación en las páginas revisadas.
El navegador registró un error con origen `chrome-extension://` durante
Select2; no procede de recursos de PGCV. No se acredita una prueba táctil
en dispositivo físico, todos los modales o los flujos de escritura.

Pasan sintaxis PHP 8.4 de los archivos PHP modificados, sintaxis de 14
bloques propios de JavaScript y `git diff --check`. El análisis JavaScript
sustituyó las expresiones PHP por `null`; no ejecutó los endpoints. No se
añadieron pruebas ni se repitieron suites completas. Se restauró el tamaño
del navegador y se cerró la pestaña creada. Sin pedidos, correos, commits o push.

Capturas privadas bajo `storage/visual-review/`:
`admin-bootstrap5-before-mobile.png`, `admin-bootstrap5-before-desktop.png`,
`admin-bootstrap5-after-mobile.png`, `admin-bootstrap5-after-desktop.png`,
`admin-bootstrap5-select2-mobile.png` y `admin-bootstrap5-offers-mobile.png`.
No incluir esta evidencia local en el paquete para hosting.

## Reversión y continuación

Para revertir esta fase, restaurar juntos el script administrativo anterior,
atributos, aperturas de modales, pestañas y encabezado; retirar la hoja
transitoria. No requiere restaurar datos ni cambiar las dependencias.

Queda migrar la base CSS administrativa, AdminLTE y DataTables conservando
el diseño. Solo después de retirar los consumidores de estilos Bootstrap 3
se debe eliminar su paquete, generación y recursos servidos y completar la
auditoría final. La guía [012](012_admin_frontend_inventory.md) conserva el
inventario inicial y los pasos previos.
