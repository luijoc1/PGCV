# Iconos habituales con procedencia reproducible

Paso 89, 4 de octubre de 2026. Se conserva Font Awesome **4.7.0**, sus
clases, símbolos y medidas. Esta fase fija la procedencia de los recursos
existentes; no es una migración a una versión mayor ni afirma que 4.7.0
sea la versión actual de toda la familia Font Awesome.

## Inventario y cambios

La búsqueda en los 122 archivos PHP de raíz, includes, administración y
sus includes encontró 53 nombres de iconos, incluyendo referencias en
comentarios. Todos están definidos por la hoja 4.7.0; no hay referencias
propias Glyphicons, Ionicons ni clases `fas`, `far` o `fab` en ese alcance.
Las reglas genéricas del tema que mencionan `.ion` o `.glyphicon` son
antecedentes de AdminLTE y no implican que esas fuentes estén cargadas.
El botón de impresión de ventas ya utiliza `fa-print` desde la migración
anterior. Solo se leyó su fuente: no se imprimieron reportes.

- `package.json` y `package-lock.json` fijan `font-awesome: 4.7.0`.
- El sincronizador copia la hoja minificada y sus cinco fuentes web a
  `bower_components/font-awesome/`, manteniendo las rutas anteriores.
  Los seis archivos coinciden por SHA-256 con los recursos previos; no
  se cambiaron CSS ni glifos. Los hashes están guardados de forma privada.
- Se añaden metadatos `pgcv-assets.json` y `LICENSE-NOTICE.md`, extraído
  de los avisos del README del paquete oficial. El sincronizador comprueba
  la versión instalada y la presencia del aviso antes de generarlo.
- Los dos encabezados cargan la misma hoja con `filemtime` para caché.
  No se modifican atributos de iconos, botones ni fuentes Sass en este paso.

La [licencia oficial](https://fontawesome.com/v4/license/) identifica
SIL OFL 1.1 para las fuentes, MIT para el código CSS y CC BY 3.0 para
documentación. Conservar los avisos al distribuir. No confundir el README
del paquete con el README raíz privado de PGCV, que permanece ignorado.

Regeneración: `npm ci --ignore-scripts` y `npm run sync:frontend`.
El hosting recibe `bower_components/font-awesome/css/font-awesome.min.css`
y las cinco fuentes de `fonts/`, junto con avisos y procedencia. No necesita
Node para servirlos. Las fuentes LESS/SCSS históricas de esa carpeta no
se utilizan en la compilación propia ni deben editarse para cambiar el tema.
Ionicons permanece conservado sin consumidores propios encontrados;
excluir `bower_components/Ionicons/` del paquete de hosting de esta aplicación.
No se borraron copias históricas.

## Verificación visual y límites

Se creó una vista temporal GET limitada a acceso local, usando el cuerpo
real de categorías, encabezado, menú, scripts y modales administrativos.
Identidad y categoría ficticias; sin configuración, sesión persistente ni
conexión real a la base. Los formularios, AJAX y enlaces externos a la
vista estaban interceptados. Una galería temporal mostró los 53 símbolos
en el tema real; no se incorpora a ninguna pantalla del producto.

En 1280 y 375 px se comprobaron contenido del pseudoelemento, fuente,
tamaño y anchura de los 53 símbolos: ninguno ausente o sin anchura.
En escritorio las medidas y posiciones coinciden exactamente. En móvil
coinciden las medidas y posiciones relativas, descontando el desplazamiento
de página que cambia al abrir el modal. La comparación admite una tolerancia
de 0.001 px para números de coma flotante. La vista móvil no desborda
horizontalmente: documento de 352 px dentro de 375 px efectivos.

Nuevo, Editar y Eliminar mantienen tamaño y posición relativos. El borde
de Nuevo cambia al recibir foco/clic por sus estilos existentes; se excluyó
ese estado de la comparación del borde. No se pulsaron Editar ni Eliminar.
Los botones Cerrar y Guardar del modal mantienen exactamente sus medidas
y posiciones en ambos tamaños, con sus símbolos visibles. Se abrió Nuevo
y se comprobó el cierre con Escape y con la X, sin rellenar ni guardar.
La consola no registró errores ni avisos.

Captura privada de escritorio:
`storage/visual-review/icons-after-desktop.png`. La captura móvil falló
por tiempo de espera del navegador; se conservan las métricas móviles
en `storage/backups/icons-review/metrics.json`, sin afirmar que exista
captura móvil ni prueba en dispositivo físico. El tamaño del navegador
se restauró y la pestaña creada se cerró. La vista temporal se retiró (404).

Sintaxis PHP 8.4 de ambos encabezados y vista temporal, sintaxis Node del
sincronizador, hashes antes/después, sincronización y `git diff --check`
correctos. CSS y fuente WOFF2 actuales responden HTTP 200. npm audit
completo, con desarrollo: cero vulnerabilidades, 37 dependencias. No cubre
bibliotecas fuera de npm. Los dos avisos conocidos Sass por `@import`
continúan; las cinco hojas se compilan correctamente.

No se abrió, recargó o interactuó con `admin/home.php`, cambiaron datos,
confirmaron pedidos ni enviaron correos. Sin pruebas permanentes, suites
generales, commits o push. La revisión aislada no acredita todas las
pantallas reales ni operaciones de guardado.

## Reversión y continuación

Para revertir esta fase, retirar conjuntamente la dependencia y su regla
de sincronización y recuperar los enlaces sin query de los encabezados.
Los recursos anteriores son idénticos a los actuales por hash. Conservar
los demás cambios de migración y no revertir archivos completos que los
contienen. Sigue la revisión visual final por casos concretos, incluido
carrito/facturación cuando se compruebe una posible regresión de estilos.
