# Preparación de Bootstrap 5 y AdminLTE 4

Esta migración cambia la interfaz. No contiene SQL ni cambia la base de datos.
La preparación está comprobada en una demostración local; la tienda todavía
utiliza Bootstrap 3 y AdminLTE 2, con la mitigación del paso 50.

## Versiones de la demostración

| Componente | Interfaz habitual | Vista previa |
|---|---|---|
| Bootstrap | 3.4.1, runtime reducido | 5.3.8 |
| AdminLTE | 2.4.0 | 4.10.0 |
| DataTables | 1.10.16, integración Bootstrap 3 | 3.1.3, integración Bootstrap 5 |

Se consultaron la [introducción oficial de Bootstrap](https://getbootstrap.com/docs/5.3/getting-started/introduction/),
su [guía de migración](https://getbootstrap.com/docs/5.3/migration/),
la [documentación oficial de AdminLTE 4](https://adminlte.io/themes/v4/docs/introduction.html)
y las [distribuciones de DataTables](https://datatables.net/download/).
Las versiones están fijadas en un manifiesto y un lock independientes.
Las distribuciones y sus licencias se copian desde npm mediante `sync.mjs`.
AdminLTE incluye el CSS de Bootstrap; la demostración carga una sola hoja
base y el bundle JavaScript de Bootstrap, que incorpora Popper.

## Abrir y regenerar la vista previa

Con Apache local funcionando, abrir:

<http://127.0.0.1/PGCV/tests/fixtures/frontend-v5/>

Esta página HTML utiliza exclusivamente datos ficticios. Los formularios
actualizan texto en el navegador y no tienen transporte HTTP, consultas SQL
ni correo. Está dentro de `tests`, protegido mediante `Require local`.
El texto y los importes del resumen son ejemplos estáticos, no una comprobación
nueva del cálculo PHP de descuentos.

Para regenerar los recursos, usar Node 20 o posterior desde el directorio del
proyecto; Node solo se necesita para preparar los archivos, no para servirlos:

```powershell
Set-Location tools/frontend-v5-preview
npm ci --ignore-scripts
npm run sync
```

El sincronizador verifica las cuatro versiones antes de copiar y escribe
únicamente en `tests/fixtures/frontend-v5/assets`. No sustituye `dist`,
`bower_components` ni el manifiesto principal de la tienda. No ejecutar
`npm audit fix --force` en el proyecto habitual para intentar completar esta
migración automáticamente.

## Dependencias que requieren adaptación

La búsqueda en las plantillas PHP encontró atributos antiguos de Bootstrap
en 19 archivos y llamadas jQuery a modales en siete. Es un inventario de texto,
que puede incluir comentarios; el cambio requiere revisar cada componente.

| Área | Archivos de entrada | Adaptación necesaria |
|---|---|---|
| Plantilla pública | `includes/header.php`, `includes/navbar.php`, `includes/scripts.php`, `includes/profile_modal.php` | Cargas, navegación, perfil y estilos compartidos. |
| Tienda | `index.php`, `category.php`, `producto.php`, `cart_ver.php`, `facturacion.php` y formularios de cuenta | Rejilla, carrusel, botones, formularios y tablas adaptables. |
| Plantilla administrativa | `admin/includes/header.php`, `navbar.php`, `menubar.php`, `scripts.php`, `profile_modal.php` | Sustituir estructura AdminLTE 2, skins, navegación y modales. |
| Mantenimiento administrativo | Productos, ofertas, usuarios, categorías, carritos y sus modales | Atributos, clases, llamadas de apertura/cierre y actualización por AJAX. |
| Tablas y reportes | Inicializadores DataTables, ventas y logs | Actualizar núcleo/integración juntos, encabezados, idioma y ajuste de columnas. |
| Fechas | `admin/sales.php`, `admin/sales_print.php`, cargas compartidas | Conservar el contrato del rango; retirar plugins que ya no se utilicen. |

Cambios principales:

- Usar `data-bs-toggle`, `data-bs-target`, `data-bs-dismiss` y los atributos
  de carrusel correspondientes. Cambiar llamadas `.modal()` por la API
  `bootstrap.Modal`, preservando el comportamiento después de AJAX.
- Adaptar `col-xs-*`, `pull-right`, `input-group-addon`, `.close`, `.item`
  del carrusel y estilos de formularios. Revisar CSS propio que utiliza estos
  selectores; una sustitución textual global no basta.
- Usar `app-wrapper`, `app-header`, `app-sidebar`, `app-main`, `app-footer`
  y tarjetas de AdminLTE 4. Cambiar PushMenu/treeview a los atributos
  `data-lte-*`; comprobar el fondo del menú en móvil.
- Conservar inicialmente jQuery para AJAX, el prefiltro CSRF y Select2.
  Bootstrap 5 y AdminLTE 4 ya no necesitan jQuery. Revisar Select2 y Jodit
  dentro de los formularios reales antes de cambiar sus cargas.
- Sustituir los iconos Glyphicon, que Bootstrap 5 no incluye. Font Awesome
  tiene usos propios y requiere una decisión independiente.
- Revisar las llamadas actuales de DataTables, incluyendo tablas insertadas
  o actualizadas dinámicamente y ajustes de columnas al abrir modales.
  La demostración utiliza `new DataTable`, pero no verifica esas rutas AJAX.
- No se encontraron campos activos para los inicializadores datepicker de
  alta/edición, timepicker y rangos de ejemplo adicionales. Confirmar de nuevo
  sus usos al retirar las cargas. El rango de ventas sí tiene un consumidor
  real y debe conservarse.

## Orden de implementación

1. **Plantilla pública y catálogo:** crear las inclusiones y estilos nuevos,
   convertir navegación, carrusel, categorías y detalle. Una página debe
   cargar una sola versión de Bootstrap. Si se convierte por grupos de rutas,
   mantener inclusiones separadas hasta terminar cada grupo completo.
2. **Carrito, facturación y cuenta:** convertir formularios y perfil conservando
   IDs utilizados por JavaScript, nombres de campos, CSRF, métodos y rutas.
   Verificar descuento, cantidades y resumen con datos aislados; la revisión
   en la base habitual termina antes de confirmar un pedido o enviar correo.
3. **Administración:** convertir la estructura compartida y todas sus páginas
   consumidoras, modales y tablas. El panel puede iniciar automáticamente
   una alerta: comprobarlo con un transporte controlado. No abrirlo como
   prueba si puede enviar correo sin autorización.
4. **Reportes por fecha:** sustituir Date Range Picker por dos campos nativos
   si la comprobación de sus consumidores confirma que basta. Generar el
   campo `date_range` esperado por el servidor: `MM/DD/YYYY - MM/DD/YYYY`.
   Validar el orden también en el servidor y preservar el intervalo que
   incluye todo el último día. Formatear cadenas sin conversiones de zona
   horaria; la demostración comprueba este formato, no ejecuta un reporte.
5. **Cierre:** retirar cargas antiguas y plugins sin consumidores, actualizar
   el manifiesto principal y su sincronizador, revisar acceso HTTP a recursos
   retirados y repetir la auditoría de las dependencias realmente utilizadas.
   Mantener commits separados con mensajes en inglés.

Después de cada bloque, revisar escritorio y 375 px, consola, teclado,
formularios y contenido con textos largos. Comprobar las rutas modificadas
con los casos existentes relevantes; añadir pruebas solo cuando haya un caso
concreto. Conservar precios históricos, autorización y lógica de servidor.

La reversión de un bloque de interfaz utiliza su commit y recursos anteriores.
Esta preparación no requiere restaurar la base. Los recursos antiguos solo
se retiran cuando ya no los consume ninguna plantilla.

## Resultado de esta preparación

Se verificaron búsqueda (seis resultados Motor de doce), paginación, apertura
y cierre del modal, aplicación ficticia de un descuento, desplegable de cuenta,
árbol lateral, cierre de aviso, pestañas y panel de detalles. El rango válido
genera `10/01/2026 - 10/02/2026`; el rango invertido muestra un error.

En 375 px, el documento mide 375 px; la tabla desplaza su contenido dentro
del contenedor. El menú lateral abre y cierra mediante su fondo, y el modal
cabe dentro del ancho de pantalla. Se restauró el tamaño del navegador.
La consola no registró avisos ni errores. Evidencias locales privadas:
`storage/visual-review/frontend-v5-escritorio.png` y
`storage/visual-review/frontend-v5-modal-movil.png`.

`npm audit --json --ignore-scripts` devuelve cero avisos para el lock de esta
demostración. Esto no limpia la auditoría principal ni certifica las copias
manuales de otros plugins. La migración de la tienda sigue pendiente hasta
terminar los bloques anteriores; no se confirmaron pedidos ni enviaron correos.
