-- Guardar el vencimiento en UTC. No modifica cuentas existentes.
ALTER TABLE users
    ADD COLUMN activate_expires_at DATETIME NULL DEFAULT NULL;
