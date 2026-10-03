# Preparación para hosting

Se prevé publicar el proyecto en un hosting, pero todavía no se ha elegido
proveedor ni dominio. No se han subido archivos, creado una base de datos
de producción ni instalado tareas programadas en otro servidor.

## Uso local mientras se prepara la publicación

El proyecto puede seguir funcionando en el XAMPP actual. Para usarlo en este
equipo, iniciar Apache y MariaDB y abrir http://localhost/PGCV. No hace
falta contratar un hosting para este uso local.

La carpeta `.git` guarda el historial de cambios, los commits y la
configuración del repositorio. Es útil durante el desarrollo y debe
conservarse en el equipo. Su descarga por HTTP está bloqueada para evitar
que los visitantes obtengan el repositorio; ese bloqueo no impide usar Git.
La carpeta no se necesita para ejecutar la tienda en el hosting.

## Comprobación inicial con PHP 8.2

El 2 de octubre de 2026 se comprobó el proyecto con PHP CLI 8.2.12 del otro
XAMPP instalado, manteniendo el sitio habitual en PHP 7.4.30. No se sustituyó
PHP ni Apache. Se utilizó una copia local del archivo de configuración,
fuera de Git y bloqueada por HTTP, con GD activado y todos los niveles de
errores habilitados. La variable PHPRC se aplicó solo al proceso de prueba
y a sus procesos secundarios.

- Los 184 archivos PHP propios pasaron sintaxis y las dependencias de
  producción pasaron la comprobación de plataforma de Composer.
- Pasaron las 278 pruebas PHP con 3666 aserciones, las 49 comprobaciones de
  integración y las 29 de integridad histórica, utilizando datos ficticios.
- Se corrigió el límite de tiempo SMTP: ahora se configura en la instancia
  SMTP que lo utiliza, evitando una propiedad dinámica deprecada en PHP 8.2.
  Las siete pruebas de correo también pasan en PHP 7.4.
- No se enviaron correos reales ni se modificaron los datos habituales.

PHP 8.2.12 es un parche antiguo y no se propone como versión de producción.
Esta primera comprobación no certificaba otras versiones ni las reglas Apache, red,
base de datos o configuración del futuro hosting. Cuando se elija proveedor,
se deberá verificar su versión actualizada y exacta en un entorno aislado.

## Comprobación con PHP 8.4

El 2 de octubre de 2026 se descargó PHP 8.4.26, NTS x64 para Windows, desde
la [distribución oficial](https://www.php.net/downloads.php?os=windows&version=8.4).
Se verificó el SHA256 publicado:

```text
da68394f9193b7f6b89d0c76861a4034ae10efee7fd55a7255d8118c2acf70d7
```

El runtime y su configuración están aislados en
`storage/backups/php84-compat`, fuera de Git y bloqueados por HTTP. Solo se
utilizó mediante CLI y los servidores temporales de las pruebas; no se
modificaron los PHP/Apache instalados ni la configuración global de Windows.
Se activaron las extensiones necesarias y E_ALL en su archivo php.ini.

Se hicieron explícitos los tipos que admiten null en los callbacks opcionales
de pedidos y alertas. La copia manual de reCAPTCHA, actualmente sin referencias
en las rutas de la aplicación, recibió el mismo ajuste en tres constructores.
Estos cambios también pasan sintaxis en PHP 7.4.

Resultados con PHP 8.4.26:

- 184 archivos PHP revisados, sin errores de sintaxis ni avisos.
- Requisitos de producción de Composer correctos.
- 278 pruebas PHP y 3666 aserciones correctas.
- 49 comprobaciones de integración, 29 históricas y 15 del comando de
  migración/respaldo correctas con datos ficticios y bases aisladas.
- 13 pruebas de pedidos/correo y 103 aserciones también correctas en PHP 7.4.

No se enviaron correos reales, confirmaron pedidos ni modificaron los datos
habituales. El resultado verifica esta configuración CLI de Windows y los
flujos cubiertos; falta comprobar Apache o PHP-FPM, certificados, red y
versiones de base de datos en el futuro hosting. La edición NTS probada no
se debe usar como reemplazo directo del módulo PHP de Apache de XAMPP.

## Prueba de PHP 8.4 como módulo de Apache

Se verificó también PHP 8.4.26 **Thread Safe x64** con el Apache 2.4.54 del
equipo, mediante un proceso de prueba separado en un puerto aleatorio de
`127.0.0.1`. La descarga oficial TS coincidió con este SHA256:

```text
6e56f0e932e92bfdce208d3a7e6068f6e2f7a19fc2922a5f856fb085f61673f3
```

La herramienta Windows `tools/verify_php84_apache.ps1` genera configuración,
sesiones, archivos ficticios y evidencia bajo `storage/backups`, sin cambiar
los archivos de configuración de XAMPP. Usa el runtime TS en
`storage/backups/php84-apache/runtime`. La consulta a la base habitual se
realiza en una transacción de solo lectura; imagen y PDF usan contenido
ficticio y se generan en memoria. El correo solo se configura: no conecta
a SMTP ni envía mensajes.

Pasaron 14 comprobaciones: configuración Apache válida, respuesta HTTP,
versión/SAPI Thread Safe, php.ini aislado, extensiones, integridad de lectura,
PNG/PDF, configuración SMTP, persistencia de sesión, bloqueos de repositorio
y migraciones, pausa HTTP 503, ausencia de errores PHP y hashes intactos de
las dos configuraciones principales de Apache. El proceso de prueba se
cierra en `finally`; el sitio habitual permanece disponible.

El Apache antiguo contiene DLL de dependencias que pueden interferir con
cURL del PHP nuevo. La configuración de prueba carga explícitamente las
bibliotecas del runtime TS antes del módulo PHP. No copiar estas DLL sobre
las de Apache ni sustituir su configuración habitual sin preparar respaldo
y comprobar las demás aplicaciones servidas, incluido phpMyAdmin.

Esta prueba valida el módulo y componentes indicados; no cambia el PHP del
sitio principal ni certifica un hosting o servidor Apache actualizado.

## PHP 8.4 activado en el Apache local de XAMPP

El 2 de octubre de 2026, con autorización, se activó PHP 8.4.26 TS en el
Apache habitual de `C:/xampp2`. PGCV y el alias local de phpMyAdmin 5.2.3
responden HTTP 200. Solo se sustituyó `apache/conf/extra/httpd-xampp.conf`;
MariaDB y sus datos permanecieron intactos. El panel de XAMPP puede seguir
usándose para iniciar Apache. La CLI y el alias CGI anterior siguen en PHP 7.4.

Pasaron 13 comprobaciones de activación: pausa HTTP 503 antes del cierre,
versión/SAPI TS, php.ini elegido, extensiones/PNG/PDF/configuración SMTP,
integridad de lectura, portadas PGCV/phpMyAdmin, tres bloqueos de archivos
privados, ausencia de errores PHP y hashes de configuración coherentes con
el único archivo sustituido y el php.ini original intacto. El fixture temporal
de comprobación se eliminó; la pausa quedó desactivada. No se confirmaron
pedidos ni enviaron correos. Puede ser necesario iniciar sesión otra vez,
porque PHP utiliza ahora un directorio de sesiones independiente.

Se repitió `tools/check_smtp_connection.php` mediante el ejecutable PHP
8.4.26 TS y el mismo php.ini activo en Apache. Gmail en el puerto 465
validó el certificado TLS y autenticó correctamente con OpenSSL 3.0.22.
La herramienta solo conecta y autentica: no define destinatarios ni envía
mensajes. Esta comprobación mediante CLI no verifica la entrega de correo
ni reemplaza la prueba desde el futuro hosting.

El cierre fue ordenado mediante el evento de la instancia identificada de
Apache, cuyo [mecanismo está en el código oficial](https://github.com/apache/httpd/blob/2.4.x/server/mpm/winnt/mpm_winnt.c).
El comando `httpd -k shutdown` no corresponde a este arranque sin servicio
Windows. No se instalaron servicios ni se terminaron procesos de otros XAMPP.
Se conserva el aviso previo del certificado local de ejemplo; HTTPS para
publicación sigue pendiente de configurar y verificar en el hosting.

**Conservar las carpetas locales que Apache está utilizando:**

- `storage/backups/php84-apache/runtime`: PHP 8.4 y sus bibliotecas.
- `storage/backups/phpmyadmin84-compat/phpMyAdmin-5.2.3-all-languages`: phpMyAdmin y su configuración privada.
- `storage/backups/php84-xampp-9e6f2e2861ed43b8b600140fb2522101`: php.ini activo, sesiones, registros, evidencia y configuración anterior.

Aunque estén bajo la carpeta de respaldos, no borrarlas durante una limpieza:
el Apache local depende de esas rutas. Su acceso HTTP directo está bloqueado
y permanecen fuera de Git. No se deben incluir en los archivos del hosting.

### Preparación y respaldo del cambio

`tools/prepare_php84_xampp.ps1` copia los archivos .conf de Apache y el
php.ini anterior a un directorio protegido bajo `storage/backups`. Genera
un candidato de `httpd-xampp.conf` y un php.ini independiente. Comprueba
la configuración completa en puertos HTTP/HTTPS aleatorios de loopback,
con PID, sesiones, caché SSL y registros propios; cierra su proceso al acabar.
El arranque se comprobó desde el directorio bin de Apache, sin anteponer el
runtime al PATH: se cargan explícitamente también brotlicommon/brotlidec,
necesarios para cURL. No instala el candidato ni reinicia el Apache habitual.

El phpMyAdmin 5.2.0 instalado respondió, pero produjo 78 avisos de funciones
deprecadas con PHP 8.4. Se preparó una copia privada de phpMyAdmin 5.2.3,
con la misma configuración de conexión. El ZIP de todas las traducciones
se obtuvo de la [descarga oficial](https://www.phpmyadmin.net/downloads/)
y coincidió con su SHA256 publicado:

```text
2d2e13c735366d318425c78e4ee2cc8fc648d77faba3ddea2cd516e43885733f
```

Las [notas de la versión](https://www.phpmyadmin.net/news/2025/10/8/phpmyadmin-523-is-released/)
incluyen correcciones para PHP 8.4. La comprobación local de su portada
no certifica todas las funciones de administración de bases de datos.
La copia y sus credenciales están fuera de Git y bloqueadas por HTTP,
salvo el alias de prueba restringido a conexiones locales.

Para reproducir la preparación con esa copia, desde la raíz del proyecto:

```powershell
./tools/prepare_php84_xampp.ps1 -PhpMyAdminRuntime ./storage/backups/phpmyadmin84-compat/phpMyAdmin-5.2.3-all-languages
```

Pasaron 12 comprobaciones: sintaxis Apache completa, módulo PHP 8.4 TS,
selección del php.ini, componentes de PGCV, integridad de lectura, portada
de PGCV, bloqueo de archivos privados, portada de phpMyAdmin, ausencia de
errores PHP antes y después de phpMyAdmin, y hashes intactos de los archivos
.conf y del php.ini original. No se entró al panel administrativo de PGCV,
que puede disparar correos; no se confirmaron pedidos ni enviaron mensajes.
Los puertos HTTPS se cargaron en la configuración; no se certificó el
certificado local ni la configuración TLS para publicación.

El candidato cambia el módulo PHP de Apache, su PHPRC/PHPIniDir y el alias
local de phpMyAdmin. Conserva los límites de memoria y subida anteriores,
usa la zona horaria de Bogotá y el archivo CA existente. La CLI de XAMPP
y el alias CGI anterior siguen en PHP 7.4; este cambio solo afecta al
módulo Apache. Los runtimes preparados deben permanecer en sus rutas
locales mientras Apache los utilice; no forman parte del paquete del hosting.

Para repetir la preparación en otro entorno: comprobar que la configuración
actual coincide con el respaldo, revisar el candidato antes de sustituirlo y
verificar versión/SAPI, tienda, phpMyAdmin y logs después del reinicio.
El script privado de activación registra la operación ya realizada; no se
debe ejecutar nuevamente como herramienta genérica.

Para volver al PHP 7.4 anterior, detener el Apache de `C:/xampp2` y restaurar
únicamente `original/extra/httpd-xampp.conf` del respaldo
`storage/backups/php84-xampp-9e6f2e2861ed43b8b600140fb2522101` en
`C:/xampp2/apache/conf/extra/httpd-xampp.conf`. Validar la configuración,
arrancar ese Apache y comprobar PGCV/phpMyAdmin. El PHP anterior y phpMyAdmin
anterior permanecen instalados. No restaurar la base de datos: este cambio
de configuración no migra datos.

## Requisitos del hosting

- PHP con PDO MySQL, OpenSSL, DOM, Mbstring, Fileinfo, GD y cURL.
  `composer check-platform-reqs --no-dev` comprueba los requisitos de las
  dependencias, pero no todas las funciones de la aplicación. Las pruebas
  locales se realizaron con PHP 7.4.30, una versión sin soporte. Elegir una
  versión con soporte según <https://www.php.net/supported-versions.php>
  y comprobar el proyecto en una copia aislada con la versión exacta del
  hosting antes de publicarlo.
- MariaDB con InnoDB, restricciones CHECK activas y claves foráneas. La base
  actual se verificó con MariaDB 10.4.25. Si el proveedor ofrece MySQL, hay
  que comprobar por separado la importación y compatibilidad del esquema.
- HTTPS para el dominio público, un archivo de certificados CA válido en
  PHP y conexión SMTP saliente al servidor y puerto configurados. Gmail
  validó TLS y autenticación localmente; faltan la comprobación desde el
  hosting y la entrega de un mensaje autorizado.
- Apache 2.4 que respete .htaccess, o reglas equivalentes en el servidor
  del proveedor. Las directivas php_flag actuales corresponden al módulo
  PHP de XAMPP; algunos hostings con PHP-FPM las rechazan. En esos casos,
  configurar los errores de PHP mediante el mecanismo del proveedor.
- Permisos de escritura de PHP en images y en el almacenamiento protegido
  de respaldos. Las fotos admiten hasta 5 MiB y 20 millones de píxeles,
  sujetos también a los límites del hosting. Evitar permisos de escritura
  generales sobre toda la aplicación.
- Composer mediante SSH o una instalación local reproducible de las
  dependencias de producción. Las herramientas de migración y respaldo
  necesitan PHP CLI y permiso para crear una base aislada de restauración;
  confirmar que el proveedor permite estas operaciones.

## Archivos y configuración de la publicación

1. Preparar los archivos desde la revisión de Git comprobada. Excluir .git,
   carpetas locales de desarrollo, node_modules, pruebas, README raíz,
   informe de revisión y respaldos locales. Mantener las instrucciones
   de migración fuera del acceso público.
2. Instalar las dependencias fijadas con
   `composer install --no-dev --prefer-dist --optimize-autoloader` en el
   entorno de publicación. Conservar el archivo de versiones fijadas.
   Un clon limpio requiere esta instalación: los metadatos de Composer
   rastreados por Git no contienen todos los archivos de las dependencias.
3. Los recursos de interfaz están incluidos en bower_components y dist;
   el servidor no necesita Node para ejecutar la tienda. Para regenerarlos
   antes de preparar los archivos, usar `npm ci --ignore-scripts` y
   `npm run sync:frontend`.
4. Crear includes/config.php específico del hosting a partir del ejemplo.
   Configurar las credenciales de su base de datos, APP_URL con HTTPS,
   destinatarios de alertas/contacto, credenciales SMTP y certificados CA.
   Mantener las credenciales fuera de Git, del chat y de documentos públicos.
5. Ajustar la condición de mantenimiento en .htaccess si la aplicación no
   está en /PGCV dentro del directorio público del servidor. Comprobar que
   devuelve HTTP 503 al activar la pausa. Detener también las escrituras
   externas y esperar a que terminen las peticiones anteriores.

## Traslado o instalación de la base de datos

- Para empezar sin datos, importar únicamente migrations/000_schema.sql
  en una base nueva y vacía y crear el administrador con la herramienta CLI.
  El esquema no contiene clientes, productos ni credenciales. No volver a
  ejecutar sobre él las migraciones que crean esas mismas columnas.
- Para conservar el catálogo, cuentas e historial existentes, transferir
  un respaldo nuevo y verificado por un canal privado. Importarlo únicamente
  en una base vacía del hosting, comprobar su contenido y configurar la
  aplicación para utilizarla. No importar el esquema limpio encima del
  respaldo restaurado.
- La migración histórica local está aplicada: cinco carritos archivados,
  17 ventas con la referencia original de la cuenta ausente y las seis
  claves foráneas adicionales activas. No repetirla con los conteos antiguos.
- Conservar una copia verificada del respaldo fuera del servidor web.
  Contiene datos reales y debe quedar fuera de Git y del acceso HTTP público.

## Comprobaciones antes de publicar

Comprobar HTTPS y certificados, bloqueo HTTP de .git, vendor, herramientas,
pruebas, migraciones, respaldos y manifiestos; bloqueo de scripts en imágenes;
inicio de sesión y roles; catálogo; precios y descuentos del carrito;
resumen de facturación; acceso a facturas PDF e informes históricos.
Verificar la integridad de la base y el comportamiento con las versiones de
PHP y base de datos del hosting. Las comprobaciones que escriben datos deben
usar una copia aislada.

Confirmar pedidos y enviar correos reales requieren autorización aparte.
El panel administrativo puede solicitar automáticamente una alerta de
inventario: comprobarlo con un transporte de correo controlado o permiso
explícito de envío. El diagnóstico SMTP solo conecta y autentica; no envía
mensajes.

Bootstrap/AdminLTE y TCPDF conservan las limitaciones de soporte documentadas
en la revisión. La mitigación de los componentes cargados de Bootstrap no
elimina el aviso de npm. La integración de una pasarela de pago también sigue
pendiente.

La parte pública utiliza JavaScript y CSS de Bootstrap 5, con estilos
compilados mediante Sass para conservar el diseño actual. El tema AdminLTE 2,
DataTables y administración siguen pendientes; la auditoría aún señala
Bootstrap 3. El [alcance de JavaScript](migrations/010_public_bootstrap_javascript.md)
y la [compilación y reversión de estilos](migrations/011_public_bootstrap_styles.md)
explican esta transición. Sass es una dependencia de desarrollo: instalar
también las dependencias de desarrollo al regenerar recursos; el hosting
recibe el CSS ya compilado. Excluir del paquete la comprobación local de `tests`.

La publicación requiere proveedor y dominio elegidos, entorno comprobado,
archivos revisados y autorización explícita para subir y publicar el proyecto.
