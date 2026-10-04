# Inventario previo de administración y plugins

Revisión del código local: 3 de octubre de 2026. Este paso documenta los
consumidores actuales; no cambia recursos, plantillas ni dependencias.

## Condiciones de la migración

Conservar el diseño habitual: cabecera, menú lateral, colores, tipografía,
tarjetas, tablas y formularios. No retomar la propuesta de rediseño revertida
en el paso 68. La X del aviso de carrito está terminada en el paso 72.

No abrir ni recargar `admin/home.php` en el navegador: su código solicita
`enviar_alerta_stock.php` automáticamente. Leer su fuente no ejecuta esa
solicitud. Las comprobaciones no deben confirmar pedidos ni enviar correos.

## Estado consolidado después del paso 97

La tabla y el orden de trabajo siguientes conservan el inventario inicial
del paso 73. No describen las versiones activas actuales. Bootstrap 3 y su
aviso npm quedaron resueltos en el paso 84; DataTables y plugins principales
están migrados y las revisiones visuales concretas de las guías 025–032 están
completadas. Se mantienen el diseño derivado de AdminLTE 2 y las copias
históricas locales inactivas, sin exigir su eliminación para preparar hosting.

El [estado previo al hosting](033_predeployment_status.md), paso 98,
distingue tareas completadas, límites y pendientes de paquete/destino,
correo y cobro. No vuelve a abrir el descuento real ni la X aceptada del
aviso de carrito. Los resultados de auditoría son los ya documentados,
sin una nueva ejecución en este cierre.

## Consumidores encontrados en el inventario inicial

| Recurso local | Carga y uso encontrados | Tratamiento pendiente |
| --- | --- | --- |
| Bootstrap 3.4.1 | CSS en `admin/includes/header.php`; runtime reducido en `admin/includes/scripts.php`. Alertas, dropdown del perfil, modales y pestañas de logs usan atributos antiguos. Productos, usuarios, categorías, carrito, ofertas y ventas llaman a `.modal('show')`. | Convertir atributos, llamadas y estilos juntos; no cargar dos runtimes en una página. |
| AdminLTE 2.4.0 | Tema y skins administrativos; JavaScript compartido también con la tienda. `navbar.php` usa `data-toggle="push-menu"` y `menubar.php`, `data-widget="tree"`. | Conservar menú y distribución; comprobar ambos consumidores antes de sustituir el código compartido. |
| DataTables 1.10.16 y adaptador Bootstrap 3 | Carga pública y administrativa; inicialización compartida en sus respectivos `includes/scripts.php`. Administración utiliza `scrollX`, traducción y ajuste de columnas al abrir modales. | Migrar núcleo y adaptador de forma coordinada, verificando controles, ordenación, filtro, paginación y desplazamiento horizontal. |
| Select2 4.0.4 | Carga administrativa global; `.select2` inicializado en scripts compartidos. Control encontrado en `admin/includes/cart_modal.php`. | Preservar selección de producto y comportamiento dentro del modal. |
| Daterangepicker 2.1.25 y Moment 2.31.0 | Carga administrativa global. `admin/sales.php` contiene `#reservation`, enviado como `date_range` a `sales_print.php`. | Conservar selector y formato enviado al reporte. No retirar por confundirlo con los selectores ausentes. |
| Bootstrap Datepicker 1.7.1 | Carga administrativa global. Ventas inicializa `#datepicker_add` y `#datepicker_edit`, sin controles correspondientes encontrados en plantillas de aplicación. | Candidato a retirar del runtime junto con esas inicializaciones. |
| Bootstrap Timepicker | CSS/JS globales desde `plugins/timepicker`. Ventas inicializa `.timepicker`, sin control correspondiente encontrado en plantillas de aplicación. | Candidato a retirar del runtime junto con su inicialización. No se atribuye una versión que la cabecera local no declara. |
| SlimScroll 1.3.8 | Carga pública y administrativa. AdminLTE lo consulta y utiliza en `Layout.fixSidebar`, incluido el modo de distribución fija. | Mantener hasta comprobar los modos de distribución usados; no considerarlo sobrante solo por ausencia de llamadas PHP propias. |
| FastClick | Carga pública y administrativa; no se encontró `FastClick.attach` en código propio ni referencia en `dist/js/adminlte.js`. | Evaluar retirada de la carga con comprobación de interacción móvil. |
| Jodit 4.17.1, jQuery 3.7.1 y Chart.js 4.5.1 | Versiones fijadas en npm. Jodit usa el adaptador propio de edición; jQuery soporta AJAX, CSRF y plugins; el panel utiliza Chart.js. | Conservar estos consumidores y sus funciones durante la transición. |

Los números anteriores identifican los archivos instalados; no afirman que
sean versiones recomendadas o las últimas disponibles. No se consultó el
registro ni se repitió una auditoría de dependencias en este paso.

En ventas también se inicializan `#reservationtime` y `#daterange-btn`, sin
elementos correspondientes encontrados. `dist/js/pages/dashboard.js`
contiene ejemplos de calendario y rango, pero las plantillas PHP revisadas
no lo cargan; el panel usa su gráfico propio. Conservar los archivos copiados
no implica que sus ejemplos formen parte del runtime de PGCV.

El botón de impresión de ventas todavía usa `glyphicon glyphicon-print`.
El adaptador DataTables anterior también depende de estilos Bootstrap 3;
no basta con reemplazar el enlace a la hoja base administrativa.

## Orden de trabajo propuesto

1. Retirar únicamente las cargas de Datepicker y Timepicker y las
   inicializaciones que apuntan a controles ausentes. Mantener el selector
   real `#reservation`, Moment y el formulario de reporte. Comprobar ventas
   y apertura de modales antes de dar este cambio por terminado.
2. Revisar FastClick por separado y conservar SlimScroll hasta verificar
   la distribución. No mezclar esta limpieza con el cambio de tablas.
3. Preparar la transición de Bootstrap administrativo con referencias
   visuales de categorías, usuarios, productos, ventas, ofertas y logs.
   Comparar escritorio y 375 px, teclado, menú lateral, dropdown, modales,
   pestañas y selector de producto, sin guardar formularios.
4. Convertir DataTables y el tema compartido conservando sus medidas y
   controles; revisar también el historial público. Seleccionar versiones
   y documentar sus fuentes oficiales en la fase de implementación.
5. Retirar Bootstrap 3 del manifiesto, sincronizador y recursos servidos
   solo después de eliminar sus consumidores. Ejecutar entonces la
   auditoría final; el aviso moderado documentado sigue pendiente.

Cada fase necesita una comprobación concreta y su documentación en español.
No repetir suites completas ni añadir pruebas sin un caso que lo justifique.
Para sintaxis PHP usar `storage/backups/php84-apache/runtime/php.exe`.
No editar el CSS público generado ni borrar `storage/backups`.

## Alcance de esta revisión

Se inspeccionaron fuentes y cargas con búsquedas estáticas, sin ejecutar PHP,
acceder a la base ni navegar por páginas reales. La ausencia de selectores
en las plantillas revisadas es evidencia para una limpieza acotada, no una
validación funcional en navegador. En ese inventario inicial no se modificó
el runtime ni se ejecutaron suites o crearon commits. La aplicación posterior
se describe a continuación.

## Primera limpieza aplicada y revisión de ventas

Se retiraron las cargas globales CSS/JS de Datepicker y Timepicker de
`admin/includes/header.php` y `admin/includes/scripts.php`. En ventas se
retiraron las inicializaciones sin controles correspondientes. Se conserva
`#reservation`, Daterangepicker, Moment y el formulario de reporte sin
cambiar su formato ni destino. Los archivos copiados de los plugins se
conservan; esta fase retira sus cargas del runtime administrativo.

La revisión encontró un fallo concreto: `admin/sales.php` incluía la plantilla
de perfil público, migrada a Bootstrap 5, pero abría su modal con Bootstrap 3.
Los botones con `data-bs-dismiss` dejaban el modal abierto. Ventas incluye
ahora `admin/includes/transaction_modal.php`, con los mismos campos de
transacción, tabla, apariencia e identificadores usados por AJAX y cierres
`data-dismiss` compatibles con su runtime. También evita incorporar un
formulario de perfil de cliente a la página administrativa. La plantilla
pública no se modifica.

Se renderizó temporalmente el marcado de ventas y las plantillas reales con
datos ficticios, sin configuración, base, sesión persistente ni correo.
La vista estaba bajo `tests/fixtures`, restringida por `Require local`;
interceptaba formularios y enlaces, y sustituyó las interacciones AJAX de
ventas por apertura local del modal. Por ello no verifica el endpoint de
transacción ni operaciones de escritura.

Se reprodujo el fallo antes de corregirlo. Después se comprobaron botón
Cerrar, X y Escape; apertura del rango y cancelación; filtro de tabla y
disposición en escritorio y 375 px CSS efectivos, compensando el zoom
existente sin cambiarlo. El documento mide 375 px, el calendario cabe y el
modal permanece dentro de la vista. La tabla conserva desplazamiento
horizontal interno y el botón Impresión permanece visible bajo el campo
en móvil. No se detectaron otros botones desplazados en esta vista de ventas.
La consola de la vista no registró avisos ni errores.

La sesión administrativa real estaba cerrada: abrir directamente ventas
redirigió a la portada. No se inició sesión, porque el flujo habitual vuelve
al panel que solicita alertas. La revisión autenticada de ventas y las otras
pantallas administrativas queda pendiente; no se presenta esta comprobación
aislada como revisión completa de administración.

Se retiraron el renderizador y la vista temporales y se restauró el tamaño
del navegador. Evidencia privada en
`storage/visual-review/admin-sales-modal-after-mobile.png`. Pasan sintaxis
PHP 8.4 de los cuatro archivos de aplicación modificados, sintaxis Node de
los bloques propios de ventas y `git diff --check`. No se añadieron pruebas
permanentes ni se repitieron suites completas. No se confirmaron pedidos,
enviaron correos, crearon commits ni hicieron push.

## Revisión posterior con sesión administrativa

Con la sesión ya disponible se entró directamente en ventas, sin abrir el
panel. El AJAX de una venta existente cargó sus dos productos, descuento y
total; se verificaron cierre inferior, X y Escape. La celda del total y los
importes original/final con descuento utilizan `text-nowrap`, una clase del
Bootstrap administrativo actual, para mantener cada símbolo e importe en
la misma línea. No se modifican cálculos ni precios.

Se inspeccionaron categorías (alta y edición), usuarios (alta), productos
(alta y editor) y la confirmación de quitar descuento, que se canceló.
Los formularios comprobados conservan sus campos y botones en escritorio y
móvil. La revisión no incluye enviar formularios, cambiar estados, descargar
facturas o imprimir reportes, ni acredita todos los modales administrativos.

En `admin/logs.php` se encontró un caso concreto: columnas recortadas en
móvil y controles en inglés. Las cuatro tablas usaban `responsive: true`
sin cargar la extensión correspondiente. Ahora usan `scrollX: true`, ancho
del 100 %, región interna enfocada por teclado y ajuste de columnas al
mostrar cada pestaña. La traducción ya usada en scripts administrativos se
establece también como valor predeterminado de DataTables, conservando el
contenido y consultas de las tablas.

Se verificaron las cuatro pestañas, desplazamiento por teclado, filtro sin
coincidencias/restauración y ordenación de ID. Login ocupa el ancho disponible
en escritorio y dispone de desplazamiento interno en móvil; el documento
permanece dentro de 375 px CSS. La sintaxis PHP 8.4, los bloques propios de
JavaScript y el diff pasan. No se añadieron pruebas ni se repitieron suites
o auditorías. No hubo pedidos, cambios de estado, guardado de datos ni correo.

Resultados detallados en el paso 75 del informe privado. Capturas locales:
`storage/visual-review/admin-logs-after-mobile.png` y
`storage/visual-review/admin-sales-total-after-desktop.png`. El tamaño del
navegador se restauró y la pestaña temporal se cerró. Cambios sin commit;
la migración global de Bootstrap/AdminLTE continúa pendiente.

## Retirada de FastClick de las cargas compartidas

Se retiraron las dos etiquetas de script de FastClick en los scripts públicos
y administrativos. La biblioteca no se conectaba automáticamente y no se
encontraron inicializaciones en las plantillas o distribuciones JavaScript
propias de la aplicación. Sus archivos copiados se conservan. SlimScroll
sigue cargado porque AdminLTE sí lo utiliza en la distribución lateral.

Se comprobó la ausencia de la carga y los controles en vistas locales
temporales con las plantillas y scripts reales, datos ficticios, formularios
y enlaces interceptados, sin sesión persistente, configuración, base o correo.
La vista pública sustituía AJAX antes de iniciar el carrito, sin solicitar
endpoints reales. En móvil se verificaron menú, categorías, Escape y modal
público; en administración, menú lateral, submenú, desplegable del perfil y
modal de categoría. No se observaron avisos/errores en esas vistas. No es una
comprobación táctil con un dispositivo físico.

El intento de entrar directamente en categorías reales encontró MariaDB
rechazando la conexión. No se modificó el estado del servidor ni se entró al
panel de alertas; falta repetir el recorrido administrativo real cuando la
base esté disponible. Los dos scripts de aplicación pasan sintaxis PHP 8.4
y el diff pasa comprobación. No se añadieron pruebas permanentes ni se
repitieron suites o auditorías porque no cambian dependencias.

Se retiraron las vistas temporales, se restauró el tamaño del navegador y se
cerraron las pestañas creadas. Captura privada:
`storage/visual-review/admin-without-fastclick-mobile.png`. Cambio documentado
en el paso 76, sin commits, push, pedidos o correo. No se modifica diseño,
CSS, lógica del carrito ni datos. Bootstrap 3/AdminLTE permanece pendiente.

## Comprobación real posterior con MariaDB disponible

Se completó el recorrido pendiente entrando directamente en categorías con
la sesión administrativa existente. La tabla cargó sus siete registros y
no se carga FastClick. En 375 px CSS efectivos se verificaron apertura y
cierre del menú lateral, submenú Usuarios, desplegable del perfil y cierre
con Escape. El modal real de alta conserva campo, X y botones dentro de la
vista; se comprobó su cierre con la X sin rellenar ni guardar datos.

La consola no registró avisos ni errores. Esta revisión de navegador no
acredita interacción táctil en un dispositivo físico. No se abrió el panel
de alertas ni se enviaron formularios o correos. Se restauró el tamaño del
navegador y se cerró la pestaña creada. Evidencia privada:
`storage/visual-review/admin-category-without-fastclick-real-mobile.png`.

Resultado documentado en el paso 77 del informe privado, sin nuevos cambios
de código, pruebas, suites, auditorías, commits o push. La migración de
Bootstrap 3/AdminLTE sigue pendiente.

## Estado posterior: JavaScript administrativo migrado

El paso 78 migra el runtime JavaScript administrativo a Bootstrap 5.3.8,
conservando los estilos actuales. El inventario inicial anterior describe
el punto de partida. La implementación, comprobaciones y límites están en
[la guía 013](013_admin_bootstrap_javascript.md). Sigue pendiente la base CSS
Bootstrap 3, AdminLTE y la integración de DataTables; no se retira aún el
paquete antiguo ni se considera resuelto su aviso de auditoría.

El paso 79 sustituye también la base CSS administrativa por Bootstrap 5
compilado con las medidas anteriores. La guía [014](014_admin_bootstrap_styles.md)
describe conservación del diseño, correcciones y comprobaciones. AdminLTE,
la integración de DataTables y la retirada del paquete antiguo continúan
pendientes; las tablas iniciales de esta guía siguen siendo antecedentes.

El paso 80 actualiza el núcleo DataTables a 3.1.3 y su integración oficial
Bootstrap 5 en administración y tienda, conservando el diseño. La guía
[015](015_datatables_bootstrap5.md) documenta recursos, controles, revisión
específica y reversión. Se retiran núcleo/adaptador anteriores; AdminLTE y
la retirada de Bootstrap 3 siguen pendientes.

El paso 81 sustituye la carga JavaScript AdminLTE por distribución y
navegación propias de PGCV y retira SlimScroll del runtime. Se conservan
CSS/skins y diseño. La guía [016](016_pgcv_layout_navigation.md) explica
consumidores, comportamiento, comparaciones, límites y reversión. Las
referencias de esta guía a Layout/SlimScroll describen el estado anterior.

El paso 82 reemplaza la carga de doce skins por el tema azul compilado
con Sass desde las mismas reglas. La guía [017](017_pgcv_blue_skin.md)
documenta la fuente, compilación, equivalencia visual y límites. El CSS
principal de AdminLTE y la retirada de Bootstrap 3 siguen pendientes.

El paso 83 reemplaza la carga del CSS principal AdminLTE por el tema
compilado desde fuentes Sass propias, conservando las reglas utilizadas y
retirando estilos de tres plugins sin consumidores. La guía
[018](018_pgcv_theme_styles.md) recoge la procedencia, equivalencia,
comprobaciones y límites. La retirada de fuentes antiguas y Bootstrap 3
sigue pendiente.

El paso 84 retira Bootstrap 3 de npm y del sincronizador, convierte la
vista de modales al tema actual y bloquea por HTTP las copias históricas
conservadas. La guía [019](019_bootstrap3_retirement.md) explica alcance,
retención de archivos y auditoría npm con cero vulnerabilidades. Queda
revisar los plugins copiados manualmente; la auditoría npm no los cubre.

El paso 85 incorpora Select2 4.1.0 a npm y al sincronizador, manteniendo
el único selector propio de carrito, su diseño y foco dentro del modal.
La guía [020](020_select2_update.md) recoge recursos, ajuste de clases CSS,
comparaciones y revisión con datos ficticios. La copia 4.0.4 permanece
bloqueada por HTTP y excluida del hosting. Los demás plugins manuales
continúan pendientes; npm mantiene cero vulnerabilidades.

El paso 86 incorpora Daterangepicker 3.1.0 a npm y al sincronizador.
Mantiene campos, medidas, colores y botones del calendario de ventas
mediante Sass y una adaptación propia; corrige su desbordamiento móvil.
La guía [021](021_daterangepicker_update.md) documenta contrato de fechas,
comparación visual, comprobaciones y reversión. La copia 2.1.25 permanece
bloqueada por HTTP y excluida del hosting. npm sigue sin vulnerabilidades;
los demás recursos manuales continúan pendientes.

El paso 87 sustituye la URL variable SweetAlert2 CDN por el bundle local
11.26.25 fijado en npm. Es idéntico al recurso que entregaba el CDN al
revisarlo. La guía [022](022_sweetalert2_local_assets.md) documenta recursos,
comparación visual y las cuatro ramas de avisos con respuestas ficticias,
sin cambios de estado reales. npm mantiene cero vulnerabilidades; Magnify,
iconos y revisión visual final permanecen pendientes.

El paso 88 incorpora Magnify 2.3.3 a npm y al sincronizador. Corrige la
solicitud de variantes `large-` inexistentes usando la foto disponible,
mantiene las medidas y posiciones y oculta la lente al terminar el gesto
táctil. La guía [023](023_magnify_product_zoom.md) documenta recursos,
comparación, simulación táctil y límites. La copia histórica raíz permanece
bloqueada por HTTP y excluida del hosting. npm mantiene cero vulnerabilidades;
siguen pendientes iconos y revisión visual final por casos concretos.

El paso 89 fija Font Awesome 4.7.0 en npm sin cambiar los símbolos: CSS
y cinco fuentes coinciden con las copias previas por hash. La guía
[024](024_font_awesome_resources.md) documenta procedencia, licencias,
comprobación de 53 nombres de iconos y botones en una vista aislada,
comparaciones de escritorio/móvil y límites. No se hallaron consumidores
propios de Ionicons o Glyphicons en las plantillas revisadas. npm mantiene
cero vulnerabilidades. Sigue la revisión visual final por casos concretos.

El paso 90 revisa carrito/facturación con plantillas reales y datos
ficticios. Corrige el borde de la celda de cantidad y la separación del
símbolo monetario en el total del carrito, conservando diseño y contratos.
Facturación mantiene sus medidas en escritorio/móvil. La guía
[025](025_cart_checkout_visual_review.md) recoge comprobaciones, capturas
y límites. Sigue la revisión visual de las demás pantallas por casos concretos.

El paso 91 revisa listas y modales de productos, usuarios y categorías con
plantillas reales y datos ficticios. Limita el ancho del selector nativo de
foto al espacio de su columna, corrigiendo el desbordamiento a 320 px sin
cambiar las medidas habituales de escritorio. La guía
[026](026_admin_visual_review.md) recoge alcance, evidencias y límites.
Continúa la revisión visual por casos concretos en las pantallas restantes.

El paso 92 revisa descuentos y las cuatro pestañas del registro de actividad
a 1280 y 320 px con plantillas reales y datos ficticios. Controles, modales
y columnas conservan su distribución, sin nuevos ajustes de runtime.
La guía [027](027_discounts_logs_visual_review.md) recoge alcance y evidencias.
Continúa con ventas y detalle de transacciones por casos concretos.

El paso 93 revisa ventas y su modal de detalle con consultas y registros
ficticios. Corrige la limpieza del aviso histórico, que se duplicaba al
reabrir una venta antigua y permanecía al consultar otra con instantánea.
No cambia diseño, importes ni datos históricos. La guía
[028](028_sales_transaction_visual_review.md) recoge evidencia y límites.
Continúa la revisión visual del carrito administrativo por casos concretos.

El paso 94 revisa tabla y modales del carrito administrativo a 1280/320 px
con datos ficticios. Corrige Escape en la búsqueda Select2: cierra primero
el desplegable, conservando el modal, y permite cerrar el formulario con
otra pulsación. Mantiene diseño, campos y botones sin cambios de estilos.
La guía [029](029_admin_cart_visual_review.md) recoge evidencia y límites.
Continúa la revisión visual de formularios públicos sin enviar solicitudes.

El paso 95 revisa acceso, registro y recuperación a 1280/320 px, incluidos
avisos ficticios. Campos, iconos, enlaces y botones caben y conservan el
diseño habitual; no requiere cambios de runtime. La guía
[030](030_public_auth_visual_review.md) recoge métricas, capturas y límites
de las vistas aisladas, sin autenticar, crear cuentas o enviar correos.
Continúa con contacto y páginas públicas restantes por casos concretos.

El paso 96 revisa contacto, Nosotros y Términos a 1280/320 px. Corrige el
solapamiento móvil del texto con la flecha derecha y los estados visuales
de los cuatro indicadores de Nosotros; mantiene el diseño y medidas de
escritorio. Contacto y Términos no requieren ajustes. La guía
[031](031_public_information_visual_review.md) recoge evidencia y límites
de las vistas aisladas, sin enviar mensajes. Continúa con inicio,
categorías, ofertas y búsqueda por casos concretos.

El paso 97 revisa inicio, categoría, ofertas y búsqueda con productos
ficticios y estados vacíos a 1280/320 px. Corrige el texto móvil y los
estados de indicadores del carrusel de inicio mediante las reglas ya
usadas en Nosotros, conservando el diseño y el resto de tarjetas. La guía
[032](032_public_catalog_visual_review.md) recoge evidencia, comparación
de medidas y límites. Continúa la consolidación de pendientes antes de
preparar el hosting, sin publicar automáticamente.
