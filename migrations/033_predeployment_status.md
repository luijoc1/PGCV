# Estado consolidado antes del hosting

Paso 98, 4 de octubre de 2026. Resumen del estado local después del paso 97.
Se revisaron documentación, manifiestos y referencias de las plantillas;
no se ejecutó una auditoría nueva ni se comprobó un servidor de destino.
Los pendientes de las guías anteriores describen su momento de revisión:
no deben volver a tratarse como tareas abiertas si una fase posterior
ya los resolvió.

Actualización del paso 99: los cambios acumulados quedaron organizados
en commits locales. No se hizo push, se preparó un paquete ni se publicó.

## Completado localmente

| Área | Estado y evidencia |
| --- | --- |
| PHP local | Apache utiliza PHP 8.4.26 TS; la CLI antigua sigue en 7.4. Para sintaxis usar `storage/backups/php84-apache/runtime/php.exe`. Compatibilidad y SMTP documentados en `DEPLOYMENT.md`. |
| Bootstrap | JavaScript y CSS Bootstrap 5.3.8 en tienda y administración, conservando el diseño. Bootstrap 3 retirado del runtime y de npm en el paso 84; copias históricas conservadas y bloqueadas. Guías 010–014 y 019. |
| Distribución y tema | Navegación propia en `dist/js/pgcv-layout.js`; AdminLTE/SlimScroll antiguos no se cargan en los includes revisados. Tema principal y azul compilados desde fuentes Sass que conservan reglas derivadas de AdminLTE 2. Guías 016–018. No es una actualización upstream de AdminLTE. |
| Tablas y plugins | DataTables 3.1.3 con adaptador Bootstrap 5, Select2 4.1.0, Daterangepicker 3.1.0, SweetAlert2 11.26.25, Magnify 2.3.3 y Font Awesome 4.7.0 fijados en npm. Jodit, jQuery, Moment y Chart.js también figuran en el manifiesto/sincronizador. Guías 015 y 020–024. Datepicker, Timepicker y FastClick antiguos retirados de la carga. |
| Auditoría npm documentada | Último resultado del paso 89: cero vulnerabilidades en 37 dependencias, incluyendo desarrollo. El aviso moderado de Bootstrap 3 quedó resuelto en el paso 84. No es una auditoría de toda la aplicación ni cubre bibliotecas fuera de npm. |
| Revisión visual | Carrito/facturación, listas y modales administrativos, descuentos, actividad, ventas, carrito administrativo, acceso/registro/recuperación, contacto, Nosotros, Términos, inicio, categorías, ofertas y búsqueda revisados por casos concretos. Guías 025–032, con los límites de datos ficticios, capturas y dispositivos documentados. |
| Correcciones aceptadas | Buscador, perfil/historial y X del aviso de carrito siguen terminados. El descuento real del 15 % en carrito/facturación ya fue revisado antes de confirmar: no se reabre como pendiente original. |
| Integridad histórica | Paso 55 aplicado: cinco carritos archivados, 17 ventas conservadas con `user_id=NULL` y referencia original en `legacy_user_id`, ocho claves foráneas. No se recrearon las cuentas ausentes. Los 50 detalles sin instantánea permanecen sin reconstruir. |
| PDF | TCPDF 6.11.4 fijado en Composer y utilizado por facturas/reporte desde el autoload. La copia raíz antigua está inactiva en esos flujos y bloqueada; no describirla como generador activo. Persisten las limitaciones de soporte documentadas en el paso 26. |
| SMTP local | Conexión, certificado TLS y autenticación comprobados con PHP 8.4; no se ha acreditado entrega de correo real. |

## Pendientes para publicar

| Pendiente | Resultado necesario |
| --- | --- |
| Elegir proveedor y dominio | Conocer el destino, su versión exacta de PHP/base, modalidad Apache o PHP-FPM, acceso a Composer/CLI, permisos y SMTP saliente. No se ha contratado ni configurado un destino. |
| Preparar una revisión y un paquete reproducibles | Los cambios acumulados desde el paso 73 están organizados en commits locales, descritos más abajo. Falta seleccionar la revisión que se publicará y preparar archivos de producción con las exclusiones de `DEPLOYMENT.md`. No se creó aún un paquete ni se subieron archivos. |
| Comprobar el entorno del hosting | Instalar en una copia aislada, configurar APP_URL/HTTPS, certificados, credenciales privadas, permisos, sesión, bloqueo de archivos y mantenimiento. Ajustar las reglas según el servidor y la ubicación real, sin copiar los runtimes locales. |
| Preparar y restaurar la base de destino | Decidir entre una instalación vacía o conservar catálogo/cuentas/historial. Para conservarlos, generar un respaldo nuevo y verificar su restauración en una base vacía. No repetir la migración histórica con los conteos originales ni inventar precios históricos. |
| Verificar el comportamiento en destino | Comprobar acceso y roles, catálogo, carrito/resumen, PDF e integridad con la versión exacta del hosting. Las comprobaciones que escriban usarán datos aislados. Las revisiones visuales locales no certifican el futuro servidor. |
| Correo real | Comprobar SMTP desde el destino y, solo con autorización y destinatario elegido, enviar un mensaje de prueba y verificar su recepción. Nunca usar la apertura del panel como prueba de correo. |
| Decidir el cobro | La pasarela de pago sigue pendiente. Antes de presentar tarjeta/PSE como cobro operativo, decidir si se registrarán pedidos sin cobro en línea o se implementará una pasarela con su comprobación específica. Cargar el SDK de PayPal no acredita pagos. |
| Publicación | Revisar el paquete concreto y el entorno probado antes de subir/publicar con autorización explícita. No hacer push ni despliegue automático. |

## Mantenimiento y alcance restante

Las copias históricas locales no necesitan borrarse para preparar el
hosting: deben excluirse del paquete. La eliminación conjunta rechazada
en el paso 84 no se repitió. El diseño derivado de AdminLTE 2 y TCPDF
conservan las limitaciones de soporte documentadas; cualquier sustitución
requiere un bloque propio que preserve apariencia y contratos.

Los encabezados públicos aún incluyen SDK externos de PayPal y reCAPTCHA;
producto y pie contienen integraciones de Facebook. Las vistas aisladas
recientes los omitieron, por lo que su carga, necesidad y funcionamiento
deben revisarse por separado. La referencia PHP de reCAPTCHA encontrada
en `registro.php` está dentro de un comentario; no se confunde con una
validación activa. No se retiraron servicios externos en este paso.

La auditoría de recursos externos o fuera de los manifiestos permanece
fuera del resultado npm. No se repitió la auditoría sin cambios de
dependencias. Los avisos conocidos de Sass por `@import` son mantenimiento
de compilación, no errores visuales nuevos ni un rediseño pendiente.

No quedan correcciones abiertas de los hallazgos concretos de las guías
025–032. Esto no equivale a certificar todas las operaciones reales o
todos los dispositivos. Un nuevo fallo visual debe revisarse por su caso,
sin repetir suites completas o rehacer correcciones aceptadas.

## Conservación y validación de este cierre

Conservar `storage/backups`: contiene respaldos privados y recursos que
utiliza Apache. No incluirla en el hosting, junto con evidencias de
`storage/visual-review`, `.git`, herramientas/pruebas, `node_modules`,
README raíz, informe de revisión y fuentes de desarrollo. El paquete
necesita recursos compilados actuales y dependencias de producción,
además de las reglas de protección adaptadas al destino.

El paso 98 solo actualizó documentación. Se contrastaron manifiestos,
sincronizador, includes y fuentes de facturas/reporte; las búsquedas no
ejecutaron PHP ni accedieron a la base o al navegador. `git diff --check`
correcto y README raíz/informe siguen ignorados. No se añadieron pruebas,
repitieron suites, recompilaron estilos, conectó SMTP, abrió/recargó
`admin/home.php`, modificaron datos, borraron respaldos, crearon commits,
hizo push o publicó el proyecto.

## Organización de commits locales: paso 99

Se mantuvieron juntos los manifiestos, recursos distribuidos, fuentes Sass,
CSS generado, scripts, plantillas y ajustes visuales asociados a la
migración. La lupa y la limpieza del aviso histórico se guardaron aparte.
Las guías de cada bloque acompañan su código; este cierre y `DEPLOYMENT.md`
se mantienen en un commit documental separado.

| Commit | Contenido |
| --- | --- |
| `756372c` | Migración de recursos y plugins, Bootstrap 5 administrativo, tema habitual compilado, tablas, navegación y ajustes visuales asociados. |
| `59a0e5c` | Lupa de producto con la variante grande disponible o la foto original y clase del marco. |
| `887b1e6` | Limpieza del aviso de venta antigua al cerrar el detalle e importes de descuento sin dividir. |

La preparación comprobó la sintaxis de los 30 archivos PHP modificados
con PHP 8.4 y de ocho scripts propios con Node. La comprobación de espacios
del código propio pasó. En terceros se conservaron finales de línea CRLF
y líneas vacías finales de licencias: la comprobación específica admite
esas características del original. Se compararon 17 recursos distribuidos
con sus copias npm fijadas y la licencia de SweetAlert2, idénticos byte a byte.

No se añadieron pruebas, repitieron suites completas o auditorías npm,
recompilaron estilos, abrieron páginas del navegador ni ejecutaron flujos
de negocio. README raíz, `REVISION_PROYECTO.md`, configuración privada,
respaldos y capturas permanecieron fuera de los commits. No se borraron
respaldos ni se hizo push o despliegue. Las comprobaciones funcionales y
visuales anteriores mantienen el alcance documentado en sus guías.
