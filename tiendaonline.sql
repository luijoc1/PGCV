-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-05-2025 a las 15:16:02
-- Versión del servidor: 10.4.25-MariaDB
-- Versión de PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------


--
-- Base de datos: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
--
-- Base de datos: `tienda`
--
CREATE DATABASE IF NOT EXISTS `tienda` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `tienda`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(2, 13, 14, 1),
(3, 13, 13, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cat_slug` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `category`
--

INSERT INTO `category` (`id`, `name`, `cat_slug`) VALUES
(2, 'MOTORES', 'MOTORES'),
(3, 'BATERIA', 'BATERIA'),
(16, 'FILTRO DE AIRE', 'TECLADOS'),
(17, 'AMORTIGUADORES', 'AMORTIGUADORES'),
(19, 'LLANTAS', 'LLANTAS'),
(20, 'FRENOS', 'FRENOS'),
(21, 'OTROS', 'OTROS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `details`
--

CREATE TABLE `details` (
  `id` int(11) NOT NULL,
  `sales_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `slug` varchar(200) NOT NULL,
  `price` double NOT NULL,
  `photo` varchar(200) NOT NULL,
  `date_view` date NOT NULL,
  `counter` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `slug`, `price`, `photo`, `date_view`, `counter`) VALUES
(13, 20, 'DISCOS SERAMICO', '<h2><strong>Durante el frenado, la energ&iacute;a cin&eacute;tica del veh&iacute;culo se convierte en calor debido a la fricci&oacute;n entre las pastillas y el disco</strong>.</h2>\r\n', 'discos-seramico', 180000, 'discos-seramico_1748424046.jpg', '2025-05-15', 2),
(14, 20, 'Pastillas de freno semimetálicas ', '<h2>Las pastillas semimet&aacute;licas est&aacute;n compuestas de metales blandos (lana de acero, polvo de hierro o cobre mezclado con cargas inorg&aacute;nicas) que se incrustan en el material de fricci&oacute;n que mejora el frenado. Son duraderas y aguantan bien el alto rendimiento. Pueden desgastar rotores normales con m&aacute;s rapidez que los org&aacute;nicos</h2>\r\n', 'pastillas-de-freno-semimetalicas', 3700, 'pastillas-de-freno-semimetalicas_1748422890.jpg', '2025-05-15', 1),
(15, 20, 'DISCOS PERFORADOS', '<h2><strong>Estos agujeros est&aacute;n estrat&eacute;gicamente colocados y no atraviesan todo el disco</strong></h2>\r\n', 'discos-perforados', 4000, 'discos-perforados_1748423821.png', '2025-05-28', 1),
(16, 20, 'PASTILLA DE  FRENO COMPLETAMENTE METALICAS', '<h2>Pastillas&quot; o &quot;frenos de disco&quot;, son componentes clave del sistema de frenos de un veh&iacute;culo</h2>\r\n', 'pastilla-de-freno-completamente-metalicas', 127000, 'pastilla-de-freno-completamente-metalicas_1748423642.jpg', '2022-02-19', 1),
(17, 20, 'DICO DE FRENO RAYADO', '<h2>&nbsp;</h2>\r\n\r\n<div style=\"background:#eeeeee;border:1px solid #cccccc;padding:5px 10px;\"><strong>Presentan surcos en su superficie para mejorar la disipaci&oacute;n del calor y la evacuaci&oacute;n de gases</strong></div>\r\n', 'dico-de-freno-rayado', 1900000, 'dico-de-freno-rayado_1748424475.jpg', '0000-00-00', 0),
(18, 20, 'DISCO DE FRENO GW CON REFRIGERACION 6 TORNILLOS', '<h2>&iexcl;Potencia tu experiencia de ciclismo con el&nbsp;<strong>DISCO REFRIGERADO 6 TORNILLOS 160MM FR-11SAF GW</strong>!</h2>\r\n', 'disco-de-freno-gw-con-refrigeracion-6-tornillos', 200000, 'disco-de-freno-gw-con-refrigeracion-6-tornillos_1748424243.jpg', '2022-02-19', 2),
(19, 20, 'PASTILLAS DE FRENO CERAMICA', '<h2><strong>Tienen excelente potencia de detenci&oacute;n y dispersan bien el calor</strong></h2>\r\n', 'pastillas-de-freno-ceramica', 120000, 'laptop-asus-fx516pc-hn004t-intel-core-i7-16gb-ram-512gb-ssd-15-6_1748422955.jpg', '2022-02-22', 2),
(20, 21, 'Bujia 12V', '<p>Dise&ntilde;adas para los carros nuevos&nbsp;</p>\r\n', 'bujia-12v', 72000, 'bujia-12v_1748422553.jpeg', '2025-05-27', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pay_id` varchar(50) NOT NULL,
  `sales_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(60) NOT NULL,
  `type` int(1) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `contact_info` varchar(100) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `status` int(1) NOT NULL,
  `activate_code` varchar(15) NOT NULL,
  `reset_code` varchar(15) NOT NULL,
  `created_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `type`, `firstname`, `lastname`, `address`, `contact_info`, `photo`, `status`, `activate_code`, `reset_code`, `created_on`) VALUES
(1, 'admin@gmail.com', '$2y$10$VZUxZl4MAEC0vZSH2tH4fOgQ5mNIfergHCpq4FtHTB05RUazH5hiu', 1, 'Tarea', 'Completo', '', '', 'tarea.png', 1, '', '', '2018-05-01'),
(13, 'joel@gmail.com', '$2y$10$M566C0GZvd8pq1UQ/4XzMOx59isKq.3qvVWkadyeJftvlwbtysIMW', 0, 'Joel', 'Tarea', 'Tarea', '876745653', 'tarea.png', 1, '', '', '2022-02-19'),
(14, 'luisj@gmail.com', '$2y$10$6tvNu37hS01yc7QfVwbKuOEs.K6VLb/LPkkeStssf0AMmOXluEB.6', 0, 'Luis', 'Castellanos', '', '', 'Captura de pantalla 2023-05-30 225328.png', 1, '', 'VIt6UE4i9bdPhpc', '2025-05-15');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `details`
--
ALTER TABLE `details`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `details`
--
ALTER TABLE `details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
