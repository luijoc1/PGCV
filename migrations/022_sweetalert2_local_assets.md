# Avisos de ventas con SweetAlert2 local y versión exacta

Paso 87, 3 de octubre de 2026. Se sustituye la carga externa
`https://cdn.jsdelivr.net/npm/sweetalert2@11` por un recurso local fijado
en npm, conservando el mismo código, estilos y comportamiento.

## Alcance

La versión oficial consultada en npm y en las
[publicaciones del autor](https://github.com/sweetalert2/sweetalert2/releases/tag/v11.26.25)
es SweetAlert2 **11.26.25**. También es la versión que entregaba el CDN
al hacer esta revisión: el archivo descargado y el bundle oficial npm
`dist/sweetalert2.all.min.js` tienen el mismo SHA-256:

```text
4e86f0e22e4771b5b8aac24c613c661806a678b1afa284d03cf6ad03d3e21a0a
```

No es un cambio de versión respecto del recurso observado ni un rediseño.
Fijarlo evita que la URL `@11` cambie automáticamente y elimina esa
dependencia de red al cargar los avisos. Esto no convierte toda la
aplicación en una aplicación sin recursos externos.

- `package.json` y `package-lock.json` fijan `sweetalert2: 11.26.25`.
- `tools/sync_frontend_libraries.mjs` copia únicamente el bundle con
  JavaScript y estilos incluidos, licencia MIT y metadatos de procedencia
  a `bower_components/sweetalert2/`.
- `admin/includes/scripts.php` conserva el orden de carga y usa la ruta
  local con `filemtime` para invalidar la caché.
- No cargar otra hoja SweetAlert2 ni modificar su CSS o JavaScript copiado.
  No se cambiaron `Swal.fire`, `Swal.close`, `Swal.showLoading`, mensajes
  ni colores de la aplicación.
- El único consumidor propio encontrado es el manejador de cambios de
  estado en `admin/sales.php`. El bundle continúa en los scripts
  administrativos compartidos; su alcance no se altera en esta fase.

El hosting debe recibir el archivo local incluido en `bower_components`.
Para regenerarlo, usar `npm ci --ignore-scripts` y `npm run sync:frontend`;
el servidor no necesita Node. Los recursos propios Sass se recompilan
durante la sincronización, sin cambios en sus fuentes en este paso.

## Comprobación visual y funcional aislada

Se creó una vista PHP temporal limitada a GET y acceso local, con las
plantillas administrativas, identidad ficticia y estilos de estados reales.
No importaba configuración, sesión persistente ni base de datos. Utilizaba
el manejador real de `sales.php` con `$.ajax` sustituido por respuestas
ficticias; rechazaba cualquier destino distinto del previsto e ID distinto
de cero. No ejecutó `actualizar_estado.php` ni solicitudes de escritura.
Los formularios estaban interceptados. La vista se retiró al terminar.

Comparación antes/después, una vez terminada la animación, en escritorio
de 1280 px y móvil de 375 px: seis zonas idénticas en posición, tamaño,
tipografía, colores y bordes —ventana, título, texto, icono, acciones y botón—.
El foco inicial sigue en Aceptar. El documento no desborda horizontalmente.
Ventana de unos 512 × 333 px en escritorio y 358 × 356 px en móvil; botón
Aceptar de unos 86 × 43 px y azul habitual `#3c8dbc`.

Se comprobaron las cuatro ramas del manejador real, con transporte ficticio:

- Éxito: aviso con Aceptar y estado ficticio seleccionado conservado.
- Rechazo: aviso Error y restauración del estado anterior del selector.
- Error de conexión: mensaje existente y restauración del estado anterior.
- Espera: indicador de carga visible, botón oculto y Escape sin cerrar,
  conforme a `allowEscapeKey: false` de la aplicación.

Aceptar/OK se accionaron con clic y Enter; Escape cierra el aviso de error.
Se confirmó la eliminación de la ventana después de su animación. La
consola no registró errores propios de PGCV. El DOM carga una única ruta
SweetAlert2 local; no carga la URL CDN anterior.

El recurso local responde HTTP 200; la referencia privada guardada bajo
`storage/backups` responde 403. Sintaxis PHP 8.4 de scripts compartidos y
vista temporal, sintaxis Node del sincronizador y bundle, sincronización,
versión instalada y `git diff --check` correctos. El recurso servido coincide
por hash con npm y con la referencia CDN anterior.

Auditoría npm completa, incluidas dependencias de desarrollo: cero
vulnerabilidades, 35 dependencias. No incluye bibliotecas fuera de npm.
Persisten los dos avisos Sass conocidos de `@import` en las bases pública
y administrativa; la compilación termina correctamente.

Evidencias privadas: referencia CDN, métricas y auditoría en
`storage/backups/sweetalert2-review/`; capturas
`storage/visual-review/sweetalert2-after-desktop.png` y
`sweetalert2-after-mobile.png`, sin datos de clientes. Excluir del hosting
y Git. Se restauró el tamaño del navegador y se cerró la pestaña creada.

No se navegó por páginas administrativas reales en esta fase ni se abrió,
recargó o interactuó con la pestaña existente de `admin/home.php`. No se
cambiaron estados reales, guardaron datos, confirmaron pedidos, imprimieron
reportes ni enviaron correos. La revisión aislada no acredita una actualización
real de estado en el servidor. No se añadieron pruebas permanentes,
repitieron suites generales, crearon commits ni hicieron push.

## Reversión y siguientes pasos

Para volver al estado anterior, revertir conjuntamente la referencia del
include, la incorporación al sincronizador y la dependencia npm, conservando
el resto de los cambios previos. La referencia anterior está guardada de
forma privada; no desplegarla desde `storage/backups` ni cargar dos bundles.

Siguen pendientes Magnify, los recursos de iconos y la revisión visual final
por casos concretos. La auditoría npm no equivale a una auditoría completa
de todas las copias del frontend.
