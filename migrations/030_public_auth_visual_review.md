# Revisión visual de formularios públicos

Paso 95, 4 de octubre de 2026. Se revisaron las plantillas de acceso
(`login.php`), registro (`registrarse.php`), solicitud de recuperación
(`password_olvidada.php`) y nueva contraseña (`password_restablecer.php`)
a 1280 y 320 px. No se encontraron errores visuales que requieran cambios
de código o estilos. Se conserva el diseño habitual de cada formulario.

## Campos, botones y avisos

Las tarjetas miden 360 px en escritorio y aproximadamente 288.36 px
en móvil. El ancho del documento coincide con el viewport en las ocho
vistas normales y las ocho variantes con un aviso de error ficticio.
Los avisos dividen el texto en líneas dentro de la tarjeta y desplazan
el formulario hacia abajo, sin cubrir sus controles.

Nombre y apellido mantienen dos columnas, con campos de 150 px en
escritorio y aproximadamente 114.18 px en móvil, separados por 12 px.
La casilla y el enlace de términos caben en el registro; Crear cuenta
permanece debajo. No se marcó la casilla ni se siguió el enlace.

Ingresar, Crear cuenta y Enviar enlace conservan el ancho de sus campos:
312 px en escritorio y aproximadamente 240.36 px en móvil, con una altura
de 45.79 px. Sus iconos y los iconos interiores de los campos permanecen
alineados. Los enlaces inferiores caben sin solaparse.

El formulario de nueva contraseña conserva su presentación habitual y
sus iconos a la derecha: los dos indicadores comparten posición vertical
y altura de aproximadamente 34 px con sus campos. Guardar contraseña
mantiene 320.02 px de ancho en escritorio y 248.38 px en móvil, con
34.41 px de alto. No se introdujeron contraseñas.

## Aislamiento, evidencias y límites

La vista temporal admitía únicamente GET desde localhost y una lista
cerrada de cuatro plantillas. Renderizaba sus cuerpos reales y los
includes públicos sin cargar configuración, base de datos o sesión
persistente. Se omitieron los guards previos al body; los parámetros de
recuperación eran ficticios, sin crear ni consumir tokens reales.

Formularios y enlaces estaban bloqueados. La única llamada AJAX admitida,
la lectura automática de `cart_obtener.php`, devolvía un carrito ficticio
vacío; cualquier otro destino se rechazaba. Los SDK externos de pago y
CAPTCHA se omitieron solo en la vista temporal. La revisión acredita la
presentación, no autenticación, creación de cuentas, validación de enlaces,
entrega de correo ni persistencia. No se abrieron los endpoints de envío.

Capturas privadas en `storage/visual-review/`:

- `auth-signup-desktop.png` y `auth-signup-notice-320.png`.
- `auth-login-320.png`.
- `auth-forgot-desktop.png` y `auth-forgot-320.png`.
- `auth-reset-desktop.png` y `auth-reset-320.png`.

La captura normal de registro móvil, la de acceso de escritorio y la de
nueva contraseña con aviso móvil agotaron el tiempo de la herramienta;
esas variantes sí tienen métricas y estado DOM. Las capturas restantes
permitieron inspeccionar su apariencia. No se acredita un móvil físico.
Métricas de las dieciséis vistas y consola sin errores o avisos en
`storage/backups/auth-visual-review/metrics.json`, fuera de Git y hosting.

Sintaxis PHP 8.4 de la vista temporal y `git diff --check` correctos.
No se añadieron pruebas permanentes, repitieron suites, recompilaron estilos
o ejecutó otra auditoría npm: esta fase solo añade documentación. La vista
temporal se retiró y devuelve HTTP 404; se restauró el viewport y se cerró
la pestaña creada.

No se abrió ni recargó `admin/home.php`, guardaron formularios, iniciaron
sesiones, crearon cuentas, cambiaron contraseñas, confirmaron pedidos o
enviaron correos. No se borraron respaldos, crearon commits, hizo push o
publicó el proyecto. Continúa la revisión de contacto y páginas públicas
restantes por casos concretos, con los envíos bloqueados.
