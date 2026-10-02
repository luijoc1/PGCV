-- Esquema actual sin datos personales ni credenciales. Importar en una base vacia.
-- Las migraciones 001-006 ya estan incorporadas.
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` int(1) NOT NULL DEFAULT 0,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `address` text NOT NULL DEFAULT '',
  `contact_info` varchar(100) NOT NULL DEFAULT '',
  `photo` varchar(200) NOT NULL DEFAULT '',
  `status` int(1) NOT NULL DEFAULT 0,
  `activate_code` varchar(15) NOT NULL DEFAULT '',
  `reset_code` varchar(15) NOT NULL DEFAULT '',
  `created_on` date NOT NULL,
  `reset_token_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `cat_slug` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_slug` (`cat_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `slug` varchar(200) NOT NULL,
  `price` decimal(18,2) NOT NULL,
  `descuento` int(3) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 5,
  `photo` varchar(200) NOT NULL DEFAULT '',
  `date_view` date NOT NULL DEFAULT curdate(),
  `counter` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_slug` (`slug`),
  KEY `ix_products_category` (`category_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  CONSTRAINT `ck_products_values` CHECK (`price` >= 0 and `stock` >= 0 and `stock_minimo` >= 0 and `descuento` between 0 and 100)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pay_id` varchar(50) NOT NULL,
  `nombre_facturacion` varchar(100) NOT NULL,
  `documento` varchar(30) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `metodo_pago` varchar(30) NOT NULL,
  `total` decimal(18,2) NOT NULL,
  `sales_date` datetime NOT NULL,
  `fecha_hora_inicio` timestamp NULL DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','enviado','entregado') NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sales_pay_id` (`pay_id`),
  KEY `ix_sales_user_date` (`user_id`,`sales_date`),
  KEY `ix_sales_date` (`sales_date`),
  CONSTRAINT `ck_sales_total` CHECK (`total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `fecha_hora_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_user_product` (`user_id`,`product_id`),
  KEY `ix_cart_product` (`product_id`),
  CONSTRAINT `ck_cart_quantity` CHECK (`quantity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_name` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `original_price` decimal(18,2) DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `unit_price` decimal(18,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_details_sale` (`sales_id`),
  KEY `ix_details_product` (`product_id`),
  CONSTRAINT `fk_details_sale` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`id`),
  CONSTRAINT `ck_details_quantity` CHECK (`quantity` > 0),
  CONSTRAINT `ck_details_snapshot` CHECK (`product_name` is null and `original_price` is null and `discount_percent` is null and `unit_price` is null or `product_name` is not null and `original_price` is not null and `discount_percent` is not null and `unit_price` is not null and `original_price` >= 0 and `discount_percent` between 0 and 100 and `unit_price` >= 0 and `unit_price` <= `original_price`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `login_attempts` (
  `bucket_key` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `window_started` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`bucket_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `checkout_requests` (
  `request_key` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  `sales_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`request_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `checkout_sequence` (
  `id` int(11) NOT NULL,
  `last_value` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mail_daily_delivery` (
  `kind` varchar(40) CHARACTER SET ascii NOT NULL,
  `delivery_day` date NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`kind`,`delivery_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `logs_login` (
  `id_registro` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_operacion` enum('EXITOSO','FALLIDO','BLOQUEADO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_created` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_hora_accion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_registro`),
  KEY `idx_email` (`email`),
  KEY `idx_tipo_operacion` (`tipo_operacion`),
  KEY `idx_ip` (`ip`),
  KEY `idx_fecha_hora_accion` (`fecha_hora_accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs_productos` (
  `id_registro` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_referencia` bigint(20) unsigned NOT NULL,
  `informacion_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`informacion_anterior`)),
  `nueva_informacion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nueva_informacion`)),
  `tipo_operacion` enum('INSERT','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_created` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_hora_accion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_registro`),
  KEY `idx_id_referencia` (`id_referencia`),
  KEY `idx_tipo_operacion` (`tipo_operacion`),
  KEY `idx_usuario_created` (`usuario_created`),
  KEY `idx_fecha_hora_accion` (`fecha_hora_accion`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs_usuarios` (
  `id_registro` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_referencia` bigint(20) unsigned NOT NULL,
  `informacion_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`informacion_anterior`)),
  `nueva_informacion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nueva_informacion`)),
  `tipo_operacion` enum('INSERT','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_created` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_hora_accion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_registro`),
  KEY `idx_id_referencia` (`id_referencia`),
  KEY `idx_tipo_operacion` (`tipo_operacion`),
  KEY `idx_usuario_created` (`usuario_created`),
  KEY `idx_fecha_hora_accion` (`fecha_hora_accion`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs_ventas` (
  `id_registro` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_referencia` bigint(20) unsigned NOT NULL,
  `informacion_anterior` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`informacion_anterior`)),
  `nueva_informacion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nueva_informacion`)),
  `tipo_operacion` enum('INSERT','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_created` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_hora_accion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_registro`),
  KEY `idx_id_referencia` (`id_referencia`),
  KEY `idx_tipo_operacion` (`tipo_operacion`),
  KEY `idx_usuario_created` (`usuario_created`),
  KEY `idx_fecha_hora_accion` (`fecha_hora_accion`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO checkout_sequence (id,last_value) VALUES (1,0);
