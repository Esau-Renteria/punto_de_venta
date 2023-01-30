-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 26-11-2019 a las 00:17:28
-- Versión del servidor: 5.7.26
-- Versión de PHP: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `somega`
--

DELIMITER $$
--
-- Procedimientos
--
DROP PROCEDURE IF EXISTS `actualizar_precio_producto`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_precio_producto` (IN `n_cantidad` INT, IN `n_precio` DECIMAL(10,2), IN `codigo` INT)  BEGIN
    	DECLARE nueva_existencia int;
        DECLARE nuevo_total  decimal(10,2);
        DECLARE nuevo_precio decimal(10,2);
        
        DECLARE cant_actual int;
        DECLARE pre_actual decimal(10,2);
        
        DECLARE actual_existencia int;
        DECLARE actual_precio decimal(10,2);
                
        SELECT precio,existencia INTO actual_precio,actual_existencia FROM producto WHERE codproducto = codigo;
        SET nueva_existencia = actual_existencia + n_cantidad;
        SET nuevo_total = (actual_existencia * actual_precio) + (n_cantidad * n_precio);
        SET nuevo_precio = nuevo_total / nueva_existencia;
        
        UPDATE producto SET existencia = nueva_existencia, precio = nuevo_precio WHERE codproducto = codigo;
        
        SELECT nueva_existencia,nuevo_precio;
        
    END$$

DROP PROCEDURE IF EXISTS `add_detalle_temp`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_temp` (`codigo` INT, `cantidad` INT, `token_user` VARCHAR(50))  BEGIN

		DECLARE precio_actual decimal(10,2);
		SELECT precio INTO precio_actual FROM producto WHERE codproducto = codigo;

		INSERT INTO detalle_temp(token_user,codproducto,cantidad,precio_venta) VALUES(token_user,codigo,cantidad,precio_actual);

		SELECT tmp.correlativo, tmp.codproducto,p.descripcion,tmp.cantidad,tmp.precio_venta FROM detalle_temp tmp
		INNER JOIN producto p
		ON tmp.codproducto = p.codproducto
		WHERE tmp.token_user = token_user;

	END$$

DROP PROCEDURE IF EXISTS `anular_factura`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `anular_factura` (IN `no_factura` INT)  BEGIN
		DECLARE existe_factura int;
        DECLARE registros int;
        DECLARE a int;
        
        DECLARE cod_producto int;
        DECLARE cant_producto int;
        DECLARE existencia_actual int;
        DECLARE nueva_existencia int;
        
        SET existe_factura = (SELECT COUNT(*) FROM factura WHERE nofactura = no_factura and estado =1);
        
        IF existe_factura > 0 THEN
        CREATE TEMPORARY TABLE tbl_tmp(
            id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            cod_prod BIGINT,
            cant_prod int);
            
            
            SET a =1;
            SET registros =(SELECT COUNT(*) FROM detallefactura WHERE nofactura = no_factura);
            
            IF registros > 0 THEN
            INSERT INTO tbl_tmp(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detallefactura WHERE nofactura = no_factura;
            
            WHILE a <= registros DO
            SELECT cod_prod,cant_prod INTO cod_producto,cant_producto FROM tbl_tmp WHERE id = a;
            SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
            SET nueva_existencia = existencia_actual + cant_producto;
            UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto;
            SET a=a+1;
            END WHILE;
            
            UPDATE factura SET estado =2 WHERE nofactura = no_factura;
            DROP TABLE tbl_tmp;
            SELECT * FROM factura WHERE nofactura = no_factura;
            	
            END IF;
            
            
        ELSE
        	SELECT 0 factura;
            END IF;
            
     END$$

DROP PROCEDURE IF EXISTS `del_detalle_temp`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `del_detalle_temp` (`id_detalle` INT, `token` VARCHAR(50))  BEGIN
    	DELETE FROM detalle_temp WHERE correlativo = id_detalle;
        
        SELECT tmp.correlativo, tmp.codproducto,p.descripcion,tmp.cantidad,tmp.precio_venta FROM detalle_temp tmp
        INNER JOIN producto p
        ON tmp.codproducto = p.codproducto
        WHERE tmp.token_user = token;
    END$$

DROP PROCEDURE IF EXISTS `procesar_venta`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `procesar_venta` (IN `cod_usuario` INT, IN `cod_cliente` INT, IN `token` VARCHAR(50))  BEGIN
 		DECLARE factura INT;
        
        DECLARE registros INT;
        DECLARE total DECIMAL(10,2);
        
        DECLARE nueva_existencia int;
        DECLARE existencia_actual int;
        
        DECLARE tmp_cod_producto int;
        DECLARE tmp_cant_producto int;
        DECLARE a INT;
        SET a = 1;
        
        CREATE TEMPORARY TABLE tbl_tmp_tokenuser (
            id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            cod_prod BIGINT,
            cant_prod int);
            
       SET registros = (SELECT COUNT(*) FROM detalle_temp WHERE token_user = token);
       
       IF registros > 0 THEN
       		INSERT INTO tbl_tmp_tokenuser(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalle_temp WHERE token_user = token;
            
            INSERT INTO factura(usuario,codcliente) VALUES(cod_usuario,cod_cliente);
            SET factura = LAST_INSERT_ID();
            
            INSERT INTO detallefactura(nofactura,codproducto,cantidad,precio_venta) SELECT (factura) as nofactura, codproducto,cantidad,precio_venta FROM detalle_temp WHERE token_user = token;
            
            WHILE a <= registros DO
            SELECT cod_prod,cant_prod INTO tmp_cod_producto, tmp_cant_producto FROM tbl_tmp_tokenuser WHERE id = a;
            SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = tmp_cod_producto;
            
            SET nueva_existencia = existencia_actual - tmp_cant_producto;
            UPDATE producto SET existencia = nueva_existencia WHERE codproducto = tmp_cod_producto;
            
            SET a=a+1;
            
            END WHILE;
            
            SET total=(SELECT SUM(cantidad * precio_venta) FROM detalle_temp WHERE token_user = token);
            UPDATE factura SET totalfactura = total WHERE nofactura = factura;
            DELETE FROM detalle_temp WHERE token_user = token;
            TRUNCATE TABLE tbl_tmp_tokenuser;
            SELECT * FROM factura WHERE nofactura = factura;
            
       ELSE
       		SELECT 0;
       END IF;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE IF NOT EXISTS `cliente` (
  `idcliente` int(11) NOT NULL AUTO_INCREMENT,
  `nit` int(11) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `direccion` text,
  `dateadd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idcliente`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `nit`, `nombre`, `telefono`, `direccion`, `dateadd`, `usuario_id`, `estado`) VALUES
(1, 2234, 'Walter White', 674102121, '308 Negra Arroyo Lane', '2019-11-17 10:29:01', 1, 1),
(2, 43, 'Homero Simpson', 674123189, '742 Evergreen Terrace', '2019-11-17 10:36:09', 1, 0),
(3, 4, 'Jesse Pikman', 677123121, '9809 Margo Street', '2019-11-17 11:07:32', 1, 0),
(4, 145, 'Sherlock Holmes', 677188231, '221B Backer Street', '2019-11-17 12:12:43', 1, 0),
(5, 126, 'Marshal Eriksen', 677123453, ' 75 Avenida Amsterdam', '2019-11-17 12:21:08', 18, 0),
(6, 6789, 'Barney Stinson', 61893913, '74 Primera Avenida', '2019-11-17 23:22:29', 1, 0),
(7, 456, 'Lily Aldrin', 678453123, '75 Avenida Amsterdam', '2019-11-18 08:28:40', 1, 0),
(8, 90543, 'Ted', 6788, '75 Primera Avenida', '2019-11-18 13:59:20', 1, 0),
(9, 981, 'example', 1287, 'Palafo', '2019-11-19 11:09:48', 1, 0),
(10, 575678, 'qq', 3456, '23 qwe', '2019-11-19 19:56:21', 1, 0),
(11, 122212, 'asd', 21, 'asd', '2019-11-19 19:56:46', 1, 1),
(12, 909890, 'prueba ed', 677889, 'prueba dir', '2019-11-20 16:06:50', 1, 1),
(13, 1233, 'Robert Neville', 67896, '20 New York', '2019-11-21 19:04:16', 1, 1),
(14, 342414, 'Flor Flores', 324234, '45 Lane Rose', '2019-11-21 19:17:14', 1, 1),
(15, 3424145, 'Sola Tenneibaum', 57685, '435 Raptura', '2019-11-21 19:19:05', 1, 1),
(16, 123, 'Clara Talen', 456456, '56 Ever Street', '2019-11-21 19:20:21', 1, 1),
(17, 67, 'lkh', 78, 'uiu', '2019-11-21 19:22:00', 1, 1),
(18, 223, 'Cala Nad', 3242323, '209 FireStreet', '2019-11-22 04:02:47', 1, 1),
(19, 0, 'Cliente', 123, '123', '2019-11-24 04:44:56', 1, 1),
(21, 8787878, 'asd', 1234, 'asd asd', '2019-11-24 04:46:08', 1, 1),
(22, 8909, 'Ing Al', 67896445, '23 Vicente Guerrero', '2019-11-24 05:12:53', 1, 1),
(23, 123098, 'El Sujeto de Pruebas', 677674, '23 Foz', '2019-11-24 13:09:12', 1, 1),
(24, 1235, 'Esteba Pruebas', 67712, 'Pruebas', '2019-11-24 16:18:14', 1, 1),
(25, 1099, 'Sujeta de Pruebas', 1234, '123 Street', '2019-11-24 17:37:16', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nit` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `razon_social` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `email` varchar(200) COLLATE utf8_spanish_ci NOT NULL,
  `direccion` text COLLATE utf8_spanish_ci NOT NULL,
  `iva` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nit`, `nombre`, `razon_social`, `telefono`, `email`, `direccion`, `iva`) VALUES
(1, '123456', 'Omega', '', 67789123, 'Omega@gmail.com', 'Nuevo Ideal', '16.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallefactura`
--

DROP TABLE IF EXISTS `detallefactura`;
CREATE TABLE IF NOT EXISTS `detallefactura` (
  `correlativo` bigint(11) NOT NULL AUTO_INCREMENT,
  `nofactura` bigint(11) DEFAULT NULL,
  `codproducto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`correlativo`),
  KEY `codproducto` (`codproducto`),
  KEY `nofactura` (`nofactura`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detallefactura`
--

INSERT INTO `detallefactura` (`correlativo`, `nofactura`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(1, 1, 1, 1, '8.00'),
(2, 1, 7, 1, '148.14'),
(3, 1, 8, 4, '43.39'),
(4, 2, 8, 1, '43.39'),
(5, 3, 8, 3, '43.39'),
(6, 4, 8, 10, '43.39'),
(7, 4, 1, 3, '8.00'),
(8, 4, 7, 4, '148.14'),
(9, 5, 8, 10, '43.39'),
(10, 5, 7, 5, '148.14'),
(11, 5, 4, 50, '192.00'),
(12, 6, 1, 1, '8.00'),
(13, 6, 4, 1, '192.00'),
(14, 6, 7, 2, '148.14'),
(15, 7, 1, 1, '8.00'),
(16, 8, 2, 5, '905.00'),
(17, 9, 1, 1, '8.00'),
(18, 9, 7, 1, '148.14'),
(19, 9, 4, 9, '192.00'),
(20, 10, 32, 15, '70.00'),
(21, 10, 1, 3, '8.00'),
(22, 10, 2, 3, '905.00'),
(23, 11, 1, 1, '8.00'),
(24, 12, 1, 1, '8.00'),
(25, 12, 4, 10, '192.00'),
(27, 13, 32, 4, '70.00'),
(28, 14, 32, 11, '70.00'),
(29, 15, 1, 8, '8.00'),
(30, 15, 32, 8, '70.00'),
(32, 16, 1, 1, '8.00'),
(33, 16, 2, 2, '905.00'),
(34, 16, 8, 10, '43.39'),
(35, 17, 1, 9, '8.00'),
(36, 17, 2, 10, '905.00'),
(38, 18, 7, 2, '148.14'),
(39, 19, 7, 1, '148.14'),
(40, 20, 1, 1, '8.00'),
(41, 20, 2, 1, '905.00'),
(43, 21, 1, 1, '8.00'),
(44, 22, 1, 1, '8.00'),
(45, 23, 1, 1, '8.00'),
(46, 24, 1, 6, '8.00'),
(47, 24, 32, 15, '79.68'),
(48, 24, 2, 9, '905.00'),
(49, 25, 2, 5, '905.00'),
(50, 25, 32, 6, '79.68'),
(52, 26, 2, 1, '905.00'),
(53, 26, 4, 5, '192.00'),
(54, 26, 1, 1, '8.00'),
(55, 27, 1, 9, '8.00'),
(56, 28, 1, 1, '8.00'),
(57, 29, 1, 9, '8.00'),
(58, 29, 2, 4, '905.00'),
(60, 30, 4, 5, '192.00'),
(61, 30, 2, 2, '905.00'),
(63, 31, 7, 9, '148.14'),
(64, 32, 32, 5, '79.89'),
(65, 33, 1, 1, '8.00'),
(66, 33, 2, 8, '905.00'),
(68, 34, 1, 9, '8.00'),
(69, 34, 4, 5, '192.00'),
(71, 35, 1, 10, '8.00'),
(72, 35, 7, 5, '148.14'),
(74, 36, 1, 10, '8.00'),
(75, 36, 7, 5, '148.14'),
(77, 37, 33, 7, '140.14'),
(78, 38, 1, 20, '8.00'),
(79, 39, 1, 10, '8.00'),
(80, 39, 33, 5, '140.14'),
(82, 40, 33, 79, '147.62'),
(83, 41, 7, 1, '148.14'),
(84, 42, 1, 1, '8.00'),
(85, 43, 1, 1, '8.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_temp`
--

DROP TABLE IF EXISTS `detalle_temp`;
CREATE TABLE IF NOT EXISTS `detalle_temp` (
  `correlativo` int(11) NOT NULL AUTO_INCREMENT,
  `token_user` varchar(50) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  PRIMARY KEY (`correlativo`),
  KEY `nofactura` (`token_user`),
  KEY `codproducto` (`codproducto`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detalle_temp`
--

INSERT INTO `detalle_temp` (`correlativo`, `token_user`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(39, 'c51ce410c124a10e0db5e4b97fc2af39', 1, 1, '8.00'),
(40, 'c51ce410c124a10e0db5e4b97fc2af39', 7, 1, '148.14'),
(44, 'd3d9446802a44259755d38e6d163e820', 1, 1, '8.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

DROP TABLE IF EXISTS `entradas`;
CREATE TABLE IF NOT EXISTS `entradas` (
  `correlativo` int(11) NOT NULL AUTO_INCREMENT,
  `codproducto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  PRIMARY KEY (`correlativo`),
  KEY `codproducto` (`codproducto`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`correlativo`, `codproducto`, `fecha`, `cantidad`, `precio`, `usuario_id`) VALUES
(1, 1, '2019-11-17 23:49:20', 100, '110.00', 1),
(2, 2, '2019-11-17 23:52:17', 50, '900.00', 1),
(3, 3, '2019-11-18 07:03:44', 5, '125.00', 1),
(4, 4, '2019-11-18 07:09:56', 10, '200.00', 1),
(5, 5, '2019-11-18 07:15:51', 12, '236.00', 1),
(6, 6, '2019-11-18 07:17:49', 12, '434.00', 1),
(7, 7, '2019-11-18 07:31:07', 20, '300.00', 1),
(8, 8, '2019-11-18 07:31:36', 5, '100.00', 1),
(9, 9, '2019-11-18 08:31:41', 5, '300.00', 1),
(10, 10, '2019-11-18 14:02:08', 4, '10.00', 1),
(11, 11, '2019-11-19 11:31:59', 2, '12.00', 1),
(12, 12, '2019-11-19 11:33:53', 4, '12.00', 1),
(13, 13, '2019-11-19 11:42:13', 1, '23.00', 1),
(14, 14, '2019-11-19 11:43:32', 23, '67.00', 1),
(15, 4, '2019-11-19 19:48:20', 100, '160.00', 1),
(16, 3, '2019-11-19 19:48:57', 10, '100.00', 1),
(17, 13, '2019-11-19 19:51:31', 1, '100.00', 1),
(18, 14, '2019-11-19 19:52:21', 2, '100.00', 1),
(19, 15, '2019-11-19 19:58:16', 6, '12.00', 1),
(20, 16, '2019-11-19 19:58:33', 12, '12.00', 1),
(21, 16, '2019-11-19 19:58:48', 3, '11.00', 1),
(22, 15, '2019-11-19 19:59:04', 4, '12.00', 1),
(23, 16, '2019-11-19 21:53:05', 4, '5.00', 1),
(24, 15, '2019-11-19 21:53:28', 30, '12.00', 1),
(25, 15, '2019-11-19 21:53:46', 55, '14.00', 1),
(26, 8, '2019-11-19 22:42:02', 100, '140.00', 1),
(27, 8, '2019-11-19 22:45:34', 10, '12.00', 1),
(28, 7, '2019-11-19 22:45:59', 23, '23.00', 1),
(29, 6, '2019-11-19 22:46:44', 2, '12.00', 1),
(30, 9, '2019-11-19 22:50:46', 5, '10.00', 1),
(31, 9, '2019-11-19 22:51:14', 1, '100.00', 1),
(32, 9, '2019-11-19 22:51:23', 23, '456.00', 1),
(33, 10, '2019-11-19 22:51:42', 6, '20.00', 1),
(34, 9, '2019-11-19 22:51:59', 50, '200.00', 1),
(35, 9, '2019-11-19 22:52:08', 12, '100.00', 1),
(36, 10, '2019-11-19 23:02:13', 10, '14.00', 1),
(37, 10, '2019-11-19 23:02:35', 4, '10.00', 1),
(38, 16, '2019-11-19 23:04:22', 1, '2.00', 1),
(39, 11, '2019-11-19 23:05:14', 2, '20.00', 1),
(40, 15, '2019-11-19 23:12:18', 5, '17.00', 1),
(41, 16, '2019-11-20 06:00:08', 20, '11.00', 1),
(42, 7, '2019-11-20 06:54:57', 7, '100.00', 1),
(43, 9, '2019-11-20 08:09:49', 4, '120.00', 1),
(44, 9, '2019-11-20 08:10:06', 12, '170.00', 1),
(45, 10, '2019-11-20 12:24:43', 6, '17.00', 1),
(46, 10, '2019-11-20 12:24:50', 5, '4.00', 1),
(47, 10, '2019-11-20 12:25:03', 5, '2.00', 1),
(48, 1, '2019-11-20 12:26:33', 3, '120.00', 1),
(49, 1, '2019-11-20 12:26:45', 7, '200.00', 1),
(50, 17, '2019-11-20 12:29:39', 3, '300.00', 1),
(51, 17, '2019-11-20 12:30:02', 3, '200.00', 1),
(52, 9, '2019-11-20 12:33:21', 8, '140.00', 1),
(53, 2, '2019-11-20 12:36:20', 6, '900.00', 1),
(54, 2, '2019-11-20 12:36:58', 4, '1000.00', 1),
(55, 1, '2019-11-20 12:40:39', 20, '300.00', 1),
(56, 1, '2019-11-20 12:40:58', 20, '300.00', 1),
(57, 17, '2019-11-20 16:10:07', 4, '456.00', 1),
(58, 1, '2019-11-20 17:28:12', 30, '300.00', 1),
(59, 5, '2019-11-20 20:21:29', 12, '300.00', 1),
(60, 17, '2019-11-20 20:31:43', 20, '100.00', 1),
(61, 17, '2019-11-21 15:45:54', 30, '170.00', 1),
(62, 18, '2019-11-21 16:01:39', 20, '400.00', 8),
(63, 18, '2019-11-21 16:10:45', 45, '390.00', 8),
(64, 12, '2019-11-21 17:00:04', 7, '6.00', 1),
(65, 12, '2019-11-21 17:14:47', 7, '6.00', 1),
(66, 9, '2019-11-21 18:03:37', 80, '150.00', 1),
(67, 19, '2019-11-21 18:05:47', 20, '70.00', 1),
(68, 19, '2019-11-21 18:06:28', 20, '80.00', 1),
(69, 20, '2019-11-22 21:47:40', 21, '56.00', 1),
(70, 20, '2019-11-22 21:48:30', 21, '4.00', 1),
(71, 7, '2019-11-23 08:29:10', 10, '160.00', 1),
(72, 7, '2019-11-23 08:29:20', 5, '160.00', 1),
(73, 21, '2019-11-23 08:41:38', 8, '12.00', 1),
(74, 22, '2019-11-23 08:42:31', 2, '1.00', 1),
(75, 22, '2019-11-23 08:44:36', 3, '3.00', 1),
(76, 8, '2019-11-23 08:46:52', 3, '3.00', 1),
(77, 22, '2019-11-23 08:48:41', 4, '3.00', 1),
(78, 23, '2019-11-23 08:54:06', 2, '2.00', 1),
(79, 24, '2019-11-23 09:03:13', 32, '23.00', 27),
(80, 25, '2019-11-23 09:10:35', 23, '23.00', 27),
(81, 25, '2019-11-23 09:10:35', 23, '23.00', 27),
(82, 26, '2019-11-23 09:14:23', 454, '456.00', 1),
(83, 27, '2019-11-23 09:15:56', 150, '110.00', 1),
(85, 28, '2019-11-23 09:17:59', 23, '43.00', 1),
(86, 29, '2019-11-23 09:21:13', 23, '23.00', 1),
(87, 30, '2019-11-23 09:23:59', 116, '12.00', 1),
(88, 30, '2019-11-23 09:31:02', 20, '5.00', 1),
(89, 30, '2019-11-23 09:31:30', 34, '6.00', 1),
(90, 31, '2019-11-23 16:33:00', 20, '150.00', 1),
(91, 31, '2019-11-23 16:33:48', 20, '140.00', 1),
(92, 4, '2019-11-23 17:14:08', 10, '180.00', 1),
(93, 4, '2019-11-23 17:14:38', 30, '300.00', 1),
(94, 32, '2019-11-24 05:16:02', 20, '70.00', 1),
(95, 32, '2019-11-24 05:26:47', 19, '70.00', 1),
(96, 32, '2019-11-24 07:25:03', 30, '80.00', 1),
(97, 32, '2019-11-24 08:07:06', 20, '80.00', 1),
(98, 32, '2019-11-24 09:34:50', 5, '100.00', 1),
(99, 33, '2019-11-24 09:35:29', 20, '150.00', 1),
(100, 32, '2019-11-24 17:18:10', 20, '95.00', 1),
(101, 33, '2019-11-24 17:40:26', 40, '170.00', 1),
(102, 33, '2019-11-24 17:40:37', 10, '1.00', 1),
(103, 33, '2019-11-24 21:05:58', 10, '200.00', 1),
(104, 32, '2019-11-25 07:30:05', 5, '100.00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

DROP TABLE IF EXISTS `factura`;
CREATE TABLE IF NOT EXISTS `factura` (
  `nofactura` bigint(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `totalfactura` decimal(10,2) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`nofactura`),
  KEY `usuario` (`usuario`),
  KEY `codcliente` (`codcliente`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`nofactura`, `fecha`, `usuario`, `codcliente`, `totalfactura`, `estado`) VALUES
(1, '2019-11-23 19:23:02', 1, 1, '329.70', 1),
(2, '2019-11-23 19:26:58', 1, 1, '43.39', 1),
(3, '2019-11-23 19:28:02', 1, 1, '130.17', 1),
(4, '2019-11-23 19:32:42', 1, 13, '1050.46', 1),
(5, '2019-11-23 19:35:18', 1, 1, '10774.60', 1),
(6, '2019-11-24 05:01:06', 1, 1, '496.28', 1),
(7, '2019-11-24 05:06:05', 1, 16, '8.00', 1),
(8, '2019-11-24 05:07:31', 1, 1, '4525.00', 1),
(9, '2019-11-24 05:10:37', 1, 19, '1884.14', 1),
(10, '2019-11-24 05:18:46', 1, 19, '3789.00', 1),
(11, '2019-11-24 05:19:31', 1, 1, '8.00', 1),
(12, '2019-11-24 05:20:41', 1, 22, '1928.00', 1),
(13, '2019-11-24 05:23:53', 1, 1, '280.00', 1),
(14, '2019-11-24 06:01:09', 1, 16, '770.00', 1),
(15, '2019-11-24 06:23:05', 1, 19, '624.00', 1),
(16, '2019-11-24 07:14:23', 1, 1, '2251.90', 1),
(17, '2019-11-24 07:15:32', 1, 19, '9122.00', 2),
(18, '2019-11-24 07:16:04', 1, 19, '296.28', 1),
(19, '2019-11-24 07:16:35', 1, 19, '148.14', 1),
(20, '2019-11-24 07:18:35', 1, 19, '913.00', 2),
(21, '2019-11-24 07:19:08', 1, 19, '8.00', 1),
(22, '2019-11-24 07:20:14', 1, 16, '8.00', 1),
(23, '2019-11-24 07:22:00', 1, 19, '8.00', 1),
(24, '2019-11-24 07:25:34', 1, 19, '9388.20', 1),
(25, '2019-11-24 07:27:03', 1, 1, '5003.08', 1),
(26, '2019-11-24 07:31:45', 1, 22, '1873.00', 1),
(27, '2019-11-24 07:35:41', 1, 13, '72.00', 1),
(28, '2019-11-24 07:39:37', 1, 13, '8.00', 1),
(29, '2019-11-24 07:54:14', 1, 1, '3692.00', 1),
(30, '2019-11-24 07:59:45', 1, 13, '2770.00', 1),
(31, '2019-11-24 08:00:46', 1, 1, '1333.26', 2),
(32, '2019-11-24 08:49:01', 1, 13, '399.45', 2),
(33, '2019-11-24 11:42:16', 1, 18, '7248.00', 2),
(34, '2019-11-24 13:09:53', 1, 23, '1032.00', 2),
(35, '2019-11-24 16:20:29', 1, 24, '820.70', 2),
(36, '2019-11-24 16:25:16', 1, 13, '820.70', 2),
(37, '2019-11-24 17:45:49', 1, 25, '980.98', 2),
(38, '2019-11-24 21:02:13', 1, 19, '160.00', 2),
(39, '2019-11-24 21:03:31', 1, 19, '780.70', 2),
(40, '2019-11-24 21:06:27', 1, 19, '11661.98', 2),
(41, '2019-11-24 21:49:19', 1, 16, '148.14', 2),
(42, '2019-11-25 05:24:50', 1, 16, '8.00', 1),
(43, '2019-11-25 05:27:17', 1, 13, '8.00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

DROP TABLE IF EXISTS `producto`;
CREATE TABLE IF NOT EXISTS `producto` (
  `codproducto` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) DEFAULT NULL,
  `proveedor` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `existencia` int(11) DEFAULT NULL,
  `dateadd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT '1',
  `foto` text,
  PRIMARY KEY (`codproducto`),
  KEY `proveedor` (`proveedor`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`codproducto`, `descripcion`, `proveedor`, `precio`, `existencia`, `dateadd`, `usuario_id`, `estado`, `foto`) VALUES
(1, 'Teclado', 15, '8.00', 127, '2019-11-17 23:49:20', 1, 1, 'img_6ab3fad5bac3b6a3ef14140ad84d4bf4.jpg'),
(2, 'Microfono Condensador', 15, '905.00', 48, '2019-11-17 23:52:17', 1, 1, 'img_6f86c821d80cb4e9dd500a7c121dc7ec.jpg'),
(3, 'Cargador de carga rapida', 15, '108.33', 15, '2019-11-18 07:03:44', 1, 0, 'img_4831ef6fc9c98e754df8ea60cd186c3e.jpg'),
(4, 'Mouse Pulsefire Alambrico', 17, '192.00', 70, '2019-11-18 07:09:56', 1, 1, 'img_88c8324a847d4b54efcf6f63239887e9.jpg'),
(5, 'Audifonos Cloud II', 15, '268.00', 24, '2019-11-18 07:15:51', 1, 0, 'img_da01b8abb949912b7ac5c9ea67d1a5ed.jpg'),
(6, 'sdfdsf', 7, '373.71', 14, '2019-11-18 07:17:49', 1, 0, 'img_a8fbf2e8e22ef0c558557a383b154d0f.jpg'),
(7, 'Banco de poder', 17, '148.14', 49, '2019-11-18 07:31:07', 1, 1, 'img_29e2a2f71b17e752aaed876f39a9b8d0.jpg'),
(8, 'Cable Tipo C', 17, '43.39', 80, '2019-11-18 07:31:36', 1, 1, 'img_425f1f5e49099db0c1e939c85e2021de.jpg'),
(9, 'Reloj Tactico', 3, '126.20', 200, '2019-11-18 08:31:41', 1, 0, 'img_6ad18ee9d90584f868e586f12a62eb1d.jpg'),
(10, 'Bateria AAA', 19, '11.81', 40, '2019-11-18 14:02:08', 1, 0, 'img_producto.jpg'),
(11, 'example', 20, '16.00', 4, '2019-11-19 11:31:59', 1, 0, 'img_producto.jpg'),
(12, 'j', 7, '7.33', 18, '2019-11-19 11:33:53', 1, 0, 'img_producto.jpg'),
(13, 'uihui', 7, '61.50', 2, '2019-11-19 11:42:13', 1, 0, 'img_acbd28dbb2000d3b07a0c8acf353a64e.jpg'),
(14, 'k', 7, '69.64', 25, '2019-11-19 11:43:32', 1, 0, 'img_f3b33916b000bf695af39531397f5739.jpg'),
(15, 'Baterias', 13, '15.00', 100, '2019-11-19 19:58:16', 1, 0, 'img_producto.jpg'),
(16, 'rter', 7, '10.48', 40, '2019-11-19 19:58:33', 1, 1, 'img_producto.jpg'),
(17, 'Bocina Bluethoot', 14, '169.50', 60, '2019-11-20 12:29:39', 1, 0, 'img_ecec898d96829311b42b18bd0c98e581.jpg'),
(18, 'Tripie', 17, '450.00', 65, '2019-11-21 16:01:39', 8, 0, 'img_66cb2b9626f3be18bc180fb4511f6fdb.jpg'),
(19, 'Protoboard', 11, '75.00', 40, '2019-11-21 18:05:47', 1, 0, 'img_producto.jpg'),
(20, 'hol', 14, '3.00', 42, '2019-11-22 21:47:40', 1, 0, 'img_producto.jpg'),
(21, 'HJ', 7, '12.00', 8, '2019-11-23 08:41:38', 1, 1, 'img_producto.jpg'),
(22, 'Hola', 7, '9.23', 13, '2019-11-23 08:42:31', 1, 1, 'img_producto.jpg'),
(23, 'd', 7, '2.00', 2, '2019-11-23 08:54:06', 1, 1, 'img_producto.jpg'),
(24, 'AD', 7, '23.00', 32, '2019-11-23 09:03:13', 27, 1, NULL),
(25, 'ter', 7, '23.00', 23, '2019-11-23 09:10:35', 27, 1, NULL),
(26, 'ghj', 14, '456.00', 454, '2019-11-23 09:14:23', 1, 1, 'img_producto.jpg'),
(27, 'Ira', 2, '110.00', 150, '2019-11-23 09:15:56', 1, 1, 'hola.png'),
(28, '34', 7, '43.00', 23, '2019-11-23 09:17:59', 1, 1, 'img_producto.jpg'),
(29, '324', 7, '23.00', 23, '2019-11-23 09:21:13', 1, 0, 'img_producto.jpg'),
(30, 'jol', 7, '4.52', 170, '2019-11-23 09:23:59', 1, 0, 'img_producto.jpg'),
(31, 'Prueba', 16, '145.00', 40, '2019-11-23 16:33:00', 1, 0, 'img_producto.jpg'),
(32, 'Paquete 4 Pilas Recargables', 19, '88.95', 60, '2019-11-24 05:16:02', 1, 1, 'img_producto.jpg'),
(33, 'Pendrive 32 gb', 11, '147.62', 80, '2019-11-24 09:35:29', 1, 1, 'img_e33dbfebc8b2271b1065566c4c0a1285.jpg');

--
-- Disparadores `producto`
--
DROP TRIGGER IF EXISTS `entradas_A_I_N`;
DELIMITER $$
CREATE TRIGGER `entradas_A_I_N` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
    	INSERT INTO entradas(codproducto,cantidad,precio,usuario_id)
        VALUES (new.codproducto, new.existencia,new.precio,new.usuario_id);
     END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
CREATE TABLE IF NOT EXISTS `proveedor` (
  `codproveedor` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor` varchar(100) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` bigint(11) DEFAULT NULL,
  `direccion` text,
  `dateadd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`codproveedor`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`codproveedor`, `proveedor`, `contacto`, `telefono`, `direccion`, `dateadd`, `usuario_id`, `estado`) VALUES
(1, 'BIC', 'Claudia Rosales', 789877889, 'Avenida las Americas', '2019-11-17 16:17:42', 0, 0),
(2, 'CASIO', 'Jorge Herrera', 565656565656, 'Calzada Las Flores', '2019-11-17 16:17:42', 0, 0),
(3, 'Sandpiper of California  ', 'Jack Ferngunson', 982877450, 'Avenida Elena Zona 4, Guatemala', '2019-11-17 16:17:42', 0, 0),
(4, 'Dell Compani', 'Roberto Estrada', 2147483647, 'Guatemala, Guatemala', '2019-11-17 16:17:42', 0, 1),
(5, 'Olimpia S.A', 'Elena Franco Morales', 564535676, '5ta. Avenida Zona 4 Ciudad', '2019-11-17 16:17:42', 0, 1),
(6, 'Oster', 'Fernando Guerra', 78987678, 'Calzada La Paz, Guatemala', '2019-11-17 16:17:42', 0, 0),
(7, 'ACELTECSA S.A ', 'Ruben PÃ©rez', 78987988, 'Colonia las Victorias', '2019-11-17 16:17:42', 0, 1),
(8, 'Sony', 'Julieta Contreras', 89476787, 'Antigua Guatemala', '2019-11-17 16:17:42', 0, 1),
(9, 'VAIO ', 'Felix Arnoldo', 476378276, 'Avenida las Americas Zona 13', '2019-11-17 16:17:42', 0, 1),
(10, 'SUMA', 'Oscar Maldonado', 788376787, 'Colonia San Jose, Zona 5 Guatemala', '2019-11-17 16:17:42', 0, 1),
(11, 'HP', 'Angel Cardona', 2147483647, '5ta. calle zona 4 Guatemala', '2019-11-17 16:17:42', 0, 1),
(12, 'Sony', 'Zoila Vaca', 677107654, '305 Palafox', '2019-11-17 19:17:20', 1, 0),
(13, 'Nvidia', 'Maria Mercedes', 67123452, '309 Vicente Guerrero', '2019-11-17 21:15:20', 1, 1),
(14, 'Digital Rev ', 'Jazmin Medina', 618561234, '678 Francisco I Madero', '2019-11-17 23:21:50', 1, 1),
(15, 'Hyper X', 'Federico Diaz', 677123432, '32 Constitucion', '2019-11-18 06:38:59', 1, 1),
(16, 'LG', 'Salma Lopez', 654123456, '124 Miguel Aleman', '2019-11-18 07:02:43', 1, 1),
(17, 'Samsung', 'Nathan Acuna', 645321234, '56 Primera Avenida', '2019-11-18 07:30:35', 1, 1),
(18, 'Jedel', 'Adrian Estrada', 678123422, '65 Revolucion', '2019-11-18 08:30:51', 1, 0),
(19, 'Duracell', 'Dorian Duran', 6754312, '376 Insurgentes', '2019-11-18 14:00:51', 1, 1),
(20, 'example INC', 'example', 67710982, 'qwert', '2019-11-19 11:16:27', 1, 1),
(21, 'INCY', 'Jack', 786, 'jol', '2019-11-19 11:31:16', 1, 0),
(22, 'ren', 'renn', 2234, '23 wert', '2019-11-19 19:57:13', 1, 1),
(23, 'prueba inc', 'pru', 67878, '45ert', '2019-11-20 16:09:43', 1, 1),
(24, 'dfg ', 'dfg', 67, 'fdg', '2019-11-22 21:46:27', 1, 0),
(25, 'Papel Digital ', 'Federico Montenegro', 2132134, '34 Constitucion', '2019-11-24 05:14:10', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `idrol` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`idrol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idrol`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `idusuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `usuario` varchar(15) DEFAULT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `rol` int(11) DEFAULT NULL,
  `estado` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idusuario`),
  KEY `rol` (`rol`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `correo`, `usuario`, `clave`, `rol`, `estado`) VALUES
(1, 'Nik', 'actesla@gmail.com', 'tesla', 'e2fc714c4727ee9395f324cd2e7f331f', 1, 1),
(2, 'Mary', 'racurie@gmail.com', 'curie', '0cc175b9c0f1b6a831c399e269772661', 2, 0),
(3, 'Tom', 'dcedison@gmail.com', 'edison', '81dc9bdb52d04dc20036dbd8313ed055', 2, 0),
(4, 'Isak', 'fgNewton@gmail.com', 'newton', '81dc9bdb52d04dc20036dbd8313ed055', 3, 0),
(5, 'Arki', 'pasiracusa@gmail.com', 'siracusa', '81dc9bdb52d04dc20036dbd8313ed055', 2, 0),
(6, 'Leo', 'hedavince@gmail.com', 'davinci', '81dc9bdb52d04dc20036dbd8313ed055', 3, 0),
(7, 'Nio', 'shcopernico@gmail.com', 'coperico', 'c4ca4238a0b923820dcc509a6f75849b', 2, 0),
(8, 'Gal', 'lmgalilei@gmail.com', 'galilei', 'c4ca4238a0b923820dcc509a6f75849b', 2, 1),
(9, 'Lou', 'pppasteur@gmail.com', 'pasteur', '81dc9bdb52d04dc20036dbd8313ed055', 2, 0),
(10, 'Alb', 'treinstein@gmail.com', 'einstein', 'c4ca4238a0b923820dcc509a6f75849b', 3, 1),
(11, 'Steph', 'euhawking@gmail.com', 'hawking', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(12, 'Charl', 'tedarwin@gmail.com', 'darwin', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(13, 'Ale', 'lpfleming@gmail.com', 'fleming', 'c4ca4238a0b923820dcc509a6f75849b', 3, 1),
(14, 'Greg', 'hgmendel@gmail.com', 'mendel', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(15, 'Mike', 'befaraday@gmail.com', 'faraday', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(16, 'Max', 'tcplanck@gmail.com', 'planck', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(17, 'Wern', 'piheinsenberg@gmail.com', 'heinsenberg', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(18, 'Rud', 'hzhertz@gmail.com', 'hertz', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(19, 'Erw', 'fschrodinger@gmail.com', 'schrodinger', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(20, 'Alf', 'dinobel@gmail.com', 'nobel', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1),
(21, 'Mike', 'cpservet@gmail.com', 'servet', '0cc175b9c0f1b6a831c399e269772661', 2, 1),
(22, 'Ka', 'tplandsteiner@gmail.com', 'landsteiner', '81dc9bdb52d04dc20036dbd8313ed055', 3, 0),
(23, 'Sim', 'tllaplace@gmail.com', 'laplac', 'e2fc714c4727ee9395f324cd2e7f331f', 2, 1),
(24, 'Rob', 'symathenson@gmail.com', 'mathenson', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(25, 'Isak', 'irasimov@gmail.com', 'asimov', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(26, 'Arth', 'shdoyle@gmail.com', 'doyle', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(27, 'Al', 'pcturing@gmail.com', 'turing', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(28, 'Edw', 'aaelric@gmail.com', 'elric', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(29, 'Ex', 'example@gmail.com', 'example', '81dc9bdb52d04dc20036dbd8313ed055', 2, 0),
(30, 'Juan', 'juan@gmail.com', 'abc', '81dc9bdb52d04dc20036dbd8313ed055', 2, 0),
(31, 'dfg', 'dfsdfg@gmail.com', 'sdfg', '81dc9bdb52d04dc20036dbd8313ed055', 2, 0),
(32, 'Aiden', 'wdpearce@gmail.com', 'pearce', '81dc9bdb52d04dc20036dbd8313ed055', 3, 1);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`);

--
-- Filtros para la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD CONSTRAINT `detallefactura_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD CONSTRAINT `detalle_temp_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`codcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`codproveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`idrol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
