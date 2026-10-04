# Revisión visual del catálogo público

Paso 97, 4 de octubre de 2026. Revisión de inicio (`index.php`), categorías
(`category.php`), ofertas (`ofertas.php`) y búsqueda (`buscar.php`) a
1280 y 320 px, con productos ficticios y sus estados sin resultados.
El carrusel de inicio también se comprobó a 375 px. Se conserva el diseño
habitual; solo se corrigen dos detalles de su carrusel.

## Carrusel de inicio

Se reprodujo el solapamiento del título con la flecha derecha en móvil.
Las cuatro copias reciben la clase `pgcv-home-carousel-copy`. Se amplían
las reglas del paso 96 para reservar 48 px a cada lado del texto hasta
767 px, limitadas a Inicio y Nosotros. El texto permanece dentro de la
imagen a 320 y 375 px. Se conservan tipografía, colores, imágenes,
centrado vertical y las medidas habituales de escritorio: texto de
aproximadamente 280 × 153.52 px y margen izquierdo de 36 px.

También se reprodujeron tres indicadores transparentes y el primer
indicador azul/ancho fijo aunque cambiara la imagen. Sus propiedades pasan
de inline a los estados Sass propios del carrusel: activo azul de 24 px,
tres inactivos blancos semitransparentes de 8 px, altura de 4 px. Las
flechas incorporan los nombres accesibles Diapositiva anterior/siguiente.
No cambian las reglas del carrusel de Nosotros ya revisado.

Siguiente mostró `motores.jpg` y el indicador 1; seleccionar el cuarto
indicador mostró `d.jpg` y el indicador 3. Anterior mediante Enter mostró
`v.jpg` y el indicador 2. Únicamente el indicador seleccionado quedó azul
y ancho. Las cuatro imágenes cargaron. Se conserva la altura habitual
de 350 px del contenedor y 320 px de sus imágenes, sin cambiar ese diseño.

## Tarjetas y estados vacíos

Se renderizaron seis productos para inicio, categoría y búsqueda; ofertas
mostró los tres productos ficticios con descuento. El caso de $150000
con 15 % sigue mostrando precio anterior $150000 y final $127500; se
incluyó además un precio sin descuento de $4500000. El cálculo usa el
helper real de precios. Esta comprobación visual no sustituye la revisión
del descuento real ya completada en carrito/facturación.

Los nombres largos mantienen puntos suspensivos y atributo title con el
texto completo. Búsqueda conserva el énfasis en Suzuki. Imágenes, precios,
distintivos y Ver producto permanecen dentro de las tarjetas, en tres
columnas en escritorio y apiladas en móvil. Las medidas, posiciones,
textos y botones de todas las tarjetas de categoría, ofertas y búsqueda
coinciden exactamente antes y después de compilar el ajuste del carrusel.
No se cambiaron esas plantillas ni la lógica de precios o consultas.

Las ocho vistas normales y ocho vacías no ensancharon el documento más
allá del viewport. Inicio sin recomendaciones mantiene su aviso y enlace
de exploración; ofertas y búsqueda mantienen sus mensajes sin resultados.
La categoría sin productos conserva el título sin tarjetas, sin añadir
un mensaje nuevo. Estas cuatro plantillas no incluyen paginación;
no se incorporó ni se atribuye una comprobación funcional de paginación.

## Aislamiento y evidencia

La vista temporal únicamente admitía GET desde localhost y las cuatro
plantillas indicadas. Renderizaba sus cuerpos e includes reales sin
configuración, base real o sesión persistente. Los guards y consultas
previos al body de categoría se omitieron y se proporcionó una categoría
ficticia. Las consultas ejecutadas en el body solo podían ser SELECT y
devolvían filas inventadas; COUNT y rowCount reflejaban esas filas.

Formularios y enlaces a páginas reales o redes sociales estaban bloqueados.
Solo se permitían los controles internos de Bootstrap. La lectura automática
del carrito devolvía una respuesta vacía y las demás llamadas AJAX se
rechazaban. Los SDK de pago, CAPTCHA y chat externo, además del avance
automático del carrusel, se omitieron únicamente en la vista temporal.
Esta revisión no acredita consultas o navegación real hacia productos,
persistencia, pagos, correo, chat externo o un móvil físico.

Capturas privadas en `storage/visual-review/`:

- `catalog-home-before-desktop.png` y `catalog-home-before-320.png`.
- `catalog-home-after-desktop.png` y `catalog-home-after-375.png`.
- `catalog-offers-desktop.png` y `catalog-offers-320.png`.
- `catalog-search-desktop.png`, `catalog-search-320.png` y
  `catalog-search-empty-320.png`.

La captura final de inicio a 320 px agotó el tiempo de la herramienta;
ese ancho dispone de métricas DOM y la evidencia visual final móvil es
de 375 px. Se reemplazó la captura de escritorio tomada durante una
transición por la vista estable. Métricas, comparación de tarjetas y
consola sin errores o avisos en `storage/backups/catalog-visual-review/`,
fuera de Git y del hosting.

## Validación y cierre

Sintaxis PHP 8.4 de inicio y de la vista temporal, compilación mediante
`node tools/build_public_styles.mjs` y `git diff --check` correctos.
Permanece el aviso conocido de Sass por `@import`. El CSS generado procede
de la fuente Sass, sin edición directa. No se añadieron pruebas permanentes,
repitieron suites completas o ejecutó otra auditoría npm; no cambian
dependencias.

La vista temporal se retiró y devuelve HTTP 404. Se restauró el viewport
y se cerró la pestaña creada. No se abrió ni recargó `admin/home.php`,
añadieron productos al carrito, modificaron datos reales, confirmaron
pedidos o enviaron correos. No se borraron respaldos, crearon commits,
hizo push o publicó el proyecto.

Para revertir esta corrección, retirar los selectores añadidos para
Inicio, restaurar sus propiedades inline anteriores y recompilar el CSS
público. Mantener los selectores de Nosotros del paso 96. Continúa la
consolidación del inventario de pendientes antes de preparar el hosting.
