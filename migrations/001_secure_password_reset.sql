-- Fechas de recuperación guardadas en UTC.
-- Ejecutar una vez en instalaciones que aún no tengan estas columnas.
ALTER TABLE users
    ADD COLUMN reset_token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    ADD COLUMN reset_expires_at DATETIME NULL DEFAULT NULL;
