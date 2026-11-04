-- =====================================================================
-- Base de Datos Histórica y Auditoría: MARAÑA CROCHET
-- Control de Cambios gestionado por Liquibase
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `marana_crochet_historico` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `marana_crochet_historico`;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: marana_crochet_historico
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `databasechangelog`
--

DROP TABLE IF EXISTS `databasechangelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `databasechangelog` (
  `ID` varchar(255) NOT NULL,
  `AUTHOR` varchar(255) NOT NULL,
  `FILENAME` varchar(255) NOT NULL,
  `DATEEXECUTED` datetime NOT NULL,
  `ORDEREXECUTED` int(11) NOT NULL,
  `EXECTYPE` varchar(10) NOT NULL,
  `MD5SUM` varchar(35) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  `COMMENTS` varchar(255) DEFAULT NULL,
  `TAG` varchar(255) DEFAULT NULL,
  `LIQUIBASE` varchar(20) DEFAULT NULL,
  `CONTEXTS` varchar(255) DEFAULT NULL,
  `LABELS` varchar(255) DEFAULT NULL,
  `DEPLOYMENT_ID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `databasechangeloglock`
--

DROP TABLE IF EXISTS `databasechangeloglock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `databasechangeloglock` (
  `ID` int(11) NOT NULL,
  `LOCKED` tinyint(1) NOT NULL,
  `LOCKGRANTED` datetime DEFAULT NULL,
  `LOCKEDBY` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historico_auditoria_usuarios`
--

DROP TABLE IF EXISTS `historico_auditoria_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_auditoria_usuarios` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `evento` varchar(60) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `detalles` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hist_audit_user` (`user_id`),
  KEY `idx_hist_audit_evento` (`evento`),
  KEY `idx_hist_audit_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historico_comprobantes`
--

DROP TABLE IF EXISTS `historico_comprobantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_comprobantes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `comprobante_id` varchar(30) NOT NULL,
  `pedido_id` varchar(30) NOT NULL,
  `cliente_nombre` varchar(120) DEFAULT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) NOT NULL,
  `codigo_aprobacion` varchar(40) DEFAULT NULL,
  `monto_total` int(11) DEFAULT NULL,
  `metodo_pago` varchar(80) DEFAULT NULL,
  `cuenta_banco` varchar(120) DEFAULT NULL,
  `nota_interna` text DEFAULT NULL,
  `revisado_por` varchar(120) DEFAULT 'Administraci├│n',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hist_comp_id` (`comprobante_id`),
  KEY `idx_hist_comp_pedido` (`pedido_id`),
  KEY `idx_hist_comp_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historico_inventario`
--

DROP TABLE IF EXISTS `historico_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_inventario` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sku` varchar(30) NOT NULL,
  `nombre_producto` varchar(150) DEFAULT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_nuevo` int(11) NOT NULL,
  `cantidad_movimiento` int(11) NOT NULL,
  `tipo_movimiento` varchar(40) NOT NULL,
  `referencia_id` varchar(60) DEFAULT NULL,
  `nota_motivo` varchar(255) DEFAULT NULL,
  `usuario_responsable` varchar(120) DEFAULT 'Sistema',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hist_inv_sku` (`sku`),
  KEY `idx_hist_inv_created_at` (`created_at`),
  KEY `idx_hist_inv_tipo` (`tipo_movimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historico_pedidos`
--

DROP TABLE IF EXISTS `historico_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_pedidos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pedido_id` varchar(30) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `cliente_nombre` varchar(120) DEFAULT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) NOT NULL,
  `estado_label` varchar(80) DEFAULT NULL,
  `guia` varchar(60) DEFAULT NULL,
  `transportista` varchar(120) DEFAULT NULL,
  `motivo_cambio` varchar(255) DEFAULT NULL,
  `origen_cambio` varchar(50) DEFAULT 'SISTEMA_WEB',
  `usuario_responsable` varchar(120) DEFAULT 'Sistema',
  `detalles_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hist_pedidos_pedido_id` (`pedido_id`),
  KEY `idx_hist_pedidos_created_at` (`created_at`),
  KEY `idx_hist_pedidos_estado` (`estado_nuevo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historico_precios`
--

DROP TABLE IF EXISTS `historico_precios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_precios` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sku` varchar(30) NOT NULL,
  `nombre_producto` varchar(150) DEFAULT NULL,
  `precio_anterior_cop` int(11) NOT NULL,
  `precio_nuevo_cop` int(11) NOT NULL,
  `costo_lana_anterior` int(11) DEFAULT NULL,
  `costo_lana_nuevo` int(11) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_responsable` varchar(120) DEFAULT 'Administraci├│n',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hist_precios_sku` (`sku`),
  KEY `idx_hist_precios_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-04 16:51:06
