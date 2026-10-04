# Revisión visual de carrito y facturación

Paso 90, 4 de octubre de 2026. Revisión específica posterior a la migración
de estilos, conservando el diseño habitual. El descuento real del 15 % ya
se comprobó en el paso 48; esta fase revisa su presentación con datos
ficticios, sin repetir una compra ni modificar el carrito real.

## Detalles corregidos

En `cart_detalles.php`, la clase `input-group` estaba aplicada directamente
al `td`. Su `display: table` separaba la altura de esa celda de la fila:
52.78 px frente a 77.19 px en el primer caso de escritorio, dejando un borde
interior adicional. El grupo pasa a un `div` dentro del `td` en ambas ramas,
cliente e invitado. La celda conserva `display: table-cell` y sus bordes
coinciden con los de la fila. Se mantienen seis celdas, clases de botones,
identificadores, cantidades, stock, consultas y manejadores existentes.

El total de la tabla separaba el símbolo `$` del importe en escritorio.
`cart_ver.php` añade `pgcv-cart-table` y la fuente Sass pública delimita
`white-space: nowrap` a precio y última celda de sus filas. El campo de
cantidad tiene un mínimo de 40 px dentro de esta tabla para mantenerlo
legible con los dos botones. No se aplican estas reglas a otras tablas.
El nombre puede dividirse en escritorio; en móvil se conserva el
desplazamiento horizontal habitual de las seis columnas.

Se recompiló mediante `node tools/build_public_styles.mjs`. No se editó
directamente el CSS generado. La compilación pasa con el aviso conocido
de Sass por `@import`. No cambian dependencias, precios, lógica de compras,
plantilla de facturación ni el aviso de carrito ya aceptado.

## Comprobación aislada

La vista temporal local GET utilizaba los cuerpos reales de `cart_ver.php`
y `facturacion.php`, encabezado, menú, sidebar, pie visual y scripts reales.
Para las filas se ejecutó el bloque de presentación real de
`cart_detalles.php` con una conexión ficticia que solo admitía SELECT.
No importaba configuración ni iniciaba sesión persistente. Los endpoints
AJAX se sustituyeron por respuestas locales; cualquier destino ajeno se
rechazaba. Formularios y enlaces fuera de la vista estaban interceptados.
El SDK de chat se excluyó de la vista temporal, conservando el pie visual.

Se representaron dos artículos ficticios con cantidad uno: precio original
$150,000.00, descuento 15 %, subtotal $127,500.00; y otro de $80,000.00.
Tabla, total junto a Pagar y resumen muestran $207,500.00. El resumen
contiene exactamente dos filas, nombres con `x1`, sin `xundefined` ni fila
extra de total dentro del cuerpo. Pagar abrió únicamente la facturación
ficticia mediante una adaptación de su destino en la vista temporal.
No se pulsó Confirmar pedido.

En escritorio de 1280 px y móvil de 375 px:

- Las seis celdas de cada fila conservan el mismo alto después del ajuste;
  en el caso final, 57.19 px en la primera fila. El símbolo monetario y
  el importe permanecen juntos. Botones menos/más conservan sus medidas,
  aproximadamente 37.41 × 34.41 px; el campo móvil mide unos 40 px.
- El contenedor móvil mantiene unos 273 px y permite consultar cantidad
  y subtotal por desplazamiento horizontal, sin desbordar la página.
  Se comprobó Tab del campo al botón más y flecha derecha en la región
  de tabla. Una lectura final confirmó ambos botones y el campo totalmente
  dentro de la región visible, con valores `1`, `1` intactos. No se accionaron
  botones de cantidad ni eliminar.
- Facturación conserva las medidas antes/después del CSS: dos columnas
  en escritorio, apiladas en móvil. Formulario, resumen y Confirmar pedido
  mantienen sus posiciones y dimensiones. En móvil resumen y botón miden
  aproximadamente 293 px. No se rellenaron campos ni se enviaron formularios.
- Consola sin errores ni avisos. Las capturas completas de móvil se
  obtuvieron con la API `screenshot({fullPage:true})`; su resultado no
  acredita una prueba en dispositivo táctil físico.

Se realizaron además siete comprobaciones específicas para cliente y siete
para invitado, ejecutando las filas reales con datos ficticios: dos filas
de producto, seis celdas por fila, dos grupos dentro de celda, ninguna
celda convertida en grupo, cantidades uno, descuento y total intactos.
Los 14 casos pasan sin conexión a la base. No se añadieron pruebas
permanentes ni se repitieron suites generales. El caso temporal guardado
privadamente dependía de la vista de revisión, que se retiró al finalizar.

Sintaxis PHP 8.4 de ambos archivos de carrito y vista temporal, compilación
y `git diff --check` correctos. No se repitió npm audit porque no cambian
dependencias: su último resultado, paso 89, sigue siendo cero vulnerabilidades
en 37 dependencias y no cubre bibliotecas fuera de npm.

Evidencias privadas, excluidas de Git y hosting: métricas y caso local en
`storage/backups/checkout-visual-review/`; capturas en `storage/visual-review/`:
`cart-review-after-desktop.png`, `cart-review-after-mobile.png`,
`billing-review-desktop.png` y `billing-review-mobile.png`.
La vista temporal se retiró (HTTP 404), se restauró el tamaño del navegador
y se cerró la pestaña creada. No se abrió ni recargó `admin/home.php`,
modificaron datos reales, confirmaron pedidos, enviaron correos, crearon
commits o hicieron push.

## Reversión y continuación

Revertir únicamente el envoltorio de cantidad en ambas ramas, la clase
de la tabla y las reglas Sass de esta fase; recompilar. No revertir
por completo fuentes que contienen migraciones anteriores. La revisión
aislada acredita presentación y contrato de filas, no una compra nueva,
actualización real de cantidad o entrega de correo. Continúa la revisión
visual por casos concretos en las demás pantallas.
