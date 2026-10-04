# Revisión visual de contacto y páginas informativas

Paso 96, 4 de octubre de 2026. Revisión de `contacto.php`,
`sobrenosotros.php` y `terminos.php` a 1280 y 320 px. El carrusel de
Nosotros también se comprobó a 375 px. Se conserva el diseño habitual.

## Carrusel de Nosotros

En móvil el título ocupaba parte de la zona de la flecha derecha. Las
cuatro copias reciben una clase propia; Sass reserva 48 px a cada lado
del texto solo hasta 767 px. Esto deja libres los círculos de las flechas
y permite dividir título y descripción en líneas dentro de la imagen.
Se mantienen fuente, colores, imágenes, altura de 320 px y centrado
vertical. El texto sigue midiendo aproximadamente 280 × 125.92 px en
escritorio, con su margen izquierdo de 36 px.

Los indicadores inactivos eran transparentes y el primer indicador tenía
ancho y color azul fijos aunque se seleccionara otra imagen. Se retiraron
esas propiedades inline y se definieron estados limitados a este carrusel:
activo azul de 24 px, otros tres blancos semitransparentes de 8 px. Se
conservan altura de 4 px, forma y ubicación. Las flechas incorporan nombres
accesibles: Diapositiva anterior y Diapositiva siguiente.

En navegador se comprobó Siguiente, selección del cuarto indicador y
Anterior mediante Enter. Se observaron la segunda, cuarta y tercera
imágenes respectivamente; al seleccionar la cuarta y regresar a la
tercera, únicamente el indicador correspondiente quedó azul y ancho.
Las cuatro imágenes cargan. El texto cabe a 320 y 375 px y las flechas
permanecen en sus posiciones habituales.

## Contacto, términos, tarjetas y pie

Los cuatro campos de contacto mantienen el mismo ancho y alineación.
Enviar mensaje queda debajo del textarea y cabe en móvil. El aviso
ficticio divide sus líneas sin cubrir el formulario. Tras compilar, el
formulario de escritorio conserva exactamente posición y dimensiones
previas: aproximadamente 749.98 × 370.78 px; el botón conserva
115.63 × 34.41 px. No se rellenó ni envió el formulario.

El encabezado, los siete bloques y Volver al registro de Términos caben
en móvil. El enlace mide aproximadamente 170.46 × 42.39 px y permanece
centrado. Las tarjetas ficticias de productos, sus enlaces Ver producto,
la barra lateral y el pie mantienen su distribución: columnas en
escritorio y bloques apilados en móvil. No se encontró desbordamiento
horizontal de la página. No se cambiaron textos legales o comerciales,
ni se verificó su contenido jurídico.

## Aislamiento, evidencias y validación

La vista temporal solo admitía GET local y las tres plantillas indicadas.
Renderizaba cuerpos e includes reales, omitiendo el include inicial de
sesión. No cargaba configuración, sesión persistente o base real. Las
consultas solo podían ser SELECT y devolvían una categoría y seis
productos inventados. La lectura automática de carrito devolvía una
respuesta vacía; otras llamadas AJAX y los formularios estaban bloqueados.
Solo se permitían los enlaces internos de Bootstrap para carrusel y
desplegables. No se siguieron enlaces a páginas reales o redes sociales.

Los SDK de pago, CAPTCHA y chat externo se omitieron únicamente en la
vista temporal. También se desactivó su avance automático de carrusel
para comparar medidas. No se alteró esa configuración en producción.
Esta revisión no acredita consultas reales, entrega de mensajes,
suscripciones, chat externo o un dispositivo móvil físico.

Evidencia privada en `storage/visual-review/`: `contact-desktop.png`,
`about-before-320.png`, `about-carousel-after-320.png`,
`about-carousel-after-desktop.png` y `terms-320.png`. Métricas y consola
sin errores o avisos en `storage/backups/info-visual-review/metrics.json`.
Varias capturas agotaron el tiempo de la herramienta; contacto móvil y
su aviso se comprobaron con métricas DOM. Se descartó una captura tomada
durante la transición del carrusel y se guardó la vista final estable.

Sintaxis PHP 8.4 de Nosotros y de la vista temporal, compilación con
`node tools/build_public_styles.mjs` y `git diff --check` correctos.
Permanece el aviso conocido de Sass por `@import`. El CSS generado deriva
de la fuente Sass; no se editó directamente. No se añadieron pruebas
permanentes, repitieron suites completas o ejecutó otra auditoría npm;
no cambian dependencias.

La vista temporal se retiró y devuelve HTTP 404. Se restauró el viewport
y se cerró la pestaña creada. No se abrió ni recargó `admin/home.php`,
modificaron datos reales, confirmaron pedidos o enviaron correos. No se
borraron respaldos, crearon commits, hizo push o publicó el proyecto.

Para revertir esta corrección, retirar las reglas específicas del carrusel,
restaurar sus estilos inline anteriores y recompilar el CSS público.
Continúa la revisión de inicio, categorías, ofertas y búsqueda por casos
concretos, con navegación y escrituras aisladas.
