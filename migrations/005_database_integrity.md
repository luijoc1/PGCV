# Integridad de la base

Esta migración usa `includes/database_integrity.php` y requiere MariaDB con CHECK activos. Ejecutar las migraciones 001–004 antes de este paso.

1. `php tools/migrate_database_integrity.php --audit`: solo consulta y devuelve conteos, sin datos personales.
2. `php tools/verify_database_integrity.php`: clona estructuras en una base aleatoria aislada, usa filas ficticias y elimina únicamente esa base al terminar. Requiere permiso para crear bases de prueba.
3. `php tools/migrate_database_integrity.php`: crea un respaldo SQL de tablas/datos antes de ejecutar los cambios. DDL no es reversible mediante rollback; un fallo parcial se debe revisar antes de repetir. Los índices y restricciones existentes se detectan por nombre.

Los respaldos quedan en `storage/backups`, fuera del seguimiento de Git. Apache debe respetar su `.htaccess`, que bloquea el acceso HTTP. Guardar también una copia fuera del servidor antes de un despliegue compartido. Restaurar el archivo en una base vacía para comprobarlo, no importarlo sobre datos activos.

Se añaden DECIMAL para precio/total, índices únicos y de consulta, CHECK, valores predeterminados para altas y claves foráneas producto/categoría y detalle/venta. La fecha inicial del carrito deja de actualizarse al modificar cantidades.

No se borra ni reasigna ninguna fila. La migración se detiene si encuentra duplicados, importes que requerirían redondeo u otros datos incompatibles con estas restricciones.

Las relaciones de carrito con usuario/producto y de venta con usuario requieren resolver primero los registros huérfanos existentes. La relación histórica detalle/producto necesita una política de borrado compatible con las instantáneas; no se impone una cascada que destruya ventas. La conversión general de latin1 a utf8mb4 requiere comprobar previamente la codificación de los datos y de la conexión.
