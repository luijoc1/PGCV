CREATE TABLE IF NOT EXISTS checkout_requests (
    request_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    user_id INT NOT NULL,
    sales_id INT NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS checkout_sequence (
    id INT PRIMARY KEY,
    last_value BIGINT UNSIGNED NOT NULL
) ENGINE=InnoDB;
INSERT INTO checkout_sequence (id,last_value)
SELECT 1, COALESCE(MAX(CAST(pay_id AS UNSIGNED)),0) FROM sales WHERE pay_id REGEXP '^[0-9]+$'
ON DUPLICATE KEY UPDATE last_value=GREATEST(last_value,VALUES(last_value));
