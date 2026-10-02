CREATE TABLE IF NOT EXISTS mail_daily_delivery (
    kind VARCHAR(40) CHARACTER SET ascii NOT NULL,
    delivery_day DATE NOT NULL,
    sent_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (kind,delivery_day)
) ENGINE=InnoDB;
