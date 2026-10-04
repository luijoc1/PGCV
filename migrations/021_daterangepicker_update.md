# Calendario de ventas actualizado conservando el diseño

Paso 86, 3 de octubre de 2026. Daterangepicker pasa de la copia manual
2.1.25 a `daterangepicker` 3.1.0, fijado en npm. Versión verificada en
el registro oficial y en las [publicaciones del autor](https://github.com/dangrossman/daterangepicker/releases).
No requiere Bootstrap 3: utiliza jQuery 3.7.1 y Moment 2.31.0 existentes.

## Recursos y consumidor

El único inicializador propio está en `admin/sales.php`, para el campo
`reservation` del formulario de reporte. Seleccionar fechas actualiza ese
campo; no filtra la tabla ni genera un reporte hasta enviar el formulario.
No se cambian el endpoint de impresión, las ventas ni su estado.

- `package.json` y `package-lock.json` fijan Daterangepicker 3.1.0.
- `tools/sync_frontend_libraries.mjs` copia JavaScript y CSS oficiales a
  `bower_components/daterangepicker/`, con metadatos y licencia MIT extraída
  del README del paquete, que no publica un archivo LICENSE separado.
- Administración carga ese JavaScript y el CSS propio con `filemtime`.
  El CSS oficial se conserva como referencia, sin cargarlo encima del tema.
- Fuente `build/scss/pgcv-daterangepicker.scss`, derivada del CSS antiguo
  conservando autor y licencia. Se adapta `.calendar` a `.drp-calendar`,
  las flechas de navegación a Font Awesome y la clase de apertura superior.
  `node tools/build_daterange_styles.mjs` genera
  `dist/css/pgcv-daterangepicker.min.css`. También lo ejecuta `sync:frontend`.
  No editar directamente el CSS generado.
- `dist/js/pgcv-sales-dates.js`, cargado únicamente en ventas, usa la opción
  `template` de 3.1 para mantener los dos campos superiores y los botones
  habituales. Conserva las etiquetas existentes Apply/Cancel, color verde,
  calendario horizontal en escritorio y apilado en móvil. Los campos tienen
  nombres accesibles en español.
- La adaptación mantiene los campos sincronizados con el método
  `updateFormInputs` de 3.1 y usa `updateView` al editar fechas. Estas dos
  llamadas internas deben revisarse si se actualiza otra vez el plugin.
  La sincronización no modifica los archivos oficiales del paquete.

El formato sigue siendo **`MM/DD/YYYY - MM/DD/YYYY`**, contrato que interpreta
`includes/sales_report.php` como mes/día/año. No cambiarlo por día/mes/año
sin actualizar también el contrato del servidor. Las fechas imposibles o
invertidas en los campos internos deshabilitan Aplicar; Enter actualiza
los calendarios y Escape cancela, restaurando el rango anterior.

La copia `bower_components/bootstrap-daterangepicker/` permanece conservada,
bloqueada por su `.htaccess` y excluida del paquete del hosting. No se borran
fuentes históricas ni respaldos. La retirada de esas copias es otro paso.

## Comprobaciones específicas

Se utilizó una vista PHP temporal, limitada a GET/local, con las plantillas
administrativas reales, identidad ficticia y el formulario copiado desde
ventas. No importaba configuración, sesión ni base de datos. Los envíos de
formulario estaban interceptados. Permitió comparar antes/después y se
retiró al terminar.

En escritorio de 1280 px coincidieron posición, tamaño, tipografía, bordes
y colores de calendario, ambos meses, tabla, celdas y botones. El campo y
el botón de impresión conservaron sus medidas; la transición del borde al
enfocar se comparó una vez asentada. El calendario mide aproximadamente
612 × 250 px, y los botones 51 × 30 y 56 × 30 px.

En móvil de 375 px se conservaron los calendarios apilados, aproximadamente
278 × 521 px. Se corrigió un desbordamiento previo de tres píxeles mediante
un margen derecho de nueve píxeles, sin alterar las medidas. La vista
temporal pasó de un ancho de documento de 378 a 375 px.

Comprobaciones realizadas sin enviar el formulario:

- Selección por celdas del 5 al 10 de octubre, con Aplicar deshabilitado
  mientras faltaba la fecha final y salida `10/05/2026 - 10/10/2026`.
- Edición manual por Tab, Enter y clic en Aplicar: rango entre septiembre
  y octubre, `09/28/2026 - 10/02/2026`.
- Fecha imposible `02/30/2026`: `aria-invalid=true` y Aplicar deshabilitado.
- Cancelar y Escape después de modificar fechas: campo principal conserva
  el rango anterior. Navegación anterior/siguiente y reapertura sin duplicados.
- Función real `salesReportRange`, ejecutada aisladamente sin PDO: interpreta
  los dos rangos y obtiene los límites exclusivos del 11 y 3 de octubre.
- Página autenticada `admin/sales.php`: recursos actuales cargados, selección
  5–10 de octubre, aplicación, apertura móvil y cancelación. Calendario dentro
  del ancho disponible; no se pulsaron Impresión, estado, detalles ni PDF.
- Consola sin errores propios de PGCV. Sintaxis PHP 8.4 de los tres archivos
  modificados, sintaxis Node del adaptador/generador/sincronizador,
  sincronización y `git diff --check` correctos. JavaScript copiado idéntico
  al archivo npm. Permanecen los dos avisos conocidos Sass por `@import`
  en las bases pública y administrativa; el calendario no añade avisos.
- Auditoría npm completa, incluidas dependencias de desarrollo: cero
  vulnerabilidades, 34 dependencias. No cubre bibliotecas fuera de npm.

Evidencias privadas: `storage/backups/daterangepicker-review/` contiene CSS
anterior, métricas y auditoría; capturas
`storage/visual-review/daterangepicker-after-desktop.png` y
`daterangepicker-after-mobile.png`, sin datos de clientes. Excluirlas de
Git y hosting. Tamaño del navegador restaurado y pestañas creadas cerradas.

No se abrió ni recargó `admin/home.php`, imprimieron reportes, confirmaron
pedidos, enviaron correos ni guardaron registros. No se añadieron pruebas
permanentes, repitieron suites amplias, crearon commits ni hicieron push.

## Reversión y siguientes pasos

Revertir conjuntamente referencias de encabezado/scripts, inicializador de
ventas, dependencia npm, sincronizador y estilos del calendario. La copia
antigua permanece intacta; una reversión local deliberada requiere retirar
su bloqueo HTTP. No servir simultáneamente ambos plugins ni ambos CSS.
Los estilos Sass y la adaptación propia solo deben cargarse con 3.1.

Siguen pendientes otros recursos copiados manualmente y la revisión final
del frontend. La entrega real de correo y la publicación requieren la
autorización específica ya documentada.
