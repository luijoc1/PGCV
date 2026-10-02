-- Solo para instalaciones que aún no tengan estas columnas.
-- Las ventas antiguas quedan NULL: no se inventan precios históricos.
ALTER TABLE details
    ADD COLUMN product_name TEXT CHARACTER SET utf8mb4 NULL DEFAULT NULL,
    ADD COLUMN original_price DECIMAL(18,2) NULL DEFAULT NULL,
    ADD COLUMN discount_percent DECIMAL(5,2) NULL DEFAULT NULL,
    ADD COLUMN unit_price DECIMAL(18,2) NULL DEFAULT NULL;
