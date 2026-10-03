# Bootstrap 5 público conservando el diseño

## Alcance de esta fase

La parte pública carga el bundle JavaScript de Bootstrap 5.3.8 con Popper.
Se mantienen los colores, tamaños, rejilla, tipografía y hojas de estilo de
la tienda. `dist/css/bootstrap5-public-compat.css` adapta los estados `show`
y las transiciones de carrusel al tema existente.

Esta combinación es una fase transitoria propia de PGCV. No es una
combinación de versiones certificada por Bootstrap ni la migración completa:
los estilos de Bootstrap 3/AdminLTE 2, DataTables y el JavaScript
administrativo todavía requieren revisión y actualización. La auditoría
principal sigue informando un paquete moderado, `bootstrap` 3.4.1.

La dependencia nueva usa el alias npm `bootstrap5`, fijado en
`npm:bootstrap@5.3.8`, para conservar por separado el runtime administrativo.
Ninguna página pública debe cargar también `dist/js/bootstrap-pgcv.js`.
Administración sigue cargando su runtime reducido del paso 50.

## Cambios realizados

- El menú público utiliza `data-bs-*`; sus categorías permiten navegación
  con flechas y cierre con Escape.
- Los carruseles de inicio y Nosotros utilizan atributos de Bootstrap 5 y
  `carousel-item`, conservando también la clase visual `item` y los controles
  existentes. Las transiciones respetan la preferencia de movimiento reducido.
- Los modales de perfil utilizan `data-bs-dismiss`, nombre accesible y
  `tabindex=-1` para el control de foco. La consulta de transacciones utiliza
  `bootstrap.Modal.getOrCreateInstance(...).show()/hide()`.
- Se conservan los campos de formulario, CSRF, rutas, AJAX, precios y lógica
  de servidor. jQuery sigue disponible para AJAX y DataTables.

Referencias oficiales: [migración de Bootstrap](https://getbootstrap.com/docs/5.3/migration/),
[menús desplegables](https://getbootstrap.com/docs/5.3/components/dropdowns/)
y [carrusel](https://getbootstrap.com/docs/5.3/components/carousel/).
Las reglas de transición adaptadas y la distribución conservan su licencia MIT.

## Generación y verificación

Desde la raíz del proyecto:

```powershell
npm ci --ignore-scripts
npm run sync:frontend
```

El sincronizador valida versiones, incluidos los alias npm, antes de copiar.
Distribución y mapa nuevos en `bower_components/bootstrap5/dist/js/` y
licencia en `bower_components/bootstrap5/LICENSE`.

`tests/fixtures/bootstrap5_public_modals.php` incluye la plantilla real de
modales con datos ficticios y las mismas hojas base. No carga configuración,
conexión a la base, sesión persistente ni correo; intercepta formularios y
rechaza peticiones que no sean GET. Está protegido mediante `Require local`.
No sustituye una comprobación del perfil real autenticado ni del AJAX de
transacciones. No debe incluirse en el paquete para hosting.

La revisión compara la portada antes y después y comprueba menú, teclado,
carruseles y modales en escritorio y 375 px. Las capturas locales están bajo
`storage/visual-review/`, ignoradas por Git y protegidas por HTTP.

La reversión de esta fase debe restaurar juntos los atributos de las
plantillas y el script público anterior; no requiere restaurar la base.

## Continuación

Revisar los estilos públicos manteniendo la misma apariencia, convertir
administración y sus plugins en un bloque coherente, y retirar Bootstrap 3
solo después de comprobar que no quedan consumidores. No presentar la
auditoría como resuelta mientras el paquete antiguo siga siendo necesario.
