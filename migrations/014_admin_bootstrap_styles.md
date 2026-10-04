# Estilos administrativos Bootstrap 5 con el diseño habitual

## Alcance

Esta guía registra el paso 79. La fase posterior de
[DataTables](015_datatables_bootstrap5.md) sustituye núcleo y adaptador
antiguos; las referencias siguientes conservan el contexto de esta fase.

El encabezado administrativo carga `dist/css/pgcv-admin.min.css`, compilado
sobre Bootstrap 5.3.8. Sustituye el enlace a Bootstrap 3 CSS y la hoja
transitoria de la fase [013](013_admin_bootstrap_javascript.md), que se retira.
El CSS nuevo se versiona con `filemtime` para evitar una copia antigua en caché.

Se conservan las hojas de AdminLTE 2, skins, Select2, Daterangepicker y el
adaptador antiguo de DataTables. Esta adaptación de PGCV conserva el marcado
y diseño actual; no equivale a migrar AdminLTE o actualizar DataTables.
Bootstrap 3 permanece provisionalmente en el manifiesto y sincronizador:
su retirada de dependencias y recursos requiere el siguiente inventario de
consumidores, incluidos las vistas locales y las fuentes Less anteriores.
El aviso moderado de npm sigue pendiente; no se ejecutó otra auditoría.

## Fuente y compilación

Fuente: `build/scss/pgcv-admin.scss`. Distribución generada:
`dist/css/pgcv-admin.min.css`. No editar directamente el archivo generado.

```powershell
node tools/build_admin_styles.mjs
```

El compilador comprueba las versiones instaladas de Bootstrap 5 y Sass
contra el manifiesto antes de escribir el CSS. `npm run sync:frontend`
también compila ahora los estilos administrativos, además de los públicos.
No se modificó la fuente ni el CSS generado público en esta fase.
La compilación administrativa pasa con un aviso conocido de Sass por
`@import`; no se modifica la distribución upstream.

Se siguen los mecanismos de personalización documentados en las guías
oficiales de [Sass](https://getbootstrap.com/docs/5.3/customize/sass/) y
[rejilla](https://getbootstrap.com/docs/5.3/layout/grid/) de Bootstrap.

## Conservación del diseño y correcciones concretas

- Tipografía de 14 px, colores, breakpoints 768/992/1200 px y separaciones
  de 30 px. El flujo de columnas conserva la distribución anterior y las
  clases `col-xs-*`, incluidas las tarjetas del panel que se inspeccionaron
  mediante lectura de código.
- Cabecera, logo, barra lateral, navegación, breadcrumbs, pestañas, botones,
  formularios horizontales, etiquetas y modales conservan sus medidas.
- Se adaptan inputs agrupados y formularios en línea. La primera revisión
  detectó el rango de ventas demasiado ancho, desplazando Impresión debajo
  en escritorio; la regla del addon devuelve ambos a la misma fila.
- Las tablas conservan relleno, color y bordes anteriores. Se eliminaron
  los bordes adicionales de filas/secciones y el borde superior de cabecera
  introducidos por las diferencias de base CSS.
- Ordenación e impresión usan Font Awesome ya instalado, eliminando su
  dependencia visual de Glyphicons. No se cambia el envío del reporte.
- Un caso concreto al pasar de escritorio a móvil dejaba cabecera y cuerpo
  de DataTables con columnas distintas. La copia utilizada para medir la
  cabecera mantiene ahora `white-space: nowrap` también en móvil. Se conserva
  el desplazamiento interno; ambos botones se alcanzan mediante teclado.

## Verificación específica

Se comparó categorías antes/después con la sesión administrativa existente,
en 1280 px y 375 px CSS efectivos. En escritorio las nueve muestras finales
(navbar, logo, encabezado, caja, contenedor de tabla, región de desplazamiento,
primera celda, botón y paginación) coinciden en tamaño, posición, color y
relleno con la referencia anterior. El formulario conserva tamaño, campos
y botones. Se comprobaron filtro y ordenación.

Se revisaron también, sin guardar formularios:

- Inventario: cuatro columnas en escritorio y tarjetas apiladas en móvil.
- Productos: formulario en columnas, disposición móvil, editor/foco, botones
  inferiores y paginación de 1–10 a 11–20 de 44 registros.
- Usuarios: campos y botones dentro del modal móvil; cierre sin guardar.
- Logs: pestañas, teclado y desplazamiento interno de la tabla ancha.
- Ventas: rango/cancelación, icono y alineación de impresión, consulta de una
  transacción existente con total $580,000.00 y tabla dentro del modal.
- Carrito administrativo: búsqueda «Suzuki», foco y dropdown dentro del
  modal; Escape cerró selector y modal sin añadir artículos.
- Descuentos: botones dentro de la vista y cancelación de la confirmación.
- Menú lateral y desplegable del perfil: apertura y cierre en móvil.

La última revisión reprodujo la desalineación de columnas, verificó su
corrección tras regresar de escritorio a móvil y desplazó la tabla hasta
su extremo derecho con ArrowRight: unos 43 px CSS; cabecera y cuerpo
acompañan el desplazamiento y los botones quedan visibles.

No se abrió ni recargó `admin/home.php`, no se confirmó ningún pedido,
imprimió reporte, descargó factura o envió correo. No se acredita una
revisión visual del panel real, todos los modales, interacción táctil en
dispositivo físico o flujos de escritura. No se detectaron errores propios
de PGCV en la consola de las páginas comprobadas.

Pasan compilación Sass, sintaxis Node de compilador/sincronizador, sintaxis
PHP 8.4 de encabezado/ventas y `git diff --check`. No se añadieron pruebas
ni se ejecutaron suites completas. Se restauró el tamaño del navegador y
se cerraron las dos pestañas creadas. No hubo commits ni push.

Capturas privadas con prefijo `admin-styles-` bajo `storage/visual-review/`,
incluidas las referencias de categorías y el resultado de columnas/scroll,
modales, productos, usuarios, Select2, inventario, logs y ventas. No incluir
esta evidencia en el paquete para hosting.

## Reversión y siguiente paso

Para revertir esta fase, restaurar juntos el encabezado con la base CSS
anterior y la hoja transitoria de la fase 013, el icono previo de impresión
y el sincronizador. El JavaScript Bootstrap 5 puede conservarse. No se
requiere restaurar la base de datos. Regenerar el CSS si se cambia la fuente.

Continúan pendientes AdminLTE y la integración DataTables, además de retirar
las fuentes, dependencias y recursos Bootstrap 3 que ya no tengan consumidor
y completar entonces la auditoría final.
