-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-01-2026 a las 02:44:59
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
-- Base de datos: `tienda`
--

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
(3, 13, 13, 1),
(5, 20, 13, 1),
(6, 20, 14, 1),
(7, 20, 17, 1),
(10, 22, 13, 3);

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
(2, 'MOTOR', 'MOTOR'),
(3, 'BATERIA', 'BATERIA'),
(16, 'FILTROS', 'FILTROS'),
(17, 'AMORTIGUADOR', 'AMORTIGUADOR'),
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

--
-- Volcado de datos para la tabla `details`
--

INSERT INTO `details` (`id`, `sales_id`, `product_id`, `quantity`) VALUES
(2, 2, 13, 2),
(3, 3, 15, 1),
(4, 4, 14, 2),
(5, 5, 13, 2),
(6, 5, 20, 3),
(7, 5, 14, 2),
(8, 6, 13, 1),
(9, 7, 26, 1),
(10, 8, 23, 1);

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
  `stock` int(11) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `date_view` date NOT NULL,
  `counter` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `slug`, `price`, `stock`, `photo`, `date_view`, `counter`) VALUES
(13, 20, 'Discos De Freno Ford New Ranger 2014 - 2021.', '<h2><strong>Discos De Freno Ford New Ranger 2014 - 2021.</strong></h2>\r\n\r\n<p><strong>Marca:</strong> Brake Usa</p>\r\n\r\n<p><strong>Modelo:</strong> Ford New Rager Modelo 2014.. 2021.</p>\r\n\r\n<p><strong>Posicion:</strong> Delantera.</p>\r\n\r\n<p><strong>Tipo de disco de freno:</strong> Ventilado.</p>\r\n\r\n<p><strong>Di&aacute;metro externo: </strong>302 mm.</p>\r\n\r\n<p><strong>Es ranurado:</strong> No.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong> Carro/Camioneta.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'discos-de-freno-ford-new-ranger-2014-2021', 180000, 10, 'discos-seramico_1748424046.jpg', '2025-11-20', 1),
(14, 20, 'Pastilla De Freno Para Renault', '<h1><strong>Pastilla De Freno Para Renault Logan/megane/twingo/simbol.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Imcolbest.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>10243.</p>\r\n\r\n<p><strong>Material:&nbsp;</strong>Semimet&aacute;lica.</p>\r\n\r\n<p><strong>Posicion:&nbsp;&nbsp;</strong>Delanteras.</p>\r\n\r\n<p><strong>Cantidad de pastillas: 4.</strong></p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp; </strong>Carro/Camioneta.&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'pastilla-de-freno-para-renault', 90000, 0, 'pastillas-de-freno-semimetalicas_1748422890.jpg', '2025-11-12', 1),
(15, 20, 'DISCOS PERFORADOS', '<h2>Discos Freno Mercedes Benz Cla200 Cla180 C117 X117 Perforado.</h2>\r\n\r\n<p><strong>Marca: </strong>FR</p>\r\n\r\n<p><strong>Modelo:</strong> Mercedes Benz Cla</p>\r\n\r\n<p><strong>Posicion: </strong>Delantera.</p>\r\n\r\n<p><strong>Tipo de Veh&iacute;culo:</strong> Carro/Camioneta.</p>\r\n\r\n<p><strong>Es perforado:</strong> Si.</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'discos-perforados', 400000, 3, 'discos-perforados_1748423821.png', '2025-11-07', 1),
(16, 20, 'Pastillas Delanteras Toyota Fortuner,tx,fj,hilux ', '<h2><strong>Pastillas Delanteras Toyota Fortuner,tx,fj,hilux 3.0,2.4.2.8</strong></h2>\r\n\r\n<p><strong>Marca:</strong> Toyota.</p>\r\n\r\n<p><strong>Modelo:</strong> Pastillas Toyota.</p>\r\n\r\n<p><strong>N&uacute;mero de Pieza:</strong> 04465-60366.</p>\r\n\r\n<p><strong>Posici&oacute;n:</strong> Delantero.</p>\r\n\r\n<p><strong>Cantidad de pastilla:</strong> 4</p>\r\n\r\n<p><strong>Origen:</strong> Original.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong> Carro/Camioneta.</p>\r\n\r\n<p><strong>Codigo OEM:&nbsp;</strong>04465-60366.</p>\r\n', 'pastillas-delanteras-toyota-fortuner-tx-fj-hilux', 127000, 3, 'pastilla-de-freno-completamente-metalicas_1748423642.jpg', '2022-02-19', 1),
(17, 20, 'Discos Freno Hiperventilados Renault Sandero Logan', '<h1><strong>Discos Freno Hiperventilados Renault Sandero Logan 2016-2023.</strong></h1>\r\n\r\n<p><strong>Marca:</strong> Bex Usa.</p>\r\n\r\n<p><strong>Modelo:</strong> LOGAN MODELO NUEVO.</p>\r\n\r\n<p><strong>Numero de pieza:</strong> BXDI-3040.</p>\r\n\r\n<p><strong>Posici&oacute;n:</strong> Delantera.</p>\r\n\r\n<p><strong>Tipo de disco de freno:</strong> Ventilado.</p>\r\n\r\n<p><strong>Es ranurado:</strong> si</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong> Carro/Camioneta.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'discos-freno-hiperventilados-renault-sandero-logan', 1900000, 4, 'dico-de-freno-rayado_1748424475.jpg', '2025-11-01', 2),
(18, 20, 'DISCO DE FRENO GW ', '<h2><strong>Rotor Disco De Freno Gw Refrigerado 203 Mm Mtb Tornillos</strong><br />\r\n<br />\r\nRotor con sistema de refrigeracion<br />\r\nMedida: 203 MM<br />\r\nMotodo de fijacion: 6 tornilos<br />\r\nPeso: 196 Gr</h2>\r\n', 'disco-de-freno-gw', 200000, 5, 'disco-de-freno-gw-con-refrigeracion-6-tornillos_1748424243.jpg', '2025-11-12', 1),
(19, 20, 'PASTILLAS DE FRENO CERAMICA', '<h1><strong>Pastillas De Freno Brakepak Mazda Cx-5.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Mazda.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>CX5 2,0 2,5 2015,,, (TRAS).</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>&nbsp;Na.</p>\r\n\r\n<p><strong>Material:&nbsp;&nbsp;</strong>Cer&aacute;mica.</p>\r\n\r\n<p><strong>Posici&oacute;n:&nbsp;</strong>&nbsp;Trasera.</p>\r\n\r\n<p><strong>Cantidad de pastillas:&nbsp;</strong>4.</p>\r\n\r\n<p><strong>Incluye sensores de desgaste:&nbsp;</strong>&nbsp;No.</p>\r\n\r\n<p><strong>Origen:&nbsp;&nbsp;</strong>China.</p>\r\n\r\n<p><strong>Codigo FMSI:&nbsp;</strong>9073-1846.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n', 'pastillas-de-freno-ceramica', 120000, 5, 'laptop-asus-fx516pc-hn004t-intel-core-i7-16gb-ram-512gb-ssd-15-6_1748422955.jpg', '2022-02-22', 2),
(20, 21, 'Bujia Encendido Motor Chevrolet Tracker (4 Und) Acdelco', '<h1><strong>Bujia Encendido Motor Chevrolet Tracker (4 Und) Acdelco.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>ACDelco.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>19336070X4Tracker.</p>\r\n\r\n<p><strong>Cantidad de buj&iacute;as: 1.</strong></p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:&nbsp;</strong>19336070X4Tracker.</p>\r\n\r\n<p><strong>Es kit:</strong> S&iacute;.</p>\r\n', 'bujia-encendido-motor-chevrolet-tracker-4-und-acdelco', 72000, 1, 'bujia-12v_1748422553.jpeg', '2025-11-06', 1),
(22, 20, 'Pastillas De Freno Dynamik  Chevrolet Onix Turbo', '<h1><strong>Pastillas De Freno Dynamik 26275866&nbsp;Chevrolet Onix Turbo.</strong></h1>\r\n\r\n<p><strong>Marca:</strong> Dynamik.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong> 26275860.</p>\r\n\r\n<p><strong>Material:</strong> Semimet&aacute;lica.</p>\r\n\r\n<p><strong>Cantidad de pastillas: </strong>4.</p>\r\n\r\n<p><strong>Origen: </strong>M&eacute;xico.</p>\r\n\r\n<p><strong>C&oacute;digo FMSI:</strong>&nbsp;26275866.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong> Carro/Camioneta.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:</strong>&nbsp;&nbsp;26275866.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'pastillas-de-freno-dynamik-chevrolet-onix-turbo', 179000, 2, '', '0000-00-00', 0),
(23, 3, 'Batería Willard 34d 1100 Amperios 12v Derecho', '<h1><strong>Bater&iacute;a Willard 34d 1100 Amperios 12v Derecho.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>&nbsp;Willard</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>&nbsp;34D-1100.</p>\r\n\r\n<p><strong>Ladop del polo positivo:&nbsp;</strong>Derecho.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>Voltaje:&nbsp;</strong>12V.</p>\r\n\r\n<p><strong>Capacodad de la bater&iacute;a:&nbsp;&nbsp;</strong>1.100 Ah.</p>\r\n\r\n<p><strong>CCA:&nbsp;</strong>&nbsp;645 A.</p>\r\n\r\n<p><strong>Ancho x Largo x Altura:&nbsp;</strong>17 cm 25.9 cm x 20.3 cm.</p>\r\n\r\n<p><strong>Peso:&nbsp;</strong>15kg.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'bateri-willard-34d-1100-amperios-12v-derecho', 742500, 9, 'bateri-willard-34d-1100-amperios-12v-derecho_1763114900.webp', '2025-11-14', 1),
(24, 3, 'Batería Bosch 65 900 Amperios 12v Derecho', '<h1><strong>Bater&iacute;a Bosch 65 900 Amperios 12v Derecho.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Bosch.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>65 HP 900D.</p>\r\n\r\n<p><strong>Lado del polo positivo:&nbsp;</strong>Derecho.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Voltaje:&nbsp;</strong>12V.</p>\r\n\r\n<p><strong>Capacidad de la bater&iacute;a:&nbsp;</strong>900 Ah.</p>\r\n\r\n<p><strong>CCA:&nbsp;</strong>550 A.</p>\r\n\r\n<p><strong>Ancho x Largo x Altura:&nbsp;</strong>17.3 cm x 23 cm x 22.5 cm.</p>\r\n\r\n<p><strong>Peso:&nbsp;</strong>14 kg.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'bateri-bosch-65-900-amperios-12v-derecho', 500000, 20, 'bateri-bosch-65-900-amperios-12v-derecho_1763114848.webp', '0000-00-00', 0),
(25, 3, 'Batería Varta 42-870 Para Renault Sandero, Scenic, Symbol,r9', '<h1><strong>Bater&iacute;a Varta 42-870 Para Renault Sandero, Scenic, Symbol,r9.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Varta.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong>&nbsp;BATERIA PARA CARRO.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>42ISTV4870.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Material:&nbsp;</strong>Electrolito l&iacute;quido.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>Nacional.</p>\r\n\r\n<p><strong>Voltaje:&nbsp;</strong>12V.</p>\r\n\r\n<p><strong>Capacidad de la bater&iacute;a:&nbsp;</strong>730 Ah.</p>\r\n\r\n<p><strong>Ancho x Largo x Altura:&nbsp;</strong>17.5 cm x 24.2 cm x 17.5 cm.</p>\r\n\r\n<p><strong>Volumen de electrolito:&nbsp;</strong>6,5 L.</p>\r\n\r\n<p><strong>Es inflamable:&nbsp;</strong>S&iacute;</p>\r\n\r\n<p>.&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>&nbsp;</strong></p>\r\n', 'bateri-varta-42-870-para-renault-sandero-scenic-symbol-r9', 400000, 30, 'bateri-varta-42-870-para-renault-sandero-scenic-symbol-r9_1763114873.webp', '0000-00-00', 0),
(26, 3, 'Bateria Optima Yellowtop D34/78-750', '<h1><strong>Bateria Optima Yellowtop D34/78-750</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Optima.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza: </strong>1.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Ancho x Largo x Altura:&nbsp;</strong>17.5 cm x 25.4 cm x 19.9 cm</p>\r\n', 'bateria-optima-yellowtop-d34-78-750', 1000000, 2, 'bateria-optima-yellowtop-d34-78-750_1763114824.webp', '2025-11-14', 1),
(27, 17, 'Amortiguador Trasero Chev Spark Lt / Cronos / Life - Juego', '<h1><strong>Amortiguador Trasero Chev Spark Lt / Cronos / Life - Juego.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>WRTEX.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>964000ZT.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Posici&oacute;n:&nbsp;</strong>Trasero.</p>\r\n\r\n<p><strong>Lado:&nbsp;</strong>Izquierdo/Derecho.</p>\r\n\r\n<p><strong>Material:&nbsp;</strong>Metal.</p>\r\n\r\n<p><strong>Tipo de composici&oacute;n:&nbsp;</strong>Hidr&aacute;ulico.</p>\r\n', 'amortiguador-trasero-chev-spark-lt-cronos-life-juego', 150000, 20, 'amortiguador-trasero-chev-spark-lt-cronos-life-juego_1763114711.webp', '2025-11-14', 1),
(28, 17, 'Base De Amortiguador Trasero Spark Gt L4 1.2 Kit', '<h1><strong>Base De Amortiguador Trasero Spark Gt L4 1.2 Kit.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Eagle BHP.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>55995599.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Posici&oacute;n:&nbsp;</strong>Trasero.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:&nbsp;</strong>5599.</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'base-de-amortiguador-trasero-spark-gt-l4-1-2-kit', 75000, 40, 'base-de-amortiguador-trasero-spark-gt-l4-1-2-kit_1763114791.webp', '0000-00-00', 0),
(29, 17, 'Kit 4 Amortiguadores Para Chevrolet Aveo Todos / Gabriel', '<h1><strong>Kit 4 Amortiguadores Para Chevrolet Aveo Todos / Gabriel.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Gabriel.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>G706062/63-69459.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Posici&oacute;n:&nbsp;</strong>Delantero/Trasero.</p>\r\n\r\n<p><strong>Lado:&nbsp;</strong>Izquierdo/Derecho.</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'kit-4-amortiguadores-para-chevrolet-aveo-todos-gabriel', 500000, 40, 'kit-4-amortiguadores-para-chevrolet-aveo-todos-gabriel_1763115192.webp', '0000-00-00', 0),
(30, 17, 'Kit Soporte Amortiguador Logan Duster Sandero Con Arandela', '<h1><strong>Kit Soporte Amortiguador Logan Duster Sandero Con Arandela.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>FR.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>FR.</p>\r\n\r\n<p><strong>Formato de venta:&nbsp;</strong>Pack.</p>\r\n\r\n<p><strong>Unidades por pack: </strong>2.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Tipo de soporte de amortiguador:&nbsp;</strong>Est&aacute;ndar.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>Nacional.</p>\r\n\r\n<p><strong>Material de la cazoleta:&nbsp;</strong>Hule/Metal.</p>\r\n\r\n<p><strong>Incluye tornillos:&nbsp;</strong>No.</p>\r\n\r\n<p><strong>Incluye arandelas:&nbsp;</strong>S&iacute;.</p>\r\n\r\n<p><strong>Incluye abrazaderas:&nbsp;</strong>No.</p>\r\n\r\n<p><strong>Incluye aislante:</strong> No.</p>\r\n\r\n<p><strong>Incluye kit de montaje:&nbsp;</strong>No.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'kit-soporte-amortiguador-logan-duster-sandero-con-arandela', 100000, 20, 'kit-soporte-amortiguador-logan-duster-sandero-con-arandela_1763115218.webp', '0000-00-00', 0),
(31, 17, 'Soporte Amortiguador Delantero Para Duster Logan Sandero', '<h1><strong>Soporte Amortiguador Delantero Para Duster Logan Sandero.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Star Parts.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>6001547499.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Posici&oacute;n:&nbsp;</strong>Delantero.</p>\r\n\r\n<p><strong>Lado:&nbsp;</strong>Izquierdo/Derecho.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>Importado.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:&nbsp;</strong>6001547499.</p>\r\n', 'soporte-amortiguador-delantero-para-duster-logan-sandero', 100000, 20, 'soporte-amortiguador-delantero-para-duster-logan-sandero_1763115269.webp', '0000-00-00', 0),
(32, 16, 'Filtro De Cabina Mazda 3 - 6 Skyactive - Cx5', '<h1><strong>Filtro De Cabina Mazda 3 - 6 Skyactive - Cx5.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>HB.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>HB1612S.</p>\r\n\r\n<p><strong>Ancho x Altura:&nbsp;</strong>19.5 cm x 1.7 cm.</p>\r\n\r\n<p><strong>Forma del filtro:&nbsp;</strong>Rectangular.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'filtro-de-cabina-mazda-3-6-skyactive-cx5', 20000, 50, 'filtro-de-cabina-mazda-3-6-skyactive-cx5_1763115113.webp', '0000-00-00', 0),
(33, 16, 'Filtro De Aire Original Acdelco 19279674', '<h1><strong>Filtro De Aire Original Acdelco 19279674.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>ACDelco.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>ACDELCO.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>19279674.</p>\r\n\r\n<p><strong>Cantidad de packs:&nbsp;</strong>1.</p>\r\n\r\n<p><strong>Forma del filtro:&nbsp;</strong>Trapezoidal.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Material:&nbsp;</strong>Pl&aacute;stico.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:&nbsp;</strong>19279674.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>Brasil.</p>\r\n', 'filtro-de-aire-original-acdelco-19279674', 30000, 20, 'filtro-de-aire-original-acdelco-19279674_1763115082.webp', '0000-00-00', 0),
(34, 16, 'Filtro De Aceite Acdelco Para Chevrolet Tracker 13/17', '<h1><strong>Filtro De Aceite Acdelco Para Chevrolet Tracker 13/17.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>ACDelco.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>Filtro de aceite.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>19279631.</p>\r\n\r\n<p><strong>Cantidad de packs:&nbsp;</strong>1.</p>\r\n\r\n<p><strong>Formato de venta:&nbsp;</strong>Cartucho.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Incluye aceite:&nbsp;</strong>No.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:&nbsp;</strong>19279631.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>No aplica.</p>\r\n', 'filtro-de-aceite-acdelco-para-chevrolet-tracker-13-17', 20000, 30, 'filtro-de-aceite-acdelco-para-chevrolet-tracker-13-17_1763115024.webp', '0000-00-00', 0),
(35, 16, 'Filtro De Aceite Acdelco Para Chevrolet Sonic 13/18', '<h1><strong>Filtro De Aceite Acdelco Para Chevrolet Sonic 13/18.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>ACDelco.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>Filtro de aceite.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>19279631.</p>\r\n\r\n<p><strong>Cantidad de packs:&nbsp;</strong>1.</p>\r\n\r\n<p><strong>Formato de venta:&nbsp;</strong>Unidad.</p>\r\n\r\n<p><strong>Tipo de filtro de aceite:</strong>&nbsp;Cartucho.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Incluye aceite:&nbsp;</strong>No.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:&nbsp;</strong>19279631.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>No aplica.</p>\r\n', 'filtro-de-aceite-acdelco-para-chevrolet-sonic-13-18', 25000, 20, 'filtro-de-aceite-acdelco-para-chevrolet-sonic-13-18_1763114972.webp', '0000-00-00', 0),
(36, 16, 'Filtro De Aire Acdelco Para Chevrolet Sonic 13/18', '<h1><strong>Filtro De Aire Acdelco Para Chevrolet Sonic 13/18.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>ACDelco.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>19315464.</p>\r\n\r\n<p><strong>Cantidad de packs:&nbsp;</strong>1.</p>\r\n\r\n<p><strong>Forma del filtro:&nbsp;</strong>Rectangular.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Material:&nbsp;</strong>Pl&aacute;stico.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:&nbsp;</strong>19315464.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>No aplica.</p>\r\n', 'filtro-de-aire-acdelco-para-chevrolet-sonic-13-18', 35000, 20, 'filtro-de-aire-acdelco-para-chevrolet-sonic-13-18_1763115057.webp', '0000-00-00', 0),
(37, 16, 'Filtro De Combustible Toyota Hilux 2.4-2.8 Diesel 2017', '<h1><strong>Filtro De Combustible Toyota Hilux 2.4-2.8 Diesel 2017.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Original.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>Diesel.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>23390-0L070.</p>\r\n\r\n<p><strong>Cantidad de packs:</strong> 1.</p>\r\n\r\n<p><strong>Formato de venta:&nbsp;</strong>&nbsp;Unidad.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'filtro-de-combustible-toyota-hilux-2-4-2-8-diesel-2017', 90000, 20, 'filtro-de-combustible-toyota-hilux-2-4-2-8-diesel-2017_1763115161.webp', '0000-00-00', 0),
(38, 16, 'Filtro Combustible Volkswagen Amarok', '<h1><strong>Filtro Combustible Volkswagen Amarok.</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Maxhanest.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong>&nbsp;VC200004.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo</strong>:&nbsp;Carro/Camioneta</p>\r\n', 'filtro-combustible-volkswagen-amarok', 80000, 15, 'filtro-combustible-volkswagen-amarok_1763115326.webp', '0000-00-00', 0),
(39, 19, 'Kit de 2 llantas Powertrac Citytour P 185/65R15 88 H', '<h1><strong>Kit de 2 llantas Powertrac Citytour P 185/65R15 88 H.</strong></h1>\r\n\r\n<p><strong>Cantidad de llantas:</strong> 2.</p>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Powertrac.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>Citytour.</p>\r\n\r\n<p><strong>Tama&ntilde;o:&nbsp;</strong>185/65R15.</p>\r\n\r\n<p><strong>&Iacute;ndice de carga:</strong>&nbsp;88</p>\r\n\r\n<p><strong>&Iacute;ndice de velocidad:&nbsp;</strong>&nbsp;H</p>\r\n\r\n<p><strong>&Iacute;ndice de tracci&oacute;n:</strong>&nbsp;A</p>\r\n\r\n<p><strong>Carga m&aacute;xima:</strong>&nbsp;560 kg.</p>\r\n\r\n<p><strong>Velocidad m&aacute;xima:&nbsp;</strong>210 km/h.</p>\r\n\r\n<p><strong>Tipo de construcci&oacute;n:&nbsp;</strong>Radial.</p>\r\n\r\n<p><strong>Perfil de la llanta:</strong> 65 %</p>\r\n\r\n<p><strong>Tipo de servicio:</strong>&nbsp;P.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Tipo de terreno:</strong>&nbsp;HT.</p>\r\n\r\n<p><strong>Es run flat:</strong>&nbsp;No.</p>\r\n\r\n<p><strong>Dise&ntilde;o de la banda de rodadura:</strong> Regular.</p>\r\n\r\n<p><strong>Di&aacute;metro de la llanta:</strong>&nbsp;15 &quot;.</p>\r\n\r\n<p><strong>Di&aacute;metro externo:</strong>&nbsp;621 mm</p>\r\n', 'kit-de-2-llantas-powertrac-citytour-p-185-65r15-88-h', 400000, 10, 'kit-de-2-llantas-powertrac-citytour-p-185-65r15-88-h.webp', '0000-00-00', 0),
(40, 19, 'Kit de 2 llantas Compasal Blazer HP P 205/55R16 91 V', '<h1><strong>Kit de 2 llantas Compasal Blazer HP P 205/55R16 91 V.</strong></h1>\r\n\r\n<p><strong>Cantidad de llantas</strong>: 2.</p>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Compasal.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>Blazer HP.</p>\r\n\r\n<p><strong>Tama&ntilde;o:&nbsp;</strong>205/55R16.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>Tipo de terreno:</strong>&nbsp;HT.</p>\r\n\r\n<p><strong>Es run flat:</strong>&nbsp;No.</p>\r\n\r\n<p><strong>Perfil:</strong> 55.</p>\r\n\r\n<p><strong>Ancho de secci&oacute;n:</strong>&nbsp;205 mm.</p>\r\n\r\n<p><strong>Di&aacute;metro de la llanta:</strong>&nbsp;16 &quot;.</p>\r\n\r\n<p><strong>Di&aacute;metro externo:</strong>&nbsp;632 mm</p>\r\n\r\n<p><strong>Carga m&aacute;xima:</strong>&nbsp;615 kg.</p>\r\n\r\n<p><strong>Velocidad m&aacute;xima:</strong>&nbsp;240 km/h.</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'kit-de-2-llantas-compasal-blazer-hp-p-205-55r16-91-v', 400000, 15, 'kit-de-2-llantas-compasal-blazer-hp-p-205-55r16-91-v.webp', '0000-00-00', 0),
(41, 19, 'Llanta 165/60 R14 Hf201 Hifly', '<h1><strong>Llanta 165/60 R14 Hf201 Hifly.</strong></h1>\r\n\r\n<p><strong>Cantidad de llantas:</strong> 1.</p>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Hifly.</p>\r\n\r\n<p><strong>L&iacute;nea:</strong>&nbsp;HF201.</p>\r\n\r\n<p><strong>Modelo: </strong>HF201.</p>\r\n\r\n<p><strong>Tama&ntilde;o</strong>:&nbsp;165/60R14.</p>\r\n\r\n<p><strong>&Iacute;ndice de carga:</strong> H.</p>\r\n\r\n<p><strong>Perfil:</strong> 60.</p>\r\n\r\n<p><strong>Ancho de secci&oacute;n:&nbsp;</strong>165 mm.</p>\r\n\r\n<p><strong>Di&aacute;metro de la llanta:</strong>&nbsp;14 &quot;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'llanta-165-60-r14-hf201-hifly', 100000, 15, 'llanta-165-60-r14-hf201-hifly.webp', '0000-00-00', 0),
(42, 19, 'Llanta 205/55 R16 Genesys 228 Boto', '<h1><strong>Llanta 205/55 R16 Genesys 228 Boto</strong></h1>\r\n\r\n<p><strong>Lateral:</strong> BSW.</p>\r\n\r\n<p><strong>Cantidad de llantas:</strong> 1.</p>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Boto.</p>\r\n\r\n<p><strong>L&iacute;nea:&nbsp;</strong>Genesys.</p>\r\n\r\n<p><strong>Modelo:</strong>&nbsp;Genesys 228.</p>\r\n\r\n<p><strong>Tama&ntilde;o:</strong>&nbsp;205/55R16.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Perfil: </strong>55.</p>\r\n\r\n<p><strong>Ancho de secci&oacute;n:</strong>&nbsp;205 mm.</p>\r\n\r\n<p><strong>Di&aacute;metro de la llanta:</strong> 16&quot;.</p>\r\n\r\n<p><strong>Rango de carga: </strong>91.</p>\r\n\r\n<p><strong>Carga m&aacute;xima:</strong> 615 kg.</p>\r\n\r\n<p><strong>Velocidad m&aacute;xima:</strong>&nbsp;270 km/h.</p>\r\n', 'llanta-205-55-r16-genesys-228-boto', 200000, 15, 'llanta-205-55-r16-genesys-228-boto.webp', '0000-00-00', 0),
(43, 19, 'Llanta 195/50 R15 Vantage H-8 Boto', '<h1><strong>Llanta 195/50 R15 Vantage H-8 Boto.</strong></h1>\r\n\r\n<p><strong>Cantidad de llantas:</strong> 1</p>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Boto.</p>\r\n\r\n<p><strong>L&iacute;nea:</strong>&nbsp;VANTAGE H-8.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>VANTAGE H-8.</p>\r\n\r\n<p><strong>Tama&ntilde;o:</strong>&nbsp;195/50R15.</p>\r\n\r\n<p><strong>Rango de carga:</strong> 82.</p>\r\n\r\n<p><strong>Perfil:</strong> 5O.</p>\r\n\r\n<p><strong>Ancho de secci&oacute;n:</strong>&nbsp;195 mm.</p>\r\n\r\n<p><strong>Di&aacute;metro de la llanta:&nbsp;</strong>15 &quot;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'llanta-195-50-r15-vantage-h-8-boto', 200000, 15, 'llanta-195-50-r15-vantage-h-8-boto.webp', '0000-00-00', 0),
(44, 19, 'Llanta Hifly Vigorous AT601 LT 255/70R16 111 T', '<h1><strong>Llanta Hifly Vigorous AT601 LT 255/70R16 111 T.</strong></h1>\r\n\r\n<p><strong>Cantidad de llantas:</strong> 1.</p>\r\n\r\n<p><strong>Marca:&nbsp;</strong>Hifly.</p>\r\n\r\n<p><strong>L&iacute;nea:&nbsp;</strong>Vigorous.</p>\r\n\r\n<p><strong>Modelo:</strong>&nbsp;AT601.</p>\r\n\r\n<p><strong>Tama&ntilde;o:&nbsp;</strong>255/70R16.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>Perfil:</strong> 70.</p>\r\n\r\n<p><strong>Ancho de secci&oacute;n:</strong> 255 mm</p>\r\n\r\n<p><strong>Di&aacute;metro de la llanta:&nbsp;</strong>16 &quot;.</p>\r\n\r\n<p><strong>Rango de carga:</strong> 111.</p>\r\n\r\n<p><strong>Carga m&aacute;xima:</strong>&nbsp;1.090 kg.</p>\r\n\r\n<p><strong>Velocidad m&aacute;xima:&nbsp;</strong>190 km/h.</p>\r\n', 'llanta-hifly-vigorous-at601-lt-255-70r16-111-t', 400000, 15, 'llanta-hifly-vigorous-at601-lt-255-70r16-111-t.webp', '0000-00-00', 0),
(45, 19, 'Llanta Michelin Energy XM2+ P 185/65R15 88 H', '<h1><strong>Llanta Michelin Energy XM2+ P 185/65R15 88 H.</strong></h1>\r\n\r\n<p><strong>Cantidad de llantas:</strong> 1</p>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Michelin</p>\r\n\r\n<p><strong>L&iacute;nea:</strong>&nbsp;Energy.</p>\r\n\r\n<p><strong>Modelo:</strong>&nbsp;XM2+.</p>\r\n\r\n<p><strong>Tama&ntilde;o:</strong>&nbsp;185/65R15.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>Perfil:</strong> 65.</p>\r\n\r\n<p><strong>Ancho de secci&oacute;n:</strong>&nbsp;185 mm.</p>\r\n\r\n<p><strong>Di&aacute;metro de la llanta:</strong>&nbsp;15 &quot;.</p>\r\n\r\n<p><strong>Di&aacute;metro externo:</strong>&nbsp;621 mm.</p>\r\n\r\n<p><strong>Rango de carga:</strong> 88</p>\r\n\r\n<p><strong>Carga m&aacute;xima:</strong> 560 kg.</p>\r\n\r\n<p><strong>Velocidad m&aacute;xima:</strong>&nbsp;210 km/h.</p>\r\n', 'llanta-michelin-energy-xm2-p-185-65r15-88-h', 420000, 15, 'llanta-michelin-energy-xm2-p-185-65r15-88-h.webp', '0000-00-00', 0),
(46, 21, 'Servicio mano de obra', '<h1><strong>Mano de obra por servicio.</strong></h1>\r\n', 'servicio-mano-de-obra', 200000, 1, '', '0000-00-00', 0),
(47, 2, 'Motor De Arranque Suzuki Gn-125/ Gs-125 ( Marca Gx )', '<h1><strong>Motor De Arranque Suzuki Gn-125/ Gs-125 ( Marca Gx )</strong></h1>\r\n\r\n<p>Marca: GX.</p>\r\n\r\n<p>N&uacute;mero de pieza: 1</p>\r\n\r\n<p>Tipo de veh&iacute;culo: Carro/Camioneta.</p>\r\n\r\n<p>Origen:&nbsp;Importado.</p>\r\n', 'motor-de-arranque-suzuki-gn-125-gs-125-marca-gx', 80000, 10, 'motor-de-arranque-suzuki-gn-125-gs-125-marca-gx.webp', '0000-00-00', 0),
(48, 2, 'Motor Arranque Renault Clio Ii 1.4l 2001 2002 2003 2004 2005', '<h1><strong>Motor Arranque Renault Clio Ii 1.4l 2001 2002 2003 2004 2005</strong></h1>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Kanuni.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong>&nbsp;2627.0.</p>\r\n\r\n<p><strong>Dientes de bendix</strong>: 1</p>\r\n\r\n<p><strong>Sentido de rotaci&oacute;n:</strong>&nbsp;Horario.</p>\r\n\r\n<p><strong>Voltaje</strong>:&nbsp;1224V.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:</strong>&nbsp;Original.</p>\r\n\r\n<p><strong>Origen:</strong>&nbsp;Original.</p>\r\n', 'motor-arranque-renault-clio-ii-1-4l-2001-2002-2003-2004-2005', 230000, 5, 'motor-arranque-renault-clio-ii-1-4l-2001-2002-2003-2004-2005.webp', '0000-00-00', 0),
(49, 2, 'Motor Arranque Renault Twingo Clio I 12v 0.8kw 9 Dientes', '<h1><strong>Motor Arranque Renault Twingo Clio I 12v 0.8kw 9 Dientes.</strong></h1>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Kanuni.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong>&nbsp;5039.</p>\r\n\r\n<p><strong>Dientes de bendix:</strong> 9.</p>\r\n\r\n<p><strong>Sentido de rotaci&oacute;n:</strong>&nbsp;Horario.</p>\r\n\r\n<p><strong>Voltaje:</strong>&nbsp;12V.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:</strong>&nbsp;Original.</p>\r\n\r\n<p><strong>Origen:</strong>&nbsp;Original.</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'motor-arranque-renault-twingo-clio-i-12v-0-8kw-9-dientes', 230000, 15, 'motor-arranque-renault-twingo-clio-i-12v-0-8kw-9-dientes.webp', '0000-00-00', 0),
(50, 2, 'Motor Arranque Chevrolet Tracker 12v 1.4kw 8 Dientes', '<h1><strong>Motor Arranque Chevrolet Tracker 12v 1.4kw 8 Dientes.</strong></h1>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Kanuni.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong>&nbsp;5014.</p>\r\n\r\n<p><strong>Dientes de bendix:</strong> 8</p>\r\n\r\n<p><strong>Sentido de rotaci&oacute;n:&nbsp;</strong>Horario.</p>\r\n\r\n<p><strong>Voltaje:&nbsp;</strong>12V.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:</strong>&nbsp;Original.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>Original</p>\r\n', 'motor-arranque-chevrolet-tracker-12v-1-4kw-8-dientes', 230000, 15, 'motor-arranque-chevrolet-tracker-12v-1-4kw-8-dientes.webp', '0000-00-00', 0),
(51, 2, 'Bloque Motor 2kd Diesel Nuevo Original Virgen', '<h1><strong>Bloque Motor 2kd Diesel Nuevo Original.</strong></h1>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Toyota.</p>\r\n\r\n<p><strong>Modelo:&nbsp;</strong>HILUX - FORTUNNER - 4rUNNER - BUS COSTER.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong>&nbsp;11401-09370.</p>\r\n\r\n<p><strong>A&ntilde;o del motor:</strong>&nbsp;2015.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>Tipo de combustible:</strong> Di&eacute;sel.</p>\r\n\r\n<p><strong>Cantidad de cilindros:</strong>&nbsp;4</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>TOYOTA JAPON</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'bloque-motor-2kd-diesel-nuevo-original-virgen', 400000, 2, 'bloque-motor-2kd-diesel-nuevo-original-virgen.webp', '0000-00-00', 0),
(52, 2, 'Módulo Abs Ford Ecosport 2.0 2013 A 2015 Progrado', '<h1><strong>M&oacute;dulo Abs Ford Ecosport 2.0 2013 A 2015 Progrado.</strong></h1>\r\n\r\n<p><strong>Marca</strong>:&nbsp;Original.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong>&nbsp;CN15-12A650.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>G&eacute;nero del conector:</strong>&nbsp;Macho.</p>\r\n\r\n<p><strong>Largo del sensor ABS:</strong>&nbsp;2 cm.</p>\r\n\r\n<p><strong>Origen:</strong>&nbsp;ORIGINAL PLANTA &quot;FOMOCO&quot;.</p>\r\n\r\n<p><strong>Posici&oacute;n del sensor ABS:</strong>&nbsp;Delantero</p>\r\n', 'modulo-abs-ford-ecosport-2-0-2013-2015-progrado', 6000000, 1, 'modulo-abs-ford-ecosport-2-0-2013-2015-progrado.webp', '0000-00-00', 0),
(53, 2, 'Módulo De Frenos Abs Ford Ecosport 2018 A 2021', '<h1><strong>M&oacute;dulo De Frenos Abs Ford Ecosport 2018 A 2021</strong></h1>\r\n\r\n<p><strong>Marca:</strong>&nbsp;FOMOCO (FORD MOTOR COMPANY).</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong> NA.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>Origen: </strong>FORD ESTADOS UNIDOS.</p>\r\n', 'modulo-de-frenos-abs-ford-ecosport-2018-2021', 6000000, 2, 'modulo-de-frenos-abs-ford-ecosport-2018-2021.webp', '0000-00-00', 0),
(54, 2, 'Motor Actuador Derecho Evaporador Ford Fiesta 2011 A 2019 - Negro', '<h1>Motor Actuador Derecho Evaporador Ford Fiesta 2011 A 2019 - Negro.</h1>\r\n\r\n<p><strong>Marca:</strong>&nbsp;Motorcraft.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:</strong>&nbsp;BE8Z-19E616-A.</p>\r\n\r\n<p><strong>Origen:&nbsp;</strong>Estados Unidos.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>C&oacute;digo OEM:</strong> BE8Z-19E616-A</p>\r\n', 'motor-actuador-derecho-evaporador-ford-fiesta-2011-2019-negro', 170000, 5, 'motor-actuador-derecho-evaporador-ford-fiesta-2011-2019-negro.webp', '0000-00-00', 0),
(55, 2, 'Termostato De Motor Ford Escape 2001 A 2012 Motor 3.0', '<h1><strong>Termostato De Motor Ford Escape 2001 A 2012 Motor 3.0</strong></h1>\r\n\r\n<p><strong>Marca</strong>:&nbsp;Motorcraft.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza: </strong>NA.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:</strong>&nbsp;Carro/Camioneta.</p>\r\n\r\n<p><strong>Origen:</strong>&nbsp;FORD ESTADOS UNIDOS</p>\r\n', 'termostato-de-motor-ford-escape-2001-2012-motor-3-0', 274000, 10, '', '0000-00-00', 0),
(56, 2, 'Termostato Chevrolet Cruze/sonic/tracker', '<h1><strong>Termostato Chevrolet Cruze/sonic/tracker</strong></h1>\r\n\r\n<p><strong>Marca:&nbsp;</strong>GM.</p>\r\n\r\n<p><strong>N&uacute;mero de pieza:&nbsp;</strong>25193683.</p>\r\n\r\n<p><strong>Tipo de veh&iacute;culo:&nbsp;</strong>Carro/Camioneta.</p>\r\n\r\n<p><strong>Origen</strong>:GM</p>\r\n', 'termostato-chevrolet-cruze-sonic-tracker', 170000, 10, 'termostato-chevrolet-cruze-sonic-tracker.webp', '0000-00-00', 0),
(57, 2, 'Termostato Chevrolet Optra Advance', '<h1><strong>Termostato Chevrolet Optra Advance.</strong></h1>\r\n\r\n<p>Marca:&nbsp;Thomson.</p>\r\n\r\n<p>N&uacute;mero de pieza:&nbsp;709907T.</p>\r\n\r\n<p>Tipo de veh&iacute;culo:&nbsp;Carro/Camioneta.</p>\r\n\r\n<p>Temperatura de apertura:&nbsp;92 &deg;C.</p>\r\n\r\n<p>Origen: Brasil</p>\r\n', 'termostato-chevrolet-optra-advance', 100000, 10, 'termostato-chevrolet-optra-advance.webp', '0000-00-00', 0);

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

--
-- Volcado de datos para la tabla `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `pay_id`, `sales_date`) VALUES
(2, 14, '1', '2025-11-07'),
(3, 14, '2', '2025-11-07'),
(4, 25, '3', '2025-11-11'),
(5, 24, '4', '2025-11-12'),
(6, 24, '5', '2025-11-12'),
(7, 24, '6', '2025-11-14'),
(8, 24, '7', '2025-11-14');

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
(1, 'admin@gmail.com', '$2y$10$VZUxZl4MAEC0vZSH2tH4fOgQ5mNIfergHCpq4FtHTB05RUazH5hiu', 1, 'Almacen', 'almendros', '', '', 'tarea.png', 1, '', '', '2025-07-06'),
(14, 'luisj@gmail.com', '$2y$10$ls.1zjp7o.6kcxRDXK.mmOauaSZUIiQagJPK4eAQ9J408q.pBk12W', 0, 'Luis', 'Castellanos', '', '', 'Captura de pantalla 2023-05-30 225328.png', 1, '', 'VIt6UE4i9bdPhpc', '2025-05-15'),
(21, 'alex@gmail.com', '$2y$10$9cSrxdt9UGUBhHJMOnnIvev/xD8mYvCpo5y4HMqUDTM82h/8ZnC/a', 0, 'Alex', 'rodriguez', '', '', '', 1, '7MlAFxctQD8N', '', '2025-11-01'),
(22, 'juancho@gmail.com', '$2y$10$k4jmx.uXXv0.aE3I/Ig3S.HZxPjZ2Zxa6p7GsWDBkDrtfTjtq.QcS', 0, 'Alex', 'rodriguez', '', '', '', 1, 'Jj6BTl2i8oxO', '', '2025-11-02'),
(23, 'luijoc75@gmail.com', '$2y$10$.6PxOxS.q6tY0nI7sr7c5eVh1VzrkWHrH5W5Quo0SPjIdXQ.k.SbC', 0, 'luis jose', 'castellanos', '', '', '', 0, 'zSnX6aMuZqQR', '', '2025-11-06'),
(24, 'sabina.rada@campusucc.edu.co', '$2y$10$WSyGp7YCrnSLpfiv9Kouf.PIsEPHqbv8wUowQ7XLlR7hvFZrbKU9i', 0, 'sabina', 'mendoza', 'bavaria club', '', '', 1, 'WeTJh3VNOA2y', '', '2025-11-06'),
(25, 'lucho@gmail.com', '$2y$10$2hBMj6YCBB/efogdJTR8huwGx2.MCeOisiTvuXOvbGjQNsHz3./uW', 0, 'luchin', 'lucho', '', '', '', 1, 'CZmrN1g9w4SG', '', '2025-11-07');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `details`
--
ALTER TABLE `details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
