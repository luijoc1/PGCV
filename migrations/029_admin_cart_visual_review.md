# Revisión visual del carrito administrativo

Paso 94, 4 de octubre de 2026. Revisión específica de `admin/cart.php`
y sus modales con datos ficticios a 1280 y 320 px. Se conserva el diseño
habitual y no se escriben datos reales.

## Escape en el selector de productos

Se reprodujo un problema en el buscador Select2 dentro del modal Nuevo:
Escape cerraba el desplegable y también el formulario completo. Select2
procesaba la tecla y el evento seguía hasta el manejador de Bootstrap.

`admin/includes/scripts.php` añade un manejador de teclado al desplegable
cuando se abre un Select2 contenido en un modal. Únicamente Escape deja
de propagarse al modal; Select2 conserva su manejador de cierre y las
demás teclas. La vinculación usa un espacio de nombres y se reemplaza
en cada apertura para evitar manejadores duplicados. No se modifican
las bibliotecas, estilos, campos, botones ni operaciones de carrito.

Comprobación en navegador a 320 y 1280 px: primera pulsación en el
buscador, desplegable cerrado y modal abierto. A 320 px se comprobó
también la segunda pulsación desde el selector: modal cerrado y cero
opciones temporales, mediante el manejador existente. Cantidad sigue
en uno. Campos y botones del alta mantienen exactamente las medidas y
posiciones de escritorio antes y después del ajuste.

## Tabla y modales

Nuevo y Los usuarios caben en el encabezado a 320 px. Las tres columnas
de la tabla conservan cabeceras y filas alineadas; el contenedor móvil
mide aproximadamente 247.61 px y su contenido ancho se desplaza dentro
de él, sin ensanchar la página. Las diez cantidades visibles permanecen
en uno. Editar cantidad y Eliminar conservan medidas y distribución.

Se abrieron alta, edición y confirmación de eliminación con un nombre
ficticio largo: «Motor de arranque Suzuki GN125 / GS125 marca GX de ejemplo».
En el selector móvil el texto se abrevia con puntos suspensivos, mantiene
el título completo y conserva el valor elegido; el control no desborda.
En edición y eliminación el nombre se divide en líneas dentro del modal.
No se rellenó otra cantidad ni se pulsaron Guardar, Actualizar o la acción
final de Eliminar. La selección de un producto solo afectó el formulario
ficticio; al reabrir volvió a Seleccione, con veinte opciones sin duplicados.

En escritorio el modal de alta mide 600 px y los campos 420 px. A 320 px
el modal mide aproximadamente 300.41 px y los campos 270.41 px. El
desplegable móvil mide 270.39 px y permanece dentro del ancho del modal.
Cerrar y Guardar conservan aproximadamente 76.42 y 88.89 px de ancho;
los pies de edición y eliminación también caben sin solapamientos.
Cierre mediante X y Escape comprobado. No se encontraron desbordamientos
que requirieran cambiar estilos o posiciones de botones.

## Aislamiento, evidencias y límites

La vista temporal únicamente admitía GET desde localhost. No importaba
configuración ni iniciaba sesión persistente. Ejecutaba el cuerpo real
del carrito, encabezados, menú, pie, modales y scripts compartidos con
una cuenta ficticia y veinte filas inventadas. La conexión solo admitía
SELECT. Las peticiones de productos y fila de carrito devolvían opciones
y registros ficticios; cualquier otro destino se rechazaba. Formularios
y enlaces fuera de la vista estaban bloqueados. No acredita el endpoint
real de productos, persistencia o escrituras en el carrito.

No se abrió ni recargó `admin/home.php`, modificaron carritos reales,
confirmaron pedidos, enviaron correos o guardaron formularios. La consola
no mostró errores propios de PGCV; se excluyeron errores procedentes de
una extensión externa del navegador.

Las capturas de viewport y recortes agotaron repetidamente el tiempo de
espera de la herramienta. La captura completa permitió guardar la evidencia
final de escritorio y móvil: `storage/visual-review/admin-cart-escape-after-desktop.png`
y `admin-cart-escape-after-320.png`. Las dimensiones móviles, desplegables
y demás modales se comprobaron con métricas y estado DOM. Esta fase no
acredita un dispositivo móvil físico ni capturas de cada modal.

Métricas, estados DOM y bloque JavaScript extraído para comprobar sintaxis
en `storage/backups/admin-cart-visual-review/`, fuera de Git y hosting.
Sintaxis PHP 8.4 del include y de la vista temporal, `node --check` del
bloque real de inicialización y `git diff --check` correctos. No se añadieron
pruebas permanentes, repitieron suites, recompilaron estilos o ejecutó otra
auditoría npm; no cambian dependencias. El último resultado del paso 89
sigue siendo cero vulnerabilidades en 37 dependencias, sin cubrir recursos
fuera de npm.

La vista temporal se retiró con HTTP 404 comprobado. Se restauró el tamaño
del navegador y se cerró la pestaña creada. No se borraron respaldos,
crearon commits, hicieron push o publicó el proyecto.

Para revertir únicamente esta corrección, retirar el manejador de Escape
añadido en la inicialización compartida, conservando la configuración
Select2 anterior. Continúa la revisión de formularios públicos con
solicitudes y envíos bloqueados.
