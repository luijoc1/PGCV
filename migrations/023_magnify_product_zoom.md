# Lupa de producto con Magnify y foto disponible

Paso 88. Se incorpora Magnify **2.3.3** desde npm con versión exacta,
licencia MIT y procedencia en `pgcv-assets.json`. Es la publicación oficial
consultada en el [repositorio del autor](https://github.com/thdoan/magnify).
La copia anterior de `magnify/` no declara una versión verificable; no se
le atribuye una versión a partir de su apariencia o tamaño.

## Corrección y recursos

La ficha solicitaba `images/large-<foto>`, pero no había ninguna imagen
con ese prefijo y el flujo de subida guarda únicamente la foto original.
La solicitud fallida impedía crear la lupa. `safeProductZoomUrl()` utiliza
la variante `large-` solo si existe y, en caso contrario, la foto original.
Conserva la codificación del nombre y el rechazo de rutas de `safeImageUrl`.
No se generan imágenes ni se modifican fotos o registros de productos.
El detalle disponible al ampliar depende de la resolución de la foto.

- npm fija `magnify: 2.3.3`; el sincronizador copia los archivos oficiales
  `dist/js/jquery.magnify.js` y `dist/css/magnify.css` a
  `bower_components/magnify/`, con licencia y metadatos. El paquete no
  publica un JavaScript minificado; se utiliza su distribución oficial.
- Los includes públicos cargan estas rutas locales con `filemtime`.
  Se conserva la lente de 100 px y los estilos habituales.
- La clase `pgcv-product-image` delimita el ajuste del envoltorio que
  crea el plugin: mantiene el marco, centrado y tamaño de la imagen.
  La inicialización con `timeout: 0` oculta la lupa al terminar el gesto
  táctil, evitando que quede fija sobre la foto.
- La copia histórica raíz `magnify/` permanece físicamente conservada,
  bloqueada por HTTP mediante `.htaccess`. Excluirla del hosting; incluir
  la distribución actual de `bower_components/magnify/`.

Para regenerar recursos: `npm ci --ignore-scripts` y
`npm run sync:frontend`. No editar las copias distribuidas. La
sincronización compila las cinco hojas propias, sin modificar sus fuentes
en este paso. Persisten los dos avisos conocidos de Sass por `@import`;
la compilación termina correctamente.

## Comprobaciones específicas y límites

Se utilizó una vista temporal local con el cuerpo real de `producto.php`,
includes públicos y producto ficticio. No importaba configuración, base
de datos ni sesión persistente. El transporte AJAX y los formularios
estaban bloqueados. No se visitó la ficha real: su GET incrementa visitas
y la sesión administrativa existente podría redirigir al panel de alertas.
La vista temporal se retiró al terminar y responde HTTP 404.

Las nueve zonas comparadas antes/después coinciden en escritorio de
1280 px y vista móvil de 375 px: marco, foto, información, encabezado,
botones menos/más, cantidad, botón de carrito y barra lateral. Se
compararon posiciones, tamaños, tipografía, colores, bordes y espaciado.
No hay desbordamiento horizontal. Los controles del carrito no se pulsaron.

La lente carga la foto existente de 1193 × 990 px, se activa sobre la
imagen y se oculta al salir. Se verificó además un gesto táctil simulado
con eventos `TouchEvent`: visible durante el movimiento y oculta al
terminar. Esto no acredita una prueba en un dispositivo táctil físico.
La consola no registró errores ni avisos. La captura móvil falló por
tiempo de espera del navegador; su comprobación se conserva mediante
métricas DOM y los eventos simulados, sin atribuirle una captura existente.

Se comprobaron seis casos del helper sin base de datos: foto original,
nombre nulo, rutas con separadores, nombre con espacios y variante grande
existente. La imagen temporal para el último caso se retiró. Sintaxis
PHP 8.4 de los cuatro archivos de aplicación y vista temporal, sintaxis
Node del sincronizador y plugin, hashes de copia npm y `git diff --check`
correctos. Los recursos actuales responden 200 y los históricos 403.
Auditoría npm completa: cero vulnerabilidades, 36 dependencias; no cubre
recursos fuera de npm.

Evidencia privada: métricas, referencia anterior, casos y auditoría en
`storage/backups/magnify-review/`; captura
`storage/visual-review/magnify-after-desktop.png`. Excluir de Git y hosting.
Se restauró el tamaño del navegador y se cerró la pestaña creada.
No se abrió ni recargó `admin/home.php`, añadieron productos, guardaron
datos, confirmaron pedidos o enviaron correos. Sin pruebas permanentes,
suites generales, commits ni push.

## Reversión y continuación

Revertir conjuntamente dependencia, sincronizador y referencias si se
necesita recuperar la copia anterior. Mantener el helper de foto disponible
evita volver a solicitar variantes inexistentes. No cargar dos plugins ni
servir recursos desde los respaldos privados. Quedan los recursos de
iconos y la revisión visual final por casos concretos.
