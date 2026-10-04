# DataTables con Bootstrap 5 y el diseño habitual

## Alcance

DataTables 1.10.16 y su adaptador Bootstrap 3 se sustituyen por
`datatables.net` y `datatables.net-bs5`, ambos fijados en 3.1.3 en npm.
Los encabezados y scripts públicos y administrativos cargan los recursos
locales nuevos. Se retiran núcleo, documentación/metadatos Bower anteriores
y adaptador Bootstrap 3. Se conservan licencias MIT y metadatos propios
`pgcv-assets.json`. No se cargan dos versiones en una página.

La actualización sigue las guías oficiales de
[DataTables 2](https://datatables.net/download/upgrade/core/2.0/upgrade),
[DataTables 3](https://datatables.net/download/upgrade/core/3.0/upgrade) y
[Bootstrap 5](https://datatables.net/examples/core/styling/bootstrap5).
No equivale a actualizar AdminLTE 2 o retirar el paquete Bootstrap 3.

## Conservación del aspecto y comportamiento

- `dist/js/pgcv-tables.js` centraliza el idioma español y conserva longitud,
  filtro, información y paginación en sus posiciones habituales. Tiene
  versión `filemtime` en ambos includes. No se añaden botones Primera/Última.
- Ordenación ascendente/descendente, como antes. Las etiquetas accesibles
  describen ese comportamiento también al terminar el ciclo: DataTables
  utiliza internamente la clave `orderableRemove` para ese caso.
- Las tablas sin ancho explícito conservan su ancho natural. Historial
  público y logs mantienen el 100 % declarado por sus plantillas.
- La columna interna del historial sigue oculta y fuera de la búsqueda.
  Su atributo `data-column-defs` declara también el ciclo de ordenación,
  porque la configuración de plantilla sustituye los defaults compartidos.
- Los selectores pasan a `dt-scroll-head/body`; se mantiene el ajuste de
  columnas al mostrar modales y pestañas. Las regiones pueden recibir foco
  para desplazarlas con teclado.
- `build/scss/_pgcv-tables.scss` se incluye desde ambos temas. Conserva
  campos de 30 px, selector de 75 px, botones, colores, bordes, relleno y
  Font Awesome. La paginación nueva usa botones `.page-link` con el aspecto
  anterior. Los controles se centran/apilan en móvil y las tablas anchas
  se desplazan internamente.

## Recursos y compilación

El sincronizador comprueba las versiones instaladas, compila ambos temas
y copia solo los recursos DataTables seleccionados, junto con licencias.
No editar upstream ni los CSS generados directamente.

```powershell
npm ci --ignore-scripts
npm run sync:frontend
```

Para cambiar únicamente estilos:

```powershell
node tools/build_public_styles.mjs
node tools/build_admin_styles.mjs
```

Ambas compilaciones pasan con el aviso conocido de Sass por `@import` de
Bootstrap. El parcial propio utiliza `@use` y un mixin.

## Verificación específica — paso 80

Revisión real administrativa en Brave a 1280 y 375 px CSS, sin escrituras:

- Categorías: comparación visual antes/después; se conservan cabecera,
  caja, controles, relleno de celdas y tamaños de botones. Filtro «motor»
  devuelve una fila y limpiar recupera siete; descendente empieza por
  OTROS. Edición por AJAX carga OTROS y se cierra sin guardar. En móvil
  coinciden anchos de cabecera/celdas; ArrowRight desplaza ambas regiones
  juntas y permite alcanzar Editar/Eliminar.
- Productos: paginación real de 1–10 a 11–20 de 44 registros.
- Usuarios: seis registros; filtro inexistente muestra el estado vacío.
- Logs: las cuatro pestañas muestran sus registros. Las flechas cambian
  de Usuarios a Ventas; coinciden los ocho anchos de cabecera/celdas y el
  desplazamiento interno mantiene sincronía.
- Ventas: 39 registros y consulta del detalle existente con total
  $580,000.00; cierre sin modificar el pedido.

La sesión del navegador era administrativa. Para comprobar el cambio
público sin redirección al panel se creó una vista temporal con los
encabezado/scripts reales y el marcado de tabla extraído de `perfil.php`.
Usó 18 filas ficticias, sin sesión, base de datos ni correos; `getCart`
se neutralizó antes de su ejecución. Se comprobaron filtro, ordenación
numérica por clic/Enter ($180,000.00 descendente, $10,000.00 ascendente),
paginación, selector de 25, columna oculta, alineación y desplazamiento
móvil y modal de detalle ficticio. En escritorio la tabla conserva unos
805 px y los campos miden 30 px. La vista temporal se retiró al terminar.
Esto no acredita el AJAX del historial real ni una nueva revisión de
carrito/facturación; tampoco una comprobación táctil en teléfono físico.

Pasan sintaxis PHP 8.4 de los seis archivos de aplicación modificados,
sintaxis Node del script compartido/sincronizador, compilación Sass y
`git diff --check`. Recursos copiados contrastados con npm. No se añadieron
pruebas permanentes ni se repitieron suites completas.

Al incorporar dependencias se ejecutó `npm audit --omit=dev`: continúa
un único paquete con aviso moderado, Bootstrap 3.4.1; no aparecen avisos
para DataTables. No es la auditoría final de la aplicación ni sustituye
la auditoría adicional del paso 50.

Capturas privadas `datatables-*.png` en `storage/visual-review/`; excluirlas
del hosting. No se abrió ni recargó `admin/home.php`, guardaron formularios,
confirmaron pedidos, imprimieron reportes, descargaron facturas o enviaron
correos. Sin commits ni push.

## Reversión y siguiente paso

Revertir juntos núcleo/adaptador, referencias de ambos includes, configuración
de tablas, atributo del perfil y reglas Sass de esta fase; regenerar CSS y
restaurar dependencias correspondientes. Mantener las migraciones Bootstrap
5 de las fases anteriores. No hace falta restaurar la base de datos.

Siguen pendientes AdminLTE y sus consumidores, retirada de fuentes y
dependencias Bootstrap 3 y auditoría final. El historial real requiere una
sesión de cliente; no se cambió la sesión actual.
