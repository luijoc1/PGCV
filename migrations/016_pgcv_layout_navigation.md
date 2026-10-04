# Distribución y navegación propias conservando el tema — paso 81

## Alcance y decisión

PGCV deja de cargar `dist/js/adminlte.min.js` y SlimScroll en los includes
público y administrativo. `dist/js/pgcv-layout.js`, versionado con `filemtime`,
cubre las funciones encontradas en la aplicación: altura de página, botón
lateral y submenús. Conserva jQuery 3.7.1 ya utilizado por el proyecto y sus
animaciones. No instala AdminLTE 4 ni sustituye el tema CSS habitual.

La [guía oficial de migración de AdminLTE](https://adminlte.io/themes/v4/docs/migration.html)
describe cambios de estructura y clases en la versión moderna. Esta fase
separa las interacciones que usa PGCV de ese cambio de tema para conservar
el diseño solicitado. Los CSS de AdminLTE 2 y skins continúan cargados.
Sus fuentes y los archivos JS antiguos permanecen por ahora en el repositorio;
su retirada se decidirá con el inventario de recursos y la siguiente fase.
Bootstrap 3 sigue instalado y su aviso moderado no se considera eliminado.

## Consumidores revisados

Se inspeccionaron PHP de administración/tienda e inicializaciones propias.
No se encontraron consumidores de BoxRefresh, BoxWidget, ControlSidebar,
DirectChat o TodoList, ni páginas de aplicación que usen el modo `.fixed`
o llamadas propias a SlimScroll. Las tres funciones usadas eran Layout,
PushMenu y Tree. El código del panel se leyó sin abrir su URL; su gráfica
Chart.js no depende de estos plugins. No se cambió la alerta de stock.

El script nuevo no implementa APIs o modos antiguos que no usa PGCV. Si
se incorporan nuevas plantillas, revisar expresamente sus necesidades.

## Comportamiento conservado

- Altura mínima a partir del viewport, encabezado, pie y barra lateral;
  se recalcula al redimensionar y al terminar la animación de submenús.
  Inicializa al cargar la página, incluyendo imágenes, y conserva el
  restablecimiento de altura de `html/body/.wrapper`.
- Escritorio: `sidebar-collapse` mantiene la barra reducida de 50 px.
  Móvil, hasta 767 px: `sidebar-open` abre la barra y tocar el contenido
  la cierra. Escape la cierra y devuelve foco al botón cuando no hay modal
  o desplegable Bootstrap abierto.
- Submenús en acordeón con las animaciones de 500 ms anteriores. Se termina
  la animación previa para evitar colas al pulsar varias veces.
- La página actual se marca antes de inicializar los submenús, conservando
  el grupo activo abierto. La primera comparación detectó una diferencia
  de inicialización y se corrigió antes de finalizar.
- Atributos propios `data-pgcv-sidebar/menu`, ID de barra y controles/estado
  ARIA. Los enlaces de submenú tienen rol de botón y admiten Enter/Espacio.
  Bootstrap 5 sigue gestionando dropdowns, modales y navegación pública.

## Comprobación específica

La sesión administrativa inicialmente redirigió categorías al inicio público.
Se preparó una vista temporal de categorías con plantillas reales, PDO ficticio,
datos de ejemplo y envío de formularios bloqueado. No inició sesión ni conectó
a la base de datos. Se compararon seis zonas antes/después: encabezado,
barra lateral, contenido, pie, caja y menú. Sus posiciones, tamaños, altura
mínima, color y relleno coincidieron exactamente en 1280 y 375 px CSS.

Se verificaron reducción a 50 px, apertura/cierre, acordeón, Enter/Espacio,
Escape y foco, cierre al tocar contenido, filtro, modal de categoría y
dropdown del perfil. Las capturas mantienen el aspecto habitual.

La sesión administrativa volvió a estar disponible durante la revisión:
se contrastaron categorías reales, navegación a Usuarios y grupo activo,
apertura móvil, submenú Inventario con teclado y modal real de alta de
usuario, cerrado con Escape sin rellenar ni guardar campos.

La última recarga real mostró un error de conexión a MariaDB
`SQLSTATE[HY000] [2002]` (conexión rechazada). La comprobación final de Escape
frente al dropdown se hizo en la vista aislada: el primer Escape cierra el
perfil dejando abierta la barra y el siguiente cierra la barra/devuelve foco.
No se reinició ni modificó el servicio de base de datos.

Para la parte pública se creó otra vista temporal con encabezado/navbar/scripts
reales, datos ficticios y `getCart` neutralizado antes de ejecutarse, evitando
acceder con la sesión administrativa al include público de sesión. Permite
comparar temporalmente runtime anterior/nuevo. Encabezado, contenido, pie
y caja coinciden en sus cuatro medidas de escritorio. En móvil funcionan
menú y categorías; el inicio del contenido coincide con el final del
encabezado expandido, sin superposición.

Ambas vistas temporales se retiraron. No sustituyen una revisión completa
del panel, historial, carrito/facturación, todas las pantallas o teléfono
físico. No se detectaron errores propios de PGCV en la consola comprobada;
se excluyeron errores de una extensión de navegador externa.

Pasan `node --check` del script, sintaxis PHP 8.4 de los cuatro includes
modificados y `git diff --check`. No se añadieron pruebas permanentes ni se
ejecutaron suites, compilación Sass o auditoría npm: no cambiaron estilos
ni dependencias en esta fase. La auditoría de producción queda como en el
paso 80, con el aviso de Bootstrap 3 pendiente.

Capturas privadas `layout-*.png` en `storage/visual-review/`; excluirlas del
hosting. Se restauró el tamaño del navegador y se cerraron las pestañas
creadas. No se abrió ni recargó `admin/home.php`, guardaron formularios,
confirmaron pedidos, imprimieron reportes, descargaron facturas o enviaron
correos. No hubo commits ni push.

## Reversión y siguiente fase

Restaurar las cargas de AdminLTE/SlimScroll de los includes, la inicialización
activa anterior y los atributos originales de navbar/menubar, y retirar la
carga del script propio. Mantener las migraciones Bootstrap 5/DataTables
anteriores. No hace falta restaurar datos ni regenerar CSS.

Siguen pendientes el tema CSS y fuentes AdminLTE 2, la retirada coordinada
de Bootstrap 3 y recursos sin consumidores, y la auditoría final. Esta fase
retira el JavaScript antiguo del runtime, no declara completada toda la
migración de AdminLTE.
