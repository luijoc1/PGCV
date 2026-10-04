# Tema azul compilado conservando el diseño

## Alcance

Todas las páginas propias de PGCV utilizan `skin-blue`. Los encabezados
público y administrativo cargan ahora `dist/css/pgcv-skin-blue.min.css`
en lugar de las doce variantes de `_all-skins.min.css`. Se conserva el
orden de las hojas y se versiona el recurso mediante `filemtime`.

La fuente `build/scss/pgcv-skin-blue.scss` conserva las reglas de
`dist/css/skins/skin-blue.css`, su autor original Almsaeed Studio y la
referencia a la licencia MIT. Es una adaptación del tema existente,
no una actualización a otra versión de AdminLTE ni un rediseño. Conserva
el verde administrativo aplicado mediante `bg-green` y los colores
propios del encabezado público.

Esta compilación no importa Bootstrap 3 ni LESS. El CSS principal
`AdminLTE.min.css`, las copias antiguas de skins y sus fuentes permanecen.
Bootstrap 3 sigue en npm y su aviso continúa pendiente.

## Compilación

```powershell
node tools/build_skin_styles.mjs
```

El generador valida la versión de Sass fijada en el manifiesto. También
se ejecuta desde `npm run sync:frontend`, después de los dos estilos base.
No editar la salida generada. El hosting recibe el CSS compilado y no
necesita Node. No se reinstalaron ni cambiaron dependencias en esta fase.

La hoja anterior mide 41.583 bytes y la nueva 3.455 bytes, incluyendo su
aviso de procedencia. El bloque azul anterior y las reglas generadas son
idénticos al normalizarlos con Sass: 3.153 caracteres en ambos casos,
excluyendo comentarios. La variante azul clara del archivo anterior
repetía dos reglas del logo azul ya presentes en el bloque conservado.

## Comprobaciones y límites

MariaDB no tiene un listener en el puerto local 3306 durante la revisión.
No se arrancó ni reinició el servicio. Se utilizaron dos vistas temporales
con los encabezados, navegación y scripts reales, categorías y cuenta
ficticias, sin configuración, sesión persistente, base o correo. Solo
admitían GET local, interceptaban formularios y la pública neutralizaba
`getCart` antes de su inicialización. Ambas se retiraron.

Las nueve zonas administrativas medidas coinciden exactamente en
escritorio de 1280 px y móvil de 375 px CSS: posición, tamaño, color,
tipografía, relleno y bordes. Coinciden también las cinco zonas públicas
medidas en ambos tamaños. Se comprobaron menú lateral, desplegable de
perfil y Escape, modal de alta de categoría y su cierre, menú público y
sus siete categorías. El contenido público comienza al terminar el
encabezado abierto. No se encontraron nuevas diferencias visuales ni
avisos/errores en las consolas de estas vistas.

Pasan compilación del tema, sintaxis Node del generador y sincronizador,
sintaxis PHP 8.4 de los dos encabezados y `git diff --check`. No se
añadieron pruebas permanentes ni se repitieron suites o auditorías npm.
Esta comprobación no acredita el panel real, todas las páginas, AJAX
con datos reales, carrito o facturación.

Evidencia privada: `storage/visual-review/skin-blue-category-mobile.png`
y `skin-blue-metrics.json`. Excluirla del hosting. Se restauró el tamaño
del navegador y se cerraron las pestañas creadas. No se abrió ni recargó
`admin/home.php`, guardaron datos, confirmaron pedidos, enviaron correos,
crearon commits o hicieron push.

## Reversión y continuación

Restaurar en los dos encabezados la carga `_all-skins.min.css` en la misma
posición. No hace falta restaurar datos. Queda revisar el CSS principal
de AdminLTE y sus recursos restantes, conservando el diseño; después,
retirar Bootstrap 3 cuando no queden consumidores y completar la auditoría.
