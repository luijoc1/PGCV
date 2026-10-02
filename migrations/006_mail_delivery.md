# Correos y alertas

Ejecutar `php tools/migrate_mail_delivery.php` para preparar el registro diario. Requiere también la tabla `login_attempts` del paso 003 para el límite del formulario de contacto.

Los envíos comparten `includes/mailer.php`, que exige certificados válidos, cifra SMTP y limita los tiempos. PHP debe tener un almacén de certificados actualizado en `openssl.cafile`; opcionalmente definir `MAIL_CA_FILE` en `includes/config.php`. Nunca desactivar la verificación para solucionar un fallo de certificados.

Configuración opcional en `includes/config.php`, que está excluido de Git:

```php
define('APP_URL', 'https://tu-dominio.example/PGCV');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 465);
define('MAIL_ENCRYPTION', 'ssl'); // 'tls' para STARTTLS, con su puerto correspondiente.
define('MAIL_ALERT_TO', 'inventario@tu-dominio.example');
define('MAIL_CONTACT_TO', 'contacto@tu-dominio.example');
```

También se admite `PGCV_APP_URL` como variable de entorno cuando APP_URL no está definida. El valor predeterminado sigue siendo `http://localhost/PGCV` para esta instalación local; se debe configurar una URL accesible a los destinatarios antes de usarla en otro equipo. No se deriva la URL del encabezado Host del navegador.

El panel administrativo y `php alarm_stock_minimo.php` usan el mismo registro persistente: una alerta exitosa al día, con fecha de Bogotá. Un fallo no marca el día como enviado. La tarea CLI puede ejecutarse desde el programador de tareas; este paso no instala una tarea ni ejecuta envíos reales.

El remitente del contacto es la cuenta SMTP autenticada y el email del cliente se usa solo como Reply-To validado. MAIL_CONTACT_TO conserva el destinatario anterior si no se configura; MAIL_ALERT_TO usa la cuenta MAIL_USER por defecto. Las pruebas usan transportes simulados; la entrega real requiere comprobar credenciales y conectividad sin desactivar TLS.
