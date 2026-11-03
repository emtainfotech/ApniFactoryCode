/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.3.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: apnifactory_local
-- ------------------------------------------------------
-- Server version	12.3.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `services` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES
(1,'Super Admin','admin@apnifactory.local',NULL,'$2y$10$klyDvl77/yj0D9JlD/ZU3eoCEc.hHPUfC8TD.QdkoFQV/.5UkkOtG','superadmin','all',NULL,'2026-08-22 03:38:23','2026-08-22 03:38:23');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `advertisements`
--

DROP TABLE IF EXISTS `advertisements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `advertisements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `addby` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `adminmsg` text DEFAULT NULL,
  `screen` varchar(255) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advertisements`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `advertisements` WRITE;
/*!40000 ALTER TABLE `advertisements` DISABLE KEYS */;
INSERT INTO `advertisements` VALUES
(1,'Special Promo','Great deals across categories','slider/advertisement.png',NULL,NULL,NULL,'CategoryScreen','2026-01-01','2030-12-31',NULL,'Active','2026-10-31 05:32:20','2026-10-31 05:32:20'),
(2,'Company Promo','Verified manufacturers','slider/banner.jpg',NULL,NULL,NULL,'CompanyScreen','2026-01-01','2030-12-31',NULL,'Active','2026-10-31 05:32:20','2026-10-31 05:32:20');
/*!40000 ALTER TABLE `advertisements` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `bank_details`
--

DROP TABLE IF EXISTS `bank_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accountholder` varchar(255) DEFAULT NULL,
  `accountno` varchar(255) DEFAULT NULL,
  `bankname` varchar(255) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `ifsc` varchar(255) DEFAULT NULL,
  `isprimary` enum('N','Y') NOT NULL DEFAULT 'N',
  `addby` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_details`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `bank_details` WRITE;
/*!40000 ALTER TABLE `bank_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_details` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `box_packings`
--

DROP TABLE IF EXISTS `box_packings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `box_packings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `pcs` int(11) DEFAULT NULL,
  `maincategory_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `box_packings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `box_packings` WRITE;
/*!40000 ALTER TABLE `box_packings` DISABLE KEYS */;
INSERT INTO `box_packings` VALUES
(1,'1 Litre',1,1,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(2,'4 Litres',4,1,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(3,'10 Litres',10,1,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(4,'20 Litres',20,1,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(5,'10 Meters',10,2,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(6,'25 Meters',25,2,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(7,'50 Meters',50,2,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(8,'100 Meters',100,2,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(9,'Size S',1,3,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(10,'Size M',1,3,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(11,'Size L',1,3,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(12,'Size XL',1,3,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(13,'Size XXL',1,3,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(14,'Single Pcs',1,4,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(15,'Pack of 3',3,4,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(16,'Box of 10',10,4,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(17,'Box of 12 (Assorted S-XL)',1,3,'Active','2026-11-02 00:29:36','2026-11-02 00:29:36'),
(25,'100 ML',1,1,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(26,'200 ML',1,1,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(27,'500 ML',1,1,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(28,'1 Ltr',1,1,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(29,'4 Ltr',1,1,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(30,'10 Ltr',1,1,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(31,'20 Ltr',1,1,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(32,'900 ML BASES',1,1,'Active','2026-11-02 00:57:21','2026-11-02 00:57:21'),
(33,'3.6 LTR BASES',1,1,'Active','2026-11-02 00:57:21','2026-11-02 00:57:21'),
(34,'9 LTR BASES',1,1,'Active','2026-11-02 00:57:21','2026-11-02 00:57:21'),
(35,'18 LTR BASES',1,1,'Active','2026-11-02 00:57:22','2026-11-02 00:57:22'),
(36,'25LTR',1,1,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(37,'5LTR',1,1,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(38,'1LTR',1,1,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(39,'20LTR',1,1,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(40,'4LTR',1,1,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(41,'500 ML x 4 Pc',1,1,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(42,'200 ML x 12 Pc',1,1,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(43,'1 kg x 20 Pc',1,1,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(44,'2 KG',1,1,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(45,'5 KG',1,1,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(46,'10 KG',1,1,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(47,'20 KG',1,1,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30');
/*!40000 ALTER TABLE `box_packings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `mid` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `addby` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `trademarkno` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `adminresponse` varchar(255) DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES
(1,'FactoryStandard',1,1,2,2,NULL,'brand/badge_factorystandard.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 03:47:37','2026-11-02 01:36:19'),
(2,'Asian Paints Pro',3,1,1,1,NULL,'brand/badge_asian_paints_pro.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 03:47:37','2026-11-02 01:36:19'),
(3,'Berger ColorCraft',3,1,1,5,NULL,'brand/badge_berger_colorcraft.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 03:47:37','2026-11-02 01:36:19'),
(4,'Vardhman Textiles',1,1,2,2,NULL,'brand/badge_vardhman.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 03:47:37','2026-11-02 01:36:19'),
(5,'Mysore Silk Works',1,1,2,3,NULL,'brand/badge_mysoresilk.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 03:47:37','2026-11-02 01:36:19'),
(6,'Raymond Apparel',1,1,3,4,NULL,'brand/badge_raymond.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 03:47:37','2026-11-02 01:36:19'),
(7,'FabIndia Crafts',1,1,3,6,NULL,'brand/badge_fabindia.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 03:47:37','2026-11-02 01:36:19'),
(8,'MasterCraft Tools',1,1,4,7,NULL,'brand/badge_mastercraft.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 03:47:37','2026-11-02 01:36:19'),
(9,'Asian Paints Pro',3,1,1,1,NULL,'brand/badge_asian_paints_pro.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 04:33:34','2026-11-02 01:36:19'),
(10,'Berger ColorCraft',3,1,1,5,NULL,'brand/badge_berger_colorcraft.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 04:33:34','2026-11-02 01:36:19'),
(11,'Vardhman Textiles',3,1,2,2,NULL,'brand/badge_vardhman.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 04:33:34','2026-11-02 01:36:19'),
(12,'Mysore Silk Works',3,1,2,3,NULL,'brand/badge_mysoresilk.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 04:33:34','2026-11-02 01:36:19'),
(13,'Raymond Apparel',3,1,3,4,NULL,'brand/badge_raymond.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 04:33:34','2026-11-02 01:36:19'),
(14,'FabIndia Crafts',3,1,3,6,NULL,'brand/badge_fabindia.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 04:33:34','2026-11-02 01:36:19'),
(15,'MasterCraft Tools',3,1,4,7,NULL,'brand/badge_mastercraft.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 04:33:34','2026-11-02 01:36:19'),
(16,'FactoryStandard',3,1,2,2,NULL,'brand/badge_factorystandard.png',NULL,NULL,NULL,'Approved','Active','2026-08-24 04:33:34','2026-11-02 01:36:19'),
(17,'UltraShield Extreme Armor',3,1,1,25,'Seller','brand/badge_ultrashield.png','TM-987214',NULL,'brand','Approved','Active','2026-10-31 01:49:00','2026-11-02 01:36:19'),
(19,'Creation',3,1,1,8,NULL,'brand/badge_creation.png',NULL,NULL,NULL,'Approved','Active','2026-11-02 00:57:20','2026-11-02 01:36:19'),
(20,'Creation Gold Luxury Silk Emulsion Base',3,1,1,1,NULL,'brand/badge_creation_gold.png',NULL,NULL,NULL,'Approved','Active','2026-11-02 00:57:22','2026-11-02 01:36:19'),
(21,'Shrirang',17,8,1,23,NULL,'brand/badge_shrirang.png',NULL,NULL,NULL,'Approved','Active','2026-11-02 00:57:23','2026-11-02 01:36:19'),
(22,'Autocoats Enamel',16,7,1,19,NULL,'brand/badge_autocoats_enamel.png',NULL,NULL,NULL,'Approved','Active','2026-11-02 00:57:30','2026-11-02 01:36:19'),
(23,'Bright',17,8,1,22,NULL,'brand/badge_bright.png',NULL,NULL,NULL,'Approved','Active','2026-11-02 00:57:31','2026-11-02 01:36:19'),
(24,'Parijat',3,1,1,1,NULL,'brand/badge_parijat.png',NULL,NULL,NULL,'Approved','Active','2026-11-02 00:57:35','2026-11-02 01:36:19'),
(25,'Bright Auto Paint',17,8,1,20,NULL,'brand/badge_bright_auto_paint.png',NULL,NULL,NULL,'Approved','Active','2026-11-02 00:57:45','2026-11-02 01:36:19'),
(26,'Autocoats',16,7,1,19,NULL,'brand/badge_autocoats.png',NULL,NULL,NULL,'Approved','Active','2026-11-02 01:15:31','2026-11-02 01:36:19');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `brand_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `productname` varchar(255) DEFAULT NULL,
  `addressid` bigint(20) unsigned DEFAULT NULL,
  `couponbyadmin` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES
(4,30,1,1,19,10,'INTERIOR EMULSION WALL PRIMER',1,NULL,NULL,NULL),
(5,31,1,1,19,11,'EXTERIOR /INTERIOR WALL PRIMER',1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cartattribute`
--

DROP TABLE IF EXISTS `cartattribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cartattribute` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `cart_id` bigint(20) unsigned DEFAULT NULL,
  `product_attributes_id` bigint(20) unsigned DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `boxpcs` int(11) NOT NULL DEFAULT 1,
  `unitprice` decimal(12,2) NOT NULL DEFAULT 0.00,
  `totalprice` decimal(12,2) NOT NULL DEFAULT 0.00,
  `prprice` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(8,2) NOT NULL DEFAULT 0.00,
  `taxamount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `coupon` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cartattribute`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cartattribute` WRITE;
/*!40000 ALTER TABLE `cartattribute` DISABLE KEYS */;
INSERT INTO `cartattribute` VALUES
(4,1,4,287,4,1,120.40,481.60,120.40,18.00,86.69,0.00,NULL,NULL),
(5,1,5,292,4,1,114.80,459.20,114.80,18.00,82.66,0.00,NULL,NULL),
(6,1,5,291,2,1,406.00,812.00,406.00,18.00,146.16,0.00,NULL,NULL);
/*!40000 ALTER TABLE `cartattribute` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT NULL,
  `maincategory_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `addby` varchar(255) DEFAULT NULL,
  `adminmsg` text DEFAULT NULL,
  `adminstatus` varchar(255) DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,1,1,'Enamel Paints','Enamel & Synthetic Paints','category/enamel-paints.png',NULL,NULL,NULL,NULL,'Active','2026-08-24 04:33:34','2026-11-02 01:15:30'),
(2,2,2,'Cotton Fabrics','Cotton Fabrics','default.png',NULL,NULL,NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(3,2,2,'Silk Fabrics','Silk Fabrics','default.png',NULL,NULL,NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(4,3,3,'Men Wear','Men Wear','default.png',NULL,NULL,NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(5,1,1,'Emulsion Paints','Emulsion & Wall Paints','category/emulsion-paints.png',NULL,NULL,NULL,NULL,'Active','2026-08-24 04:33:34','2026-11-02 01:15:30'),
(6,3,3,'Women Wear','Women Wear','default.png',NULL,NULL,NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(7,4,4,'Painting Tools','Hardware & Painting Tools','default.png',NULL,NULL,NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(8,NULL,1,'Metallic Paints','Metallic Paints & Coatings','category/metallic-paints.png',NULL,NULL,NULL,1,'Active','2026-11-02 01:15:22','2026-11-02 01:15:22'),
(9,NULL,1,'Shine Emulsion','Shine Emulsion & Coatings','category/shine-emulsion.png',NULL,NULL,NULL,2,'Active','2026-11-02 01:15:22','2026-11-02 01:15:22'),
(10,NULL,1,'Interior Wall Primer','Interior Wall Primer & Coatings','category/interior-wall-primer.png',NULL,NULL,NULL,3,'Active','2026-11-02 01:15:23','2026-11-02 01:15:23'),
(11,NULL,1,'Exterior Wall Primer','Exterior Wall Primer & Coatings','category/exterior-wall-primer.png',NULL,NULL,NULL,4,'Active','2026-11-02 01:15:23','2026-11-02 01:15:23'),
(12,NULL,1,'2K PU Metal Finish','2K PU Metal Finish & Coatings','category/2k-pu-metal-finish.png',NULL,NULL,NULL,5,'Active','2026-11-02 01:15:24','2026-11-02 01:15:24'),
(13,NULL,1,'1K PU Coatings','1K PU Coatings & Coatings','category/1k-pu-coatings.png',NULL,NULL,NULL,6,'Active','2026-11-02 01:15:24','2026-11-02 01:15:24'),
(14,NULL,1,'Distemper','Distemper & Coatings','category/distemper.png',NULL,NULL,NULL,7,'Active','2026-11-02 01:15:24','2026-11-02 01:15:24'),
(15,NULL,1,'2K PU Wood Finish','2K PU Wood Finish & Coatings','category/2k-pu-wood-finish.png',NULL,NULL,NULL,8,'Active','2026-11-02 01:15:25','2026-11-02 01:15:25'),
(16,NULL,1,'Exterior Emulsion Water Based','Exterior Emulsion Water Based & Coatings','category/exterior-emulsion-water-based.png',NULL,NULL,NULL,9,'Active','2026-11-02 01:15:25','2026-11-02 01:15:25'),
(17,NULL,1,'Distemper Bucket','Distemper Bucket & Coatings','category/distemper-bucket.png',NULL,NULL,NULL,10,'Active','2026-11-02 01:15:25','2026-11-02 01:15:25'),
(18,NULL,1,'Interior Emulsion Water Based','Interior Emulsion Water Based & Coatings','category/interior-emulsion-water-based.png',NULL,NULL,NULL,11,'Active','2026-11-02 01:15:26','2026-11-02 01:15:26'),
(19,NULL,1,'Fast Drying Enamel','Fast Drying Enamel & Coatings','category/fast-drying-enamel.png',NULL,NULL,NULL,12,'Active','2026-11-02 01:15:27','2026-11-02 01:15:27'),
(20,NULL,1,'NC Paints','NC Paints & Coatings','category/nc-paints.png',NULL,NULL,NULL,13,'Active','2026-11-02 01:15:27','2026-11-02 01:15:27'),
(21,NULL,1,'Wood Finishes','Wood Finishes & Coatings','category/wood-finishes.png',NULL,NULL,NULL,14,'Active','2026-11-02 01:15:27','2026-11-02 01:15:27'),
(22,NULL,1,'Synthetic Enamel Oil Paint','Synthetic Enamel Oil Paint & Coatings','category/synthetic-enamel-oil-paint.png',NULL,NULL,NULL,15,'Active','2026-11-02 01:15:28','2026-11-02 01:15:28'),
(23,NULL,1,'Oil Based Primer','Oil Based Primer & Coatings','category/oil-based-primer.png',NULL,NULL,NULL,16,'Active','2026-11-02 01:15:28','2026-11-02 01:15:28'),
(24,NULL,1,'Furniture Enamel','Furniture Enamel & Coatings','category/furniture-enamel.png',NULL,NULL,NULL,17,'Active','2026-11-02 01:15:29','2026-11-02 01:15:29'),
(25,NULL,1,'Polyurethane Coating PU','Polyurethane Coating PU & Coatings','category/polyurethane-coating-pu.png',NULL,NULL,NULL,18,'Active','2026-11-02 01:15:29','2026-11-02 01:15:29'),
(26,NULL,1,'Hammertone Finish','Hammertone Finish & Coatings','category/hammertone-finish.png',NULL,NULL,NULL,19,'Active','2026-11-02 01:15:29','2026-11-02 01:15:29'),
(27,NULL,1,'Bases','Bases & Coatings','category/bases.png',NULL,NULL,NULL,20,'Active','2026-11-02 01:15:30','2026-11-02 01:15:30'),
(28,NULL,1,'Floor Cleaner','Floor Cleaner & Coatings','category/floor-cleaner.png',NULL,NULL,NULL,23,'Active','2026-11-02 01:15:31','2026-11-02 01:15:31');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `maincategory_id` bigint(20) unsigned DEFAULT NULL,
  `gst` varchar(255) DEFAULT NULL,
  `crn` varchar(255) DEFAULT NULL,
  `minordervalue` decimal(12,2) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `comission` decimal(8,2) DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES
(1,'ApniFactory Prime Mills',3,'seller@apnifactory.local','9876543210',1,'07AAAAA0000A1Z5','CRN123456',1000.00,NULL,'New Delhi','Delhi','110001',5.00,'Active','2026-08-24 04:33:34','2026-11-02 01:15:31'),
(2,'Apex Coatings & Paints Ltd',10,'apex.coatings@apnifactory.local','9811223344',1,'07BBPPA1234F1Z1','CRN100000',2000.00,NULL,'New Delhi','Delhi','110001',5.00,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(3,'Surat Silk & Cotton Mills',11,'surat.textiles@apnifactory.local','9822334455',2,'24CCPPB5678G1Z2','CRN100001',1500.00,NULL,'Surat','Gujarat','395001',5.00,'Active','2026-08-24 04:33:35','2026-08-24 04:33:35'),
(4,'MasterCraft Hardware Mart',12,'mastercraft.tools@apnifactory.local','9833445566',4,'27DDPPC9012H1Z3','CRN100002',800.00,NULL,'Mumbai','Maharashtra','400001',5.00,'Active','2026-08-24 04:33:35','2026-08-24 04:33:35'),
(5,'Premier Garments & Apparels',13,'premier.garments@apnifactory.local','9844556677',3,'29EEPPD3456I1Z4','CRN100003',1200.00,NULL,'Bengaluru','Karnataka','560001',5.00,'Active','2026-08-24 04:33:35','2026-08-24 04:33:35'),
(6,'UltraTech Industrial Coatings Pvt Ltd',15,'orders@ultratechcoatings.local','9876543210',1,'27AAACU9603R1ZM','U24200MH2024PTC123456',2500.00,'company/ultratech_logo.png','Mumbai','Maharashtra','400001',8.00,'Active','2026-10-31 01:49:00','2026-10-31 01:49:00'),
(7,'Autocoats Industrial Finishes Ltd',16,'autocoats@apnifactory.local','9811223388',1,'07AAAAA8521A1Z5',NULL,1000.00,NULL,NULL,NULL,NULL,5.00,'Active','2026-11-02 01:15:31','2026-11-02 01:15:31'),
(8,'Shrirang Automotive Paints Ltd',17,'shrirang@apnifactory.local','9822334499',1,'07AAAAA3811A1Z5',NULL,1000.00,NULL,NULL,NULL,NULL,5.00,'Active','2026-11-02 01:15:31','2026-11-02 01:15:31');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `company_audit_logs`
--

DROP TABLE IF EXISTS `company_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `actor_name` varchar(255) DEFAULT NULL,
  `actor_role` varchar(255) NOT NULL DEFAULT 'seller',
  `action_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `company_audit_logs_company_id_index` (`company_id`),
  KEY `company_audit_logs_user_id_index` (`user_id`),
  KEY `company_audit_logs_action_type_index` (`action_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_audit_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `company_audit_logs` WRITE;
/*!40000 ALTER TABLE `company_audit_logs` DISABLE KEYS */;
INSERT INTO `company_audit_logs` VALUES
(1,1,3,'Demo Seller','seller','category_price_adjustment','Category Bulk Adjustment: Enamel Paints','Applied +5% (Percentage) across 1 products (12 SKUs) in Enamel Paints.','{\"old_total\":43580}','{\"new_total\":45759}','127.0.0.1','2026-08-24 04:49:26','2026-08-24 04:49:26'),
(2,1,3,'Demo Seller','seller','min_order_value_change','Minimum Order Value Updated (Test)','Threshold changed from ₹1,000 to ₹1,500 for testing','{\"old_min\":1000}','{\"new_min\":1500}','127.0.0.1','2026-08-24 04:49:26','2026-08-24 04:49:26'),
(3,1,3,'Demo Seller','seller','price_adjustment','Price Adjustment: Royale Luxury Interior Emulsion','Applied ₹1.04 (Per litre) across 3 SKUs.','{\"old_total\":1690}','{\"new_total\":1693.12}','127.0.0.1','2026-09-08 05:44:08','2026-09-08 05:44:08');
/*!40000 ALTER TABLE `company_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `expiry` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `couponon` varchar(255) DEFAULT NULL,
  `couponapplyon` varchar(255) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `address_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `landmark1` varchar(255) DEFAULT NULL,
  `landmark2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `phoneno` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `identityname` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'transport',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_addresses`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
INSERT INTO `customer_addresses` VALUES
(1,NULL,1,'Demo Customer','Plot No. 42, Connaught Place','Near Central Park','New Delhi','Delhi','110001','India','9876543210','28.6315, 77.2167','Home','transport','2026-10-30 23:29:41','2026-10-30 23:29:41'),
(2,NULL,4,'Apni Factory User','Sector 18, Commercial Zone','Next to Metro Station','New Delhi','Delhi','110001','India','9999999999','28.6139, 77.2090','Office','transport','2026-10-30 23:29:41','2026-10-30 23:29:41');
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `lastlogin` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `type` enum('user','vendor') NOT NULL DEFAULT 'user',
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `deviceid` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `followers` int(11) NOT NULL DEFAULT 0,
  `followings` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `regby` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_email_unique` (`email`),
  UNIQUE KEY `customers_mobile_unique` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES
(1,'Demo Customer','customer@apnifactory.local','9876543210','2026-11-03 04:41:29','$2y$10$uu.4EqFA4c8maqqUD1RqN.wUJNXZZrwfpNvuGaN8Qo/Ulc9ddEleO','user','active','de_YYJ37Tq27hwR-yNwzp6:APA91bEtkY21O0DCUxfkrFF9c0LgRH86_fSIpDyrxlcBvUgIhAniH6M1jiZ2tDTY3ds0bPt4Ps2EH-bOkVWZLfbzzZQCqjCjU3PqttdkfXWtOSh3uHBheMI',NULL,0,0,NULL,NULL,NULL,NULL,'2026-08-22 03:38:24','2026-11-03 04:41:29'),
(2,'Audit Tester','audit_test_1787564783@apnifactory.local','9929947690',NULL,'$2y$10$J73ckrq/znOwfaXhuKxwD.g30Rg6zzkn.n80tPEzcODnv6huBL.7q','user','active',NULL,NULL,0,0,NULL,NULL,'app',NULL,'2026-08-24 04:16:23','2026-08-24 04:16:23'),
(4,'Apni Factory User','demo@apnifactory.local','9999999999','2026-10-30 23:31:35','$2y$10$ML0/CV3qtmIYdjfEL3BFgu3T3OZhEIp19GABsGi9pFukIcAnEmYtK','user','active','','New Delhi, Delhi',0,0,NULL,NULL,NULL,NULL,'2026-10-30 23:29:41','2026-10-30 23:31:35');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES
(1,'How do I track my order?','You can track your order in real-time from the My Orders section in your profile.',1,'Active','2026-10-31 06:18:39','2026-10-31 06:18:39'),
(2,'What payment methods are supported?','We support UPI, Net Banking, Credit/Debit cards, and Cash on Delivery for eligible locations.',1,'Active','2026-10-31 06:18:39','2026-10-31 06:18:39'),
(3,'How does wholesale factory pricing work?','Apni Factory connects you directly to verified manufacturers for bulk and factory rates with transparent volume slabs.',1,'Active','2026-10-31 06:18:39','2026-10-31 06:18:39');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cusid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `feedback` varchar(255) NOT NULL,
  `status` enum('Active','Deactive') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `images`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `images` WRITE;
/*!40000 ALTER TABLE `images` DISABLE KEYS */;
/*!40000 ALTER TABLE `images` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `india_pincode`
--

DROP TABLE IF EXISTS `india_pincode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `india_pincode` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pincode` varchar(20) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'India',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_india_pincode_pincode` (`pincode`),
  KEY `idx_india_pincode_state` (`state`),
  KEY `idx_india_pincode_city` (`city`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `india_pincode`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `india_pincode` WRITE;
/*!40000 ALTER TABLE `india_pincode` DISABLE KEYS */;
INSERT INTO `india_pincode` VALUES
(1,'110001','Delhi','New Delhi','Central Delhi','India','2026-08-22 03:38:23','2026-08-22 03:38:23'),
(2,'400001','Maharashtra','Mumbai','Mumbai','India','2026-08-22 03:38:23','2026-08-22 03:38:23'),
(3,'560001','Karnataka','Bengaluru','Bangalore','India','2026-08-22 03:38:23','2026-08-22 03:38:23');
/*!40000 ALTER TABLE `india_pincode` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `logsofpages`
--

DROP TABLE IF EXISTS `logsofpages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logsofpages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `updateon` varchar(255) DEFAULT NULL,
  `pagename` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logsofpages`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `logsofpages` WRITE;
/*!40000 ALTER TABLE `logsofpages` DISABLE KEYS */;
/*!40000 ALTER TABLE `logsofpages` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `main_categories`
--

DROP TABLE IF EXISTS `main_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `main_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_categories_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `main_categories` WRITE;
/*!40000 ALTER TABLE `main_categories` DISABLE KEYS */;
INSERT INTO `main_categories` VALUES
(1,'paints','Paints & Coatings','default.png',NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(2,'fabrics','Fabrics','default.png',NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(3,'garments','Garments','default.png',NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(4,'accessories','Accessories','default.png',NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34');
/*!40000 ALTER TABLE `main_categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'2014_10_12_000000_create_users_table',1),
(2,'2014_10_12_100000_create_password_resets_table',1),
(3,'2019_08_19_000000_create_failed_jobs_table',1),
(4,'2019_12_14_000001_create_personal_access_tokens_table',1),
(5,'2024_01_19_143753_customers',1),
(6,'2024_01_19_174429_create_sliders_table',1),
(7,'2024_01_19_174706_create_main_categories_table',1),
(8,'2024_01_19_174755_create_categories_table',1),
(9,'2024_01_19_174900_create_sub_categories_table',1),
(10,'2024_01_31_195232_create_products_table',1),
(11,'2024_02_01_191641_create_pages_table',1),
(12,'2024_02_01_191743_create_faqs_table',1),
(13,'2024_02_01_191822_create_feedback_table',1),
(14,'2024_02_27_053007_create_images_table',1),
(15,'2024_02_27_053040_create_product_attributes_table',1),
(16,'2024_02_27_182809_create_customer_addresses_table',1),
(17,'2024_02_27_182933_create_coupons_table',1),
(18,'2024_03_16_035318_create_sizes_table',1),
(19,'2024_03_16_035343_create_box_packings_table',1),
(20,'2024_03_16_043031_create_brands_table',1),
(21,'2024_07_30_152243_create_companies_table',1),
(22,'2024_07_30_152347_create_shade_cards_table',1),
(23,'2024_07_31_030945_create_productshades_table',1),
(24,'2024_11_13_145640_admin',1),
(25,'2024_11_13_150016_create_admins_table',1),
(26,'2024_11_20_095912_create_advertisements_table',1),
(27,'2024_11_20_142402_create_tickets_table',1),
(28,'2024_11_20_142511_create_bank_details_table',1),
(29,'2024_12_26_075400_create_product_reviews_table',1),
(30,'2025_01_03_120514_create_orders_table',1),
(31,'2025_06_07_134946_create_profiles_table',1),
(32,'2025_12_16_141323_create_whatsappmsgs_table',1),
(33,'2026_01_01_000001_create_transections_table',1),
(34,'2026_01_01_000002_create_wallet_table',1),
(35,'2026_01_01_000003_create_order_status_table',1),
(36,'2026_01_01_000004_create_order_tracks_table',1),
(37,'2026_01_01_000005_create_orderdetail_table',1),
(38,'2026_01_01_000006_create_cart_table',1),
(39,'2026_01_01_000007_create_cartattribute_table',1),
(40,'2026_01_01_000008_create_wishlist_table',1),
(41,'2026_01_01_000009_create_tbl_otp_table',1),
(42,'2026_01_01_000010_create_logsofpages_table',1),
(43,'2026_01_01_000011_create_notifications_table',1),
(44,'2026_01_01_000012_create_india_pincode_table',1),
(45,'2026_01_02_000001_add_seller_pricing_to_product_attributes_table',1),
(46,'2026_01_02_000002_create_paint_price_adjustments_table',1),
(47,'2026_08_24_101313_create_company_audit_logs_table',2),
(48,'2026_10_27_100000_add_performance_indexes_to_core_tables',3),
(49,'2026_10_27_110000_create_v2_rfq_and_slab_pricing_tables',4),
(50,'2026_10_31_170000_add_compound_performance_indexes',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `msg` text DEFAULT NULL,
  `msgread` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `deep_link` varchar(500) DEFAULT NULL,
  `screen` varchar(100) DEFAULT NULL,
  `screenid` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_id` (`user_id`),
  KEY `idx_notif_cust_read` (`customer_id`,`msgread`),
  KEY `idx_notif_user_read` (`user_id`,`msgread`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES
(1,1,1,'Order #TEST-EXP-1787562985 has been auto-cancelled because no response was provided within the 72-hour window. Repeated expirations affect seller rating.',1,'Order #TEST-EXP-1787562985 Expired Due to Inactivity','seller','2026-08-24 03:46:25','2026-08-24 03:58:46',NULL,NULL,NULL,NULL),
(2,1,1,'Order #TEST-EXP-1787563107 has been auto-cancelled because no response was provided within the 72-hour window. Repeated expirations affect seller rating.',1,'Order #TEST-EXP-1787563107 Expired Due to Inactivity','seller','2026-08-24 03:48:27','2026-08-24 03:58:46',NULL,NULL,NULL,NULL),
(3,1,NULL,'Your order has been safely delivered.',0,'Order Delivered - ApniFactory','customer','2026-08-24 05:17:33','2026-08-24 05:17:33',NULL,NULL,NULL,NULL),
(4,1,NULL,'Your order has been safely delivered.',0,'Order Delivered - ApniFactory','customer','2026-08-24 05:18:02','2026-08-24 05:18:02',NULL,NULL,NULL,NULL),
(5,1,NULL,'Your order has been safely delivered.',0,'Order Delivered - ApniFactory','customer','2026-08-24 05:18:50','2026-08-24 05:18:50',NULL,NULL,NULL,NULL),
(6,1,NULL,'Your order has been safely delivered.',0,'Order Delivered - ApniFactory','customer','2026-08-24 05:19:12','2026-08-24 05:19:12',NULL,NULL,NULL,NULL),
(7,1,NULL,'Your order has been safely delivered.',0,'Order Delivered - ApniFactory','customer','2026-08-24 05:19:59','2026-08-24 05:19:59',NULL,NULL,NULL,NULL),
(8,1,NULL,'Your refund of ₹500.00 has been successfully credited to your account/wallet. Reason: Automated Test Refund SLA Expired',0,'💰 Refund Processed for Order #AF20260822-001','customer','2026-08-24 05:19:59','2026-08-24 05:19:59',NULL,NULL,NULL,NULL),
(9,1,NULL,'Your order has been safely delivered.',0,'Order Delivered - ApniFactory','customer','2026-08-24 05:20:28','2026-08-24 05:20:28',NULL,NULL,NULL,NULL),
(10,1,NULL,'Your refund of ₹500.00 has been successfully credited to your account/wallet. Reason: Automated Test Refund SLA Expired',0,'💰 Refund Processed for Order #AF20260822-001','customer','2026-08-24 05:20:28','2026-08-24 05:20:28',NULL,NULL,NULL,NULL),
(11,1,NULL,'Your order has been safely delivered.',0,'Order Delivered - ApniFactory','customer','2026-08-24 05:20:47','2026-08-24 05:20:47',NULL,NULL,NULL,NULL),
(12,1,NULL,'Your refund of ₹500.00 has been successfully credited to your account/wallet. Reason: Automated Test Refund SLA Expired',0,'💰 Refund Processed for Order #AF20260822-001','customer','2026-08-24 05:20:47','2026-08-24 05:20:47',NULL,NULL,NULL,NULL),
(13,1,NULL,'Your order has been safely delivered.',0,'Order Delivered - ApniFactory','customer','2026-09-08 05:38:56','2026-09-08 05:38:56',NULL,NULL,NULL,NULL),
(14,1,NULL,'Your refund of ₹500.00 has been successfully credited to your account/wallet. Reason: Automated Test Refund SLA Expired',0,'💰 Refund Processed for Order #AF20260822-001','customer','2026-09-08 05:38:57','2026-09-08 05:38:57',NULL,NULL,NULL,NULL),
(15,NULL,NULL,'Exclusive 35% discount on all Acrylic Emulsions and Primers today only.',0,'🎉 Factory Direct Flash Sale!','broadcast','2026-11-03 03:08:29','2026-11-03 03:08:29','slider/banner.jpg',NULL,'product','30');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `order_status`
--

DROP TABLE IF EXISTS `order_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `order_no` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `msg` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_status_order_no` (`order_no`),
  KEY `idx_order_status_status` (`status`),
  KEY `idx_order_status_no_id` (`order_no`,`id`),
  KEY `idx_order_status_no_status` (`order_no`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `order_status` WRITE;
/*!40000 ALTER TABLE `order_status` DISABLE KEYS */;
INSERT INTO `order_status` VALUES
(1,1,'AF20260822-001',1,'pending','Order placed successfully','2026-08-22 03:38:24','2026-08-22 03:38:24'),
(2,2,'TEST-EXP-1787562916',1,'pending','Wait For Confirmation','2026-08-20 19:45:16','2026-08-20 19:45:16'),
(3,2,'TEST-EXP-1787562916',1,'Cancelled','Order automatically cancelled by system: Seller response deadline (72 hours) exceeded.','2026-08-24 03:45:16','2026-08-24 03:45:16'),
(4,3,'TEST-EXP-1787562985',1,'pending','Wait For Confirmation','2026-08-20 19:46:25','2026-08-20 19:46:25'),
(5,3,'TEST-EXP-1787562985',1,'Cancelled','Order automatically cancelled by system: Seller response deadline (72 hours) exceeded.','2026-08-24 03:46:25','2026-08-24 03:46:25'),
(8,1,'AF20260822-001',1,'Cancelled','Order cancelled & refunded: Automated Test Refund SLA Expired','2026-08-24 05:19:59','2026-08-24 05:19:59'),
(9,1,'AF20260822-001',1,'Cancelled','Order cancelled & refunded: Automated Test Refund SLA Expired','2026-08-24 05:20:28','2026-08-24 05:20:28'),
(10,1,'AF20260822-001',1,'Cancelled','Order cancelled & refunded: Automated Test Refund SLA Expired','2026-08-24 05:20:47','2026-08-24 05:20:47'),
(11,1,'AF20260822-001',1,'Cancelled','Order cancelled & refunded: Automated Test Refund SLA Expired','2026-09-08 05:38:57','2026-09-08 05:38:57');
/*!40000 ALTER TABLE `order_status` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `order_tracks`
--

DROP TABLE IF EXISTS `order_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_tracks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `orderno` varchar(255) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `transcontact` varchar(255) DEFAULT NULL,
  `lrno` varchar(255) DEFAULT NULL,
  `invoiceno` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `creditamnt` decimal(12,2) NOT NULL DEFAULT 0.00,
  `billty` varchar(255) DEFAULT NULL,
  `invoice` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_tracks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `order_tracks` WRITE;
/*!40000 ALTER TABLE `order_tracks` DISABLE KEYS */;
INSERT INTO `order_tracks` VALUES
(1,2,'TEST-EXP-1787562916','Order automatically cancelled by system: Seller response deadline (72 hours) exceeded.',NULL,NULL,NULL,'0',0.00,NULL,NULL,'2026-08-24 03:45:16','2026-08-24 03:45:16'),
(2,3,'TEST-EXP-1787562985','Order automatically cancelled by system: Seller response deadline (72 hours) exceeded.',NULL,NULL,NULL,'0',0.00,NULL,NULL,'2026-08-24 03:46:25','2026-08-24 03:46:25'),
(4,1,'AF20260822-001','Order cancelled & refunded: Automated Test Refund SLA Expired',NULL,NULL,NULL,'0',0.00,NULL,NULL,'2026-09-08 05:38:57','2026-09-08 05:38:57');
/*!40000 ALTER TABLE `order_tracks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `orderdetail`
--

DROP TABLE IF EXISTS `orderdetail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orderdetail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `orderno` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `productname` varchar(255) DEFAULT NULL,
  `hsn` varchar(255) DEFAULT NULL,
  `brdcmpcat` varchar(255) DEFAULT NULL,
  `attribute` longtext DEFAULT NULL,
  `coupondetail` text DEFAULT NULL,
  `productimage` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_orderdetail_order_id` (`order_id`),
  KEY `idx_orderdetail_product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orderdetail`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `orderdetail` WRITE;
/*!40000 ALTER TABLE `orderdetail` DISABLE KEYS */;
INSERT INTO `orderdetail` VALUES
(1,1,'AF20260822-001',1,1,'Pure Cotton Fabric','5208','FactoryStandard / ApniFactory Prime Mills / Fabrics','[{\"color\":\"White\",\"qty\":10,\"price\":250}]',NULL,'default.png','2026-08-22 03:38:24','2026-08-22 03:38:24');
/*!40000 ALTER TABLE `orderdetail` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `orderno` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `address` text DEFAULT NULL,
  `sellercouponamount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `admincouponamount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `admincoupondetail` text DEFAULT NULL,
  `netamount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `taxdetail` longtext DEFAULT NULL,
  `taxamount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grandtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_orderno_unique` (`orderno`),
  KEY `idx_orders_user_id` (`user_id`),
  KEY `idx_orders_orderno` (`orderno`),
  KEY `idx_orders_created_at` (`created_at`),
  KEY `idx_orders_customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,'AF20260822-001',1,1,'123 Test Industrial Area, New Delhi, 110001',0.00,0.00,NULL,2500.00,'[{\"name\":\"GST @18%\",\"value\":450}]',450.00,2950.00,'2026-08-22 03:38:24','2026-08-22 03:38:24'),
(2,'TEST-EXP-1787562916',2,1,'{\"name\":\"Test User\",\"city\":\"Delhi\",\"pincode\":\"110001\",\"state\":\"Delhi\",\"country\":\"India\"}',0.00,0.00,NULL,1500.00,NULL,270.00,1770.00,'2026-08-20 19:45:16','2026-08-20 19:45:16'),
(3,'TEST-EXP-1787562985',2,1,'{\"name\":\"Test User\",\"city\":\"Delhi\",\"pincode\":\"110001\",\"state\":\"Delhi\",\"country\":\"India\"}',0.00,0.00,NULL,1500.00,NULL,270.00,1770.00,'2026-08-20 19:46:25','2026-08-20 19:46:25');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES
(1,'About Us','about-us','<p>Welcome to Apni Factory - Your trusted B2B factory direct marketplace connecting verified manufacturers and buyers.</p>',1,'Active','2026-10-31 05:34:54','2026-10-31 05:34:54'),
(2,'Privacy Policy','privacy-policy','<p>Your privacy and data security are paramount at Apni Factory.</p>',1,'Active','2026-10-31 05:34:54','2026-10-31 05:34:54'),
(3,'Terms & Conditions','terms-and-conditions','<p>Terms and conditions governing orders and payments on Apni Factory.</p>',1,'Active','2026-10-31 05:34:54','2026-10-31 05:34:54'),
(4,'Return Policy','return-policy','<p>Our streamlined 7-day verified return policy for verified buyers.</p>',1,'Active','2026-10-31 05:34:54','2026-10-31 05:34:54');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `paint_price_adjustments`
--

DROP TABLE IF EXISTS `paint_price_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `paint_price_adjustments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `adjustment_type` varchar(255) NOT NULL,
  `adjustment_value` decimal(10,2) NOT NULL,
  `scope_type` varchar(255) NOT NULL DEFAULT 'family',
  `scope_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scope_json`)),
  `affected_count` int(11) NOT NULL DEFAULT 0,
  `preview_data` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paint_price_adjustments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `paint_price_adjustments` WRITE;
/*!40000 ALTER TABLE `paint_price_adjustments` DISABLE KEYS */;
INSERT INTO `paint_price_adjustments` VALUES
(1,1,4,'per_litre',1.00,'family','{\"type\":\"family\"}',16,'[{\"sku_id\":1,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":400,\"new_seller_price\":401,\"adjustment_delta\":1,\"old_customer_price\":500,\"new_customer_price\":421.05,\"commission_rate\":5},{\"sku_id\":2,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1600,\"new_seller_price\":1604,\"adjustment_delta\":4,\"old_customer_price\":2000,\"new_customer_price\":1684.2,\"commission_rate\":5},{\"sku_id\":3,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":3800,\"new_seller_price\":3810,\"adjustment_delta\":10,\"old_customer_price\":4750,\"new_customer_price\":4000.5,\"commission_rate\":5},{\"sku_id\":4,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7400,\"new_seller_price\":7420,\"adjustment_delta\":20,\"old_customer_price\":9250,\"new_customer_price\":7791,\"commission_rate\":5},{\"sku_id\":5,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":450,\"new_seller_price\":451,\"adjustment_delta\":1,\"old_customer_price\":562.5,\"new_customer_price\":473.55,\"commission_rate\":5},{\"sku_id\":6,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1800,\"new_seller_price\":1804,\"adjustment_delta\":4,\"old_customer_price\":2250,\"new_customer_price\":1894.2,\"commission_rate\":5},{\"sku_id\":7,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4300,\"new_seller_price\":4310,\"adjustment_delta\":10,\"old_customer_price\":5375,\"new_customer_price\":4525.5,\"commission_rate\":5},{\"sku_id\":8,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8400,\"new_seller_price\":8420,\"adjustment_delta\":20,\"old_customer_price\":10500,\"new_customer_price\":8841,\"commission_rate\":5},{\"sku_id\":9,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":420,\"new_seller_price\":421,\"adjustment_delta\":1,\"old_customer_price\":525,\"new_customer_price\":442.05,\"commission_rate\":5},{\"sku_id\":10,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1680,\"new_seller_price\":1684,\"adjustment_delta\":4,\"old_customer_price\":2100,\"new_customer_price\":1768.2,\"commission_rate\":5},{\"sku_id\":11,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4000,\"new_seller_price\":4010,\"adjustment_delta\":10,\"old_customer_price\":5000,\"new_customer_price\":4210.5,\"commission_rate\":5},{\"sku_id\":12,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7800,\"new_seller_price\":7820,\"adjustment_delta\":20,\"old_customer_price\":9750,\"new_customer_price\":8211,\"commission_rate\":5},{\"sku_id\":13,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":410,\"new_seller_price\":411,\"adjustment_delta\":1,\"old_customer_price\":512.5,\"new_customer_price\":431.55,\"commission_rate\":5},{\"sku_id\":14,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1640,\"new_seller_price\":1644,\"adjustment_delta\":4,\"old_customer_price\":2050,\"new_customer_price\":1726.2,\"commission_rate\":5},{\"sku_id\":15,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":3900,\"new_seller_price\":3910,\"adjustment_delta\":10,\"old_customer_price\":4875,\"new_customer_price\":4105.5,\"commission_rate\":5},{\"sku_id\":16,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7600,\"new_seller_price\":7620,\"adjustment_delta\":20,\"old_customer_price\":9500,\"new_customer_price\":8001,\"commission_rate\":5}]',1,'2026-08-22 03:39:04','2026-08-22 03:39:04'),
(2,1,4,'fixed',50.00,'shades','{\"type\":\"shades\",\"shades\":[2]}',4,'[{\"sku_id\":5,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":451,\"new_seller_price\":501,\"adjustment_delta\":50,\"old_customer_price\":473.55,\"new_customer_price\":526.05,\"commission_rate\":5},{\"sku_id\":6,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1804,\"new_seller_price\":1854,\"adjustment_delta\":50,\"old_customer_price\":1894.2,\"new_customer_price\":1946.7,\"commission_rate\":5},{\"sku_id\":7,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4310,\"new_seller_price\":4360,\"adjustment_delta\":50,\"old_customer_price\":4525.5,\"new_customer_price\":4578,\"commission_rate\":5},{\"sku_id\":8,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8420,\"new_seller_price\":8470,\"adjustment_delta\":50,\"old_customer_price\":8841,\"new_customer_price\":8893.5,\"commission_rate\":5}]',1,'2026-08-22 03:39:04','2026-08-22 03:39:04'),
(3,1,4,'percentage',2.00,'shades','{\"type\":\"shades\",\"shades\":[1]}',4,'[{\"sku_id\":1,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":401,\"new_seller_price\":409.02,\"adjustment_delta\":8.02,\"old_customer_price\":421.05,\"new_customer_price\":429.47,\"commission_rate\":5},{\"sku_id\":2,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1604,\"new_seller_price\":1636.08,\"adjustment_delta\":32.08,\"old_customer_price\":1684.2,\"new_customer_price\":1717.88,\"commission_rate\":5},{\"sku_id\":3,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":3810,\"new_seller_price\":3886.2,\"adjustment_delta\":76.2,\"old_customer_price\":4000.5,\"new_customer_price\":4080.51,\"commission_rate\":5},{\"sku_id\":4,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7420,\"new_seller_price\":7568.4,\"adjustment_delta\":148.4,\"old_customer_price\":7791,\"new_customer_price\":7946.82,\"commission_rate\":5}]',1,'2026-08-22 03:39:04','2026-08-22 03:39:04'),
(4,1,4,'per_litre',12.04,'family','{\"type\":\"family\",\"shades\":[],\"packings\":[],\"skus\":[]}',16,'[{\"sku_id\":1,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":409.02,\"new_seller_price\":421.06,\"adjustment_delta\":12.04,\"old_customer_price\":429.47,\"new_customer_price\":442.11,\"commission_rate\":5},{\"sku_id\":2,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1636.08,\"new_seller_price\":1684.24,\"adjustment_delta\":48.16,\"old_customer_price\":1717.88,\"new_customer_price\":1768.45,\"commission_rate\":5},{\"sku_id\":3,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":3886.2,\"new_seller_price\":4006.6,\"adjustment_delta\":120.4,\"old_customer_price\":4080.51,\"new_customer_price\":4206.93,\"commission_rate\":5},{\"sku_id\":4,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7568.4,\"new_seller_price\":7809.2,\"adjustment_delta\":240.8,\"old_customer_price\":7946.82,\"new_customer_price\":8199.66,\"commission_rate\":5},{\"sku_id\":5,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":501,\"new_seller_price\":513.04,\"adjustment_delta\":12.04,\"old_customer_price\":526.05,\"new_customer_price\":538.69,\"commission_rate\":5},{\"sku_id\":6,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1854,\"new_seller_price\":1902.16,\"adjustment_delta\":48.16,\"old_customer_price\":1946.7,\"new_customer_price\":1997.27,\"commission_rate\":5},{\"sku_id\":7,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4360,\"new_seller_price\":4480.4,\"adjustment_delta\":120.4,\"old_customer_price\":4578,\"new_customer_price\":4704.42,\"commission_rate\":5},{\"sku_id\":8,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8470,\"new_seller_price\":8710.8,\"adjustment_delta\":240.8,\"old_customer_price\":8893.5,\"new_customer_price\":9146.34,\"commission_rate\":5},{\"sku_id\":9,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":421,\"new_seller_price\":433.04,\"adjustment_delta\":12.04,\"old_customer_price\":442.05,\"new_customer_price\":454.69,\"commission_rate\":5},{\"sku_id\":10,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1684,\"new_seller_price\":1732.16,\"adjustment_delta\":48.16,\"old_customer_price\":1768.2,\"new_customer_price\":1818.77,\"commission_rate\":5},{\"sku_id\":11,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4010,\"new_seller_price\":4130.4,\"adjustment_delta\":120.4,\"old_customer_price\":4210.5,\"new_customer_price\":4336.92,\"commission_rate\":5},{\"sku_id\":12,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7820,\"new_seller_price\":8060.8,\"adjustment_delta\":240.8,\"old_customer_price\":8211,\"new_customer_price\":8463.84,\"commission_rate\":5},{\"sku_id\":13,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":411,\"new_seller_price\":423.04,\"adjustment_delta\":12.04,\"old_customer_price\":431.55,\"new_customer_price\":444.19,\"commission_rate\":5},{\"sku_id\":14,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1644,\"new_seller_price\":1692.16,\"adjustment_delta\":48.16,\"old_customer_price\":1726.2,\"new_customer_price\":1776.77,\"commission_rate\":5},{\"sku_id\":15,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":3910,\"new_seller_price\":4030.4,\"adjustment_delta\":120.4,\"old_customer_price\":4105.5,\"new_customer_price\":4231.92,\"commission_rate\":5},{\"sku_id\":16,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7620,\"new_seller_price\":7860.8,\"adjustment_delta\":240.8,\"old_customer_price\":8001,\"new_customer_price\":8253.84,\"commission_rate\":5}]',1,'2026-08-22 03:41:56','2026-08-22 03:41:56'),
(5,1,4,'per_litre',12.04,'family','{\"type\":\"family\",\"shades\":[],\"packings\":[],\"skus\":[]}',16,'[{\"sku_id\":1,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":421.06,\"new_seller_price\":433.1,\"adjustment_delta\":12.04,\"old_customer_price\":442.11,\"new_customer_price\":454.76,\"commission_rate\":5},{\"sku_id\":2,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1684.24,\"new_seller_price\":1732.4,\"adjustment_delta\":48.16,\"old_customer_price\":1768.45,\"new_customer_price\":1819.02,\"commission_rate\":5},{\"sku_id\":3,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4006.6,\"new_seller_price\":4127,\"adjustment_delta\":120.4,\"old_customer_price\":4206.93,\"new_customer_price\":4333.35,\"commission_rate\":5},{\"sku_id\":4,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7809.2,\"new_seller_price\":8050,\"adjustment_delta\":240.8,\"old_customer_price\":8199.66,\"new_customer_price\":8452.5,\"commission_rate\":5},{\"sku_id\":5,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":513.04,\"new_seller_price\":525.08,\"adjustment_delta\":12.04,\"old_customer_price\":538.69,\"new_customer_price\":551.33,\"commission_rate\":5},{\"sku_id\":6,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1902.16,\"new_seller_price\":1950.32,\"adjustment_delta\":48.16,\"old_customer_price\":1997.27,\"new_customer_price\":2047.84,\"commission_rate\":5},{\"sku_id\":7,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4480.4,\"new_seller_price\":4600.8,\"adjustment_delta\":120.4,\"old_customer_price\":4704.42,\"new_customer_price\":4830.84,\"commission_rate\":5},{\"sku_id\":8,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8710.8,\"new_seller_price\":8951.6,\"adjustment_delta\":240.8,\"old_customer_price\":9146.34,\"new_customer_price\":9399.18,\"commission_rate\":5},{\"sku_id\":9,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":433.04,\"new_seller_price\":445.08,\"adjustment_delta\":12.04,\"old_customer_price\":454.69,\"new_customer_price\":467.33,\"commission_rate\":5},{\"sku_id\":10,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1732.16,\"new_seller_price\":1780.32,\"adjustment_delta\":48.16,\"old_customer_price\":1818.77,\"new_customer_price\":1869.34,\"commission_rate\":5},{\"sku_id\":11,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4130.4,\"new_seller_price\":4250.8,\"adjustment_delta\":120.4,\"old_customer_price\":4336.92,\"new_customer_price\":4463.34,\"commission_rate\":5},{\"sku_id\":12,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8060.8,\"new_seller_price\":8301.6,\"adjustment_delta\":240.8,\"old_customer_price\":8463.84,\"new_customer_price\":8716.68,\"commission_rate\":5},{\"sku_id\":13,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":423.04,\"new_seller_price\":435.08,\"adjustment_delta\":12.04,\"old_customer_price\":444.19,\"new_customer_price\":456.83,\"commission_rate\":5},{\"sku_id\":14,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1692.16,\"new_seller_price\":1740.32,\"adjustment_delta\":48.16,\"old_customer_price\":1776.77,\"new_customer_price\":1827.34,\"commission_rate\":5},{\"sku_id\":15,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4030.4,\"new_seller_price\":4150.8,\"adjustment_delta\":120.4,\"old_customer_price\":4231.92,\"new_customer_price\":4358.34,\"commission_rate\":5},{\"sku_id\":16,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7860.8,\"new_seller_price\":8101.6,\"adjustment_delta\":240.8,\"old_customer_price\":8253.84,\"new_customer_price\":8506.68,\"commission_rate\":5}]',1,'2026-08-22 03:42:15','2026-08-22 03:42:15'),
(6,1,4,'per_litre',12.04,'family','{\"type\":\"family\",\"shades\":[],\"packings\":[],\"skus\":[]}',16,'[{\"sku_id\":1,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":433.1,\"new_seller_price\":445.14,\"adjustment_delta\":12.04,\"old_customer_price\":454.76,\"new_customer_price\":467.4,\"commission_rate\":5},{\"sku_id\":2,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1732.4,\"new_seller_price\":1780.56,\"adjustment_delta\":48.16,\"old_customer_price\":1819.02,\"new_customer_price\":1869.59,\"commission_rate\":5},{\"sku_id\":3,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4127,\"new_seller_price\":4247.4,\"adjustment_delta\":120.4,\"old_customer_price\":4333.35,\"new_customer_price\":4459.77,\"commission_rate\":5},{\"sku_id\":4,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8050,\"new_seller_price\":8290.8,\"adjustment_delta\":240.8,\"old_customer_price\":8452.5,\"new_customer_price\":8705.34,\"commission_rate\":5},{\"sku_id\":5,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":525.08,\"new_seller_price\":537.12,\"adjustment_delta\":12.04,\"old_customer_price\":551.33,\"new_customer_price\":563.98,\"commission_rate\":5},{\"sku_id\":6,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1950.32,\"new_seller_price\":1998.48,\"adjustment_delta\":48.16,\"old_customer_price\":2047.84,\"new_customer_price\":2098.4,\"commission_rate\":5},{\"sku_id\":7,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4600.8,\"new_seller_price\":4721.2,\"adjustment_delta\":120.4,\"old_customer_price\":4830.84,\"new_customer_price\":4957.26,\"commission_rate\":5},{\"sku_id\":8,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8951.6,\"new_seller_price\":9192.4,\"adjustment_delta\":240.8,\"old_customer_price\":9399.18,\"new_customer_price\":9652.02,\"commission_rate\":5},{\"sku_id\":9,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":445.08,\"new_seller_price\":457.12,\"adjustment_delta\":12.04,\"old_customer_price\":467.33,\"new_customer_price\":479.98,\"commission_rate\":5},{\"sku_id\":10,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1780.32,\"new_seller_price\":1828.48,\"adjustment_delta\":48.16,\"old_customer_price\":1869.34,\"new_customer_price\":1919.9,\"commission_rate\":5},{\"sku_id\":11,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4250.8,\"new_seller_price\":4371.2,\"adjustment_delta\":120.4,\"old_customer_price\":4463.34,\"new_customer_price\":4589.76,\"commission_rate\":5},{\"sku_id\":12,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8301.6,\"new_seller_price\":8542.4,\"adjustment_delta\":240.8,\"old_customer_price\":8716.68,\"new_customer_price\":8969.52,\"commission_rate\":5},{\"sku_id\":13,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":435.08,\"new_seller_price\":447.12,\"adjustment_delta\":12.04,\"old_customer_price\":456.83,\"new_customer_price\":469.48,\"commission_rate\":5},{\"sku_id\":14,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1740.32,\"new_seller_price\":1788.48,\"adjustment_delta\":48.16,\"old_customer_price\":1827.34,\"new_customer_price\":1877.9,\"commission_rate\":5},{\"sku_id\":15,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4150.8,\"new_seller_price\":4271.2,\"adjustment_delta\":120.4,\"old_customer_price\":4358.34,\"new_customer_price\":4484.76,\"commission_rate\":5},{\"sku_id\":16,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8101.6,\"new_seller_price\":8342.4,\"adjustment_delta\":240.8,\"old_customer_price\":8506.68,\"new_customer_price\":8759.52,\"commission_rate\":5}]',1,'2026-08-22 04:00:58','2026-08-22 04:00:58'),
(7,1,4,'per_litre',12.04,'family','{\"type\":\"family\",\"shades\":[],\"packings\":[],\"skus\":[]}',16,'[{\"sku_id\":1,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":445.14,\"new_seller_price\":457.18,\"adjustment_delta\":12.04,\"old_customer_price\":467.4,\"new_customer_price\":480.04,\"commission_rate\":5},{\"sku_id\":2,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1780.56,\"new_seller_price\":1828.72,\"adjustment_delta\":48.16,\"old_customer_price\":1869.59,\"new_customer_price\":1920.16,\"commission_rate\":5},{\"sku_id\":3,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4247.4,\"new_seller_price\":4367.8,\"adjustment_delta\":120.4,\"old_customer_price\":4459.77,\"new_customer_price\":4586.19,\"commission_rate\":5},{\"sku_id\":4,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8290.8,\"new_seller_price\":8531.6,\"adjustment_delta\":240.8,\"old_customer_price\":8705.34,\"new_customer_price\":8958.18,\"commission_rate\":5},{\"sku_id\":5,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":537.12,\"new_seller_price\":549.16,\"adjustment_delta\":12.04,\"old_customer_price\":563.98,\"new_customer_price\":576.62,\"commission_rate\":5},{\"sku_id\":6,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1998.48,\"new_seller_price\":2046.64,\"adjustment_delta\":48.16,\"old_customer_price\":2098.4,\"new_customer_price\":2148.97,\"commission_rate\":5},{\"sku_id\":7,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4721.2,\"new_seller_price\":4841.6,\"adjustment_delta\":120.4,\"old_customer_price\":4957.26,\"new_customer_price\":5083.68,\"commission_rate\":5},{\"sku_id\":8,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":9192.4,\"new_seller_price\":9433.2,\"adjustment_delta\":240.8,\"old_customer_price\":9652.02,\"new_customer_price\":9904.86,\"commission_rate\":5},{\"sku_id\":9,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":457.12,\"new_seller_price\":469.16,\"adjustment_delta\":12.04,\"old_customer_price\":479.98,\"new_customer_price\":492.62,\"commission_rate\":5},{\"sku_id\":10,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1828.48,\"new_seller_price\":1876.64,\"adjustment_delta\":48.16,\"old_customer_price\":1919.9,\"new_customer_price\":1970.47,\"commission_rate\":5},{\"sku_id\":11,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4371.2,\"new_seller_price\":4491.6,\"adjustment_delta\":120.4,\"old_customer_price\":4589.76,\"new_customer_price\":4716.18,\"commission_rate\":5},{\"sku_id\":12,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8542.4,\"new_seller_price\":8783.2,\"adjustment_delta\":240.8,\"old_customer_price\":8969.52,\"new_customer_price\":9222.36,\"commission_rate\":5},{\"sku_id\":13,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":447.12,\"new_seller_price\":459.16,\"adjustment_delta\":12.04,\"old_customer_price\":469.48,\"new_customer_price\":482.12,\"commission_rate\":5},{\"sku_id\":14,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1788.48,\"new_seller_price\":1836.64,\"adjustment_delta\":48.16,\"old_customer_price\":1877.9,\"new_customer_price\":1928.47,\"commission_rate\":5},{\"sku_id\":15,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4271.2,\"new_seller_price\":4391.6,\"adjustment_delta\":120.4,\"old_customer_price\":4484.76,\"new_customer_price\":4611.18,\"commission_rate\":5},{\"sku_id\":16,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8342.4,\"new_seller_price\":8583.2,\"adjustment_delta\":240.8,\"old_customer_price\":8759.52,\"new_customer_price\":9012.36,\"commission_rate\":5}]',1,'2026-08-22 04:01:11','2026-08-22 04:01:11'),
(8,1,4,'per_litre',65.00,'family','{\"type\":\"family\",\"shades\":[],\"packings\":[],\"skus\":[]}',16,'[{\"sku_id\":1,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":457.18,\"new_seller_price\":522.18,\"adjustment_delta\":65,\"old_customer_price\":480.04,\"new_customer_price\":548.29,\"commission_rate\":5},{\"sku_id\":2,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1828.72,\"new_seller_price\":2088.72,\"adjustment_delta\":260,\"old_customer_price\":1920.16,\"new_customer_price\":2193.16,\"commission_rate\":5},{\"sku_id\":3,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4367.8,\"new_seller_price\":5017.8,\"adjustment_delta\":650,\"old_customer_price\":4586.19,\"new_customer_price\":5268.69,\"commission_rate\":5},{\"sku_id\":4,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8531.6,\"new_seller_price\":9831.6,\"adjustment_delta\":1300,\"old_customer_price\":8958.18,\"new_customer_price\":10323.18,\"commission_rate\":5},{\"sku_id\":5,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":549.16,\"new_seller_price\":614.16,\"adjustment_delta\":65,\"old_customer_price\":576.62,\"new_customer_price\":644.87,\"commission_rate\":5},{\"sku_id\":6,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":2046.64,\"new_seller_price\":2306.64,\"adjustment_delta\":260,\"old_customer_price\":2148.97,\"new_customer_price\":2421.97,\"commission_rate\":5},{\"sku_id\":7,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4841.6,\"new_seller_price\":5491.6,\"adjustment_delta\":650,\"old_customer_price\":5083.68,\"new_customer_price\":5766.18,\"commission_rate\":5},{\"sku_id\":8,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":9433.2,\"new_seller_price\":10733.2,\"adjustment_delta\":1300,\"old_customer_price\":9904.86,\"new_customer_price\":11269.86,\"commission_rate\":5},{\"sku_id\":9,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":469.16,\"new_seller_price\":534.16,\"adjustment_delta\":65,\"old_customer_price\":492.62,\"new_customer_price\":560.87,\"commission_rate\":5},{\"sku_id\":10,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1876.64,\"new_seller_price\":2136.64,\"adjustment_delta\":260,\"old_customer_price\":1970.47,\"new_customer_price\":2243.47,\"commission_rate\":5},{\"sku_id\":11,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4491.6,\"new_seller_price\":5141.6,\"adjustment_delta\":650,\"old_customer_price\":4716.18,\"new_customer_price\":5398.68,\"commission_rate\":5},{\"sku_id\":12,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8783.2,\"new_seller_price\":10083.2,\"adjustment_delta\":1300,\"old_customer_price\":9222.36,\"new_customer_price\":10587.36,\"commission_rate\":5},{\"sku_id\":13,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":459.16,\"new_seller_price\":524.16,\"adjustment_delta\":65,\"old_customer_price\":482.12,\"new_customer_price\":550.37,\"commission_rate\":5},{\"sku_id\":14,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1836.64,\"new_seller_price\":2096.64,\"adjustment_delta\":260,\"old_customer_price\":1928.47,\"new_customer_price\":2201.47,\"commission_rate\":5},{\"sku_id\":15,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4391.6,\"new_seller_price\":5041.6,\"adjustment_delta\":650,\"old_customer_price\":4611.18,\"new_customer_price\":5293.68,\"commission_rate\":5},{\"sku_id\":16,\"shade_id\":\"4\",\"shade_name\":\"Emerald Green\",\"hexcode\":\"#059669\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8583.2,\"new_seller_price\":9883.2,\"adjustment_delta\":1300,\"old_customer_price\":9012.36,\"new_customer_price\":10377.36,\"commission_rate\":5}]',1,'2026-08-22 04:01:41','2026-08-22 04:01:41'),
(9,3,5,'per_litre',20.00,'family','{\"type\":\"family\",\"shades\":[\"2\"],\"packings\":[],\"skus\":[]}',12,'[{\"sku_id\":17,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":420,\"new_seller_price\":440,\"adjustment_delta\":20,\"old_customer_price\":525,\"new_customer_price\":462,\"commission_rate\":5},{\"sku_id\":18,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1650,\"new_seller_price\":1730,\"adjustment_delta\":80,\"old_customer_price\":2062.5,\"new_customer_price\":1816.5,\"commission_rate\":5},{\"sku_id\":19,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":3950,\"new_seller_price\":4150,\"adjustment_delta\":200,\"old_customer_price\":4937.5,\"new_customer_price\":4357.5,\"commission_rate\":5},{\"sku_id\":20,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7700,\"new_seller_price\":8100,\"adjustment_delta\":400,\"old_customer_price\":9625,\"new_customer_price\":8505,\"commission_rate\":5},{\"sku_id\":21,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":460,\"new_seller_price\":480,\"adjustment_delta\":20,\"old_customer_price\":575,\"new_customer_price\":504,\"commission_rate\":5},{\"sku_id\":22,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1820,\"new_seller_price\":1900,\"adjustment_delta\":80,\"old_customer_price\":2275,\"new_customer_price\":1995,\"commission_rate\":5},{\"sku_id\":23,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4400,\"new_seller_price\":4600,\"adjustment_delta\":200,\"old_customer_price\":5500,\"new_customer_price\":4830,\"commission_rate\":5},{\"sku_id\":24,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8600,\"new_seller_price\":9000,\"adjustment_delta\":400,\"old_customer_price\":10750,\"new_customer_price\":9450,\"commission_rate\":5},{\"sku_id\":25,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":440,\"new_seller_price\":460,\"adjustment_delta\":20,\"old_customer_price\":550,\"new_customer_price\":483,\"commission_rate\":5},{\"sku_id\":26,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1740,\"new_seller_price\":1820,\"adjustment_delta\":80,\"old_customer_price\":2175,\"new_customer_price\":1911,\"commission_rate\":5},{\"sku_id\":27,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4200,\"new_seller_price\":4400,\"adjustment_delta\":200,\"old_customer_price\":5250,\"new_customer_price\":4620,\"commission_rate\":5},{\"sku_id\":28,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8200,\"new_seller_price\":8600,\"adjustment_delta\":400,\"old_customer_price\":10250,\"new_customer_price\":9030,\"commission_rate\":5}]',3,'2026-08-24 02:54:06','2026-08-24 02:54:06'),
(10,3,5,'percentage',5.00,'category','{\"type\":\"category\",\"category_id\":1}',12,'[{\"sku_id\":159,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":420,\"new_seller_price\":441,\"adjustment_delta\":21,\"old_customer_price\":525,\"new_customer_price\":463.05,\"commission_rate\":5},{\"sku_id\":160,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1650,\"new_seller_price\":1732.5,\"adjustment_delta\":82.5,\"old_customer_price\":2062.5,\"new_customer_price\":1819.13,\"commission_rate\":5},{\"sku_id\":161,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":3950,\"new_seller_price\":4147.5,\"adjustment_delta\":197.5,\"old_customer_price\":4937.5,\"new_customer_price\":4354.88,\"commission_rate\":5},{\"sku_id\":162,\"shade_id\":\"1\",\"shade_name\":\"Super White\",\"hexcode\":\"#FFFFFF\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":7700,\"new_seller_price\":8085,\"adjustment_delta\":385,\"old_customer_price\":9625,\"new_customer_price\":8489.25,\"commission_rate\":5},{\"sku_id\":163,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":460,\"new_seller_price\":483,\"adjustment_delta\":23,\"old_customer_price\":575,\"new_customer_price\":507.15,\"commission_rate\":5},{\"sku_id\":164,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1820,\"new_seller_price\":1911,\"adjustment_delta\":91,\"old_customer_price\":2275,\"new_customer_price\":2006.55,\"commission_rate\":5},{\"sku_id\":165,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4400,\"new_seller_price\":4620,\"adjustment_delta\":220,\"old_customer_price\":5500,\"new_customer_price\":4851,\"commission_rate\":5},{\"sku_id\":166,\"shade_id\":\"2\",\"shade_name\":\"Royal Blue\",\"hexcode\":\"#1E3A8A\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8600,\"new_seller_price\":9030,\"adjustment_delta\":430,\"old_customer_price\":10750,\"new_customer_price\":9481.5,\"commission_rate\":5},{\"sku_id\":167,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":440,\"new_seller_price\":462,\"adjustment_delta\":22,\"old_customer_price\":550,\"new_customer_price\":485.1,\"commission_rate\":5},{\"sku_id\":168,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"2\",\"packing_name\":\"4 Litres\",\"pack_litres\":4,\"old_seller_price\":1740,\"new_seller_price\":1827,\"adjustment_delta\":87,\"old_customer_price\":2175,\"new_customer_price\":1918.35,\"commission_rate\":5},{\"sku_id\":169,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"3\",\"packing_name\":\"10 Litres\",\"pack_litres\":10,\"old_seller_price\":4200,\"new_seller_price\":4410,\"adjustment_delta\":210,\"old_customer_price\":5250,\"new_customer_price\":4630.5,\"commission_rate\":5},{\"sku_id\":170,\"shade_id\":\"3\",\"shade_name\":\"Sunset Orange\",\"hexcode\":\"#EA580C\",\"packing_id\":\"4\",\"packing_name\":\"20 Litres\",\"pack_litres\":20,\"old_seller_price\":8200,\"new_seller_price\":8610,\"adjustment_delta\":410,\"old_customer_price\":10250,\"new_customer_price\":9040.5,\"commission_rate\":5}]',3,'2026-08-24 04:49:26','2026-08-24 04:49:26'),
(11,3,6,'per_litre',1.04,'packings','{\"type\":\"packings\",\"shades\":[],\"packings\":[\"1\"],\"skus\":[]}',3,'[{\"sku_id\":171,\"shade_id\":\"5\",\"shade_name\":\"Pearl Ivory\",\"hexcode\":\"#FFFBEB\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":550,\"new_seller_price\":551.04,\"adjustment_delta\":1.04,\"old_customer_price\":687.5,\"new_customer_price\":578.59,\"commission_rate\":5},{\"sku_id\":175,\"shade_id\":\"6\",\"shade_name\":\"Mint Whisper\",\"hexcode\":\"#ECFDF5\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":560,\"new_seller_price\":561.04,\"adjustment_delta\":1.04,\"old_customer_price\":700,\"new_customer_price\":589.09,\"commission_rate\":5},{\"sku_id\":179,\"shade_id\":\"7\",\"shade_name\":\"Coral Glow\",\"hexcode\":\"#FFE4E6\",\"packing_id\":\"1\",\"packing_name\":\"1 Litre\",\"pack_litres\":1,\"old_seller_price\":580,\"new_seller_price\":581.04,\"adjustment_delta\":1.04,\"old_customer_price\":725,\"new_customer_price\":610.09,\"commission_rate\":5}]',3,'2026-09-08 05:44:08','2026-09-08 05:44:08');
/*!40000 ALTER TABLE `paint_price_adjustments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `product_attributes`
--

DROP TABLE IF EXISTS `product_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_attributes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `oldprice` varchar(255) NOT NULL,
  `seller_price` decimal(12,2) DEFAULT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 25.00,
  `pack_litres` decimal(8,2) NOT NULL DEFAULT 1.00,
  `price` varchar(255) NOT NULL,
  `status` enum('active','deactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_attr_product_id` (`product_id`),
  KEY `idx_product_attr_color` (`color`),
  KEY `idx_product_attr_quantity` (`quantity`),
  KEY `idx_prod_attr_prod_color` (`product_id`,`color`)
) ENGINE=InnoDB AUTO_INCREMENT=830 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attributes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `product_attributes` WRITE;
/*!40000 ALTER TABLE `product_attributes` DISABLE KEYS */;
INSERT INTO `product_attributes` VALUES
(268,'28','34','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(269,'28','34','26','198.34',158.67,5.00,1.00,'166.6','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(270,'28','34','27','433.34',346.67,5.00,1.00,'364','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(271,'28','34','28','805',644.00,5.00,1.00,'676.2','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(272,'28','34','29','3143.34',2514.67,5.00,1.00,'2640.4','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(273,'28','34','30','7658.34',6126.67,5.00,1.00,'6433','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(274,'28','34','31','14850',11880.00,5.00,1.00,'12474','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(275,'28','35','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(276,'28','36','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(277,'28','37','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(278,'28','38','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(279,'28','39','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(280,'28','40','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:15:32'),
(281,'28','41','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:05:07'),
(282,'28','42','25','111.66',89.33,5.00,1.00,'93.8','active','2026-11-02 00:57:20','2026-11-02 01:05:07'),
(283,'29','43','28','411.66',329.33,5.00,1.00,'345.8','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(284,'29','43','29','1618.34',1294.67,5.00,1.00,'1359.4','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(285,'29','43','31','7575',6060.00,5.00,1.00,'6363','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(286,'29','43','30','3896.66',3117.33,5.00,1.00,'3273.2','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(287,'30','43','29','143.34',114.67,5.00,1.00,'120.4','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(288,'30','43','28','516.66',413.33,5.00,1.00,'434','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(289,'30','43','30','1210',968.00,5.00,1.00,'1016.4','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(290,'30','43','31','2266.66',1813.33,5.00,1.00,'1904','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(291,'31','43','29','483.34',386.67,5.00,1.00,'406','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(292,'31','43','28','136.66',109.33,5.00,1.00,'114.8','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(293,'31','43','30','991.66',793.33,5.00,1.00,'833','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(294,'31','43','31','1816.66',1453.33,5.00,1.00,'1526','active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(295,'32','43','29','170',136.00,5.00,1.00,'142.8','active','2026-11-02 00:57:21','2026-11-02 01:15:34'),
(296,'32','43','28','616.66',493.33,5.00,1.00,'518','active','2026-11-02 00:57:21','2026-11-02 01:15:34'),
(297,'32','43','30','1466.66',1173.33,5.00,1.00,'1232','active','2026-11-02 00:57:21','2026-11-02 01:15:34'),
(298,'32','43','31','2715',2172.00,5.00,1.00,'2280.6','active','2026-11-02 00:57:21','2026-11-02 01:15:34'),
(299,'33','44','32','371.66',297.33,5.00,1.00,'312.2','active','2026-11-02 00:57:21','2026-11-02 01:15:34'),
(300,'33','44','33','1426.25',1141.00,5.00,1.00,'1198.05','active','2026-11-02 00:57:21','2026-11-02 01:15:34'),
(301,'33','44','34','3466.66',2773.33,5.00,1.00,'2912','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(302,'33','44','35','6737.91',5390.33,5.00,1.00,'5659.85','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(303,'34','44','32','8091.66',6473.33,5.00,1.00,'6797','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(304,'34','44','33','4133.34',3306.67,5.00,1.00,'3472','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(305,'34','44','34','1706.66',1365.33,5.00,1.00,'1433.6','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(306,'34','44','35','8091.66',6473.33,5.00,1.00,'6797','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(307,'35','44','32','355',284.00,5.00,1.00,'298.2','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(308,'35','44','33','1406.62',1125.30,5.00,1.00,'1181.57','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(309,'35','44','34','3393.34',2714.67,5.00,1.00,'2850.4','active','2026-11-02 00:57:22','2026-11-02 01:15:34'),
(310,'35','44','35','6600',5280.00,5.00,1.00,'5544','active','2026-11-02 00:57:22','2026-11-02 01:15:35'),
(311,'36','44','28','473.34',378.67,5.00,1.00,'397.6','active','2026-11-02 00:57:23','2026-11-02 01:15:35'),
(312,'36','44','29','1883.34',1506.67,5.00,1.00,'1582','active','2026-11-02 00:57:23','2026-11-02 01:15:35'),
(313,'36','44','30','4575',3660.00,5.00,1.00,'3843','active','2026-11-02 00:57:23','2026-11-02 01:15:35'),
(314,'36','44','31','8998.34',7198.67,5.00,1.00,'7558.6','active','2026-11-02 00:57:23','2026-11-02 01:15:35'),
(315,'37','45','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 00:57:23','2026-11-02 01:15:35'),
(316,'37','45','37','420',336.00,5.00,1.00,'352.8','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(317,'37','45','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(318,'37','46','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(319,'37','47','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(320,'37','48','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(321,'37','49','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(322,'37','50','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(323,'37','51','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(324,'37','52','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:05:09'),
(325,'37','36','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:23','2026-11-02 01:05:09'),
(326,'38','43','29','178.34',142.67,5.00,1.00,'149.8','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(327,'38','43','28','662.5',530.00,5.00,1.00,'556.5','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(328,'38','43','30','1541.66',1233.33,5.00,1.00,'1295','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(329,'38','43','31','2950',2360.00,5.00,1.00,'2478','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(330,'39','53','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(331,'39','53','39','9440',7552.00,5.00,1.00,'7929.6','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(332,'39','53','40','1926.66',1541.33,5.00,1.00,'1618.4','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(333,'39','53','41','260',208.00,5.00,1.00,'218.4','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(334,'39','53','42','125',100.00,5.00,1.00,'105','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(335,'39','46','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(336,'39','54','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(337,'39','47','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(338,'39','55','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(339,'39','56','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(340,'39','57','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 01:15:36'),
(341,'39','58','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 01:05:10'),
(342,'39','59','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 00:57:23','2026-11-02 01:05:10'),
(343,'40','60','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(344,'40','61','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(345,'40','62','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(346,'40','63','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(347,'40','64','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(348,'40','47','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(349,'40','65','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(350,'40','66','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:05:10'),
(351,'40','67','43','61.66',49.33,5.00,1.00,'51.8','active','2026-11-02 00:57:23','2026-11-02 01:05:10'),
(352,'41','68','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 00:57:23','2026-11-02 01:15:37'),
(353,'41','68','37','3775.41',3020.33,5.00,1.00,'3171.35','active','2026-11-02 00:57:24','2026-11-02 01:15:38'),
(354,'41','68','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:15:38'),
(355,'41','69','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:15:38'),
(356,'41','70','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:15:38'),
(357,'41','71','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:15:38'),
(358,'41','72','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(359,'41','73','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(360,'41','74','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(361,'41','75','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:05:10'),
(362,'41','76','38','766.66',613.33,5.00,1.00,'644','active','2026-11-02 00:57:24','2026-11-02 01:05:10'),
(363,'42','43','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(364,'42','43','28','1451.66',1161.33,5.00,1.00,'1219.4','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(365,'42','43','30','3480',2784.00,5.00,1.00,'2923.2','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(366,'42','43','31','6741.66',5393.33,5.00,1.00,'5663','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(367,'42','77','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(368,'42','78','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(369,'42','36','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(370,'42','39','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(371,'42','79','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(372,'42','80','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:15:39'),
(373,'42','23','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:05:10'),
(374,'42','81','29','370',296.00,5.00,1.00,'310.8','active','2026-11-02 00:57:24','2026-11-02 01:05:10'),
(375,'43','82','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(376,'43','82','28','223.34',178.67,5.00,1.00,'187.6','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(377,'43','82','31','3883.34',3106.67,5.00,1.00,'3262','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(378,'43','82','30','1991.66',1593.33,5.00,1.00,'1673','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(379,'43','82','27','130',104.00,5.00,1.00,'109.2','active','2026-11-02 00:57:24','2026-11-02 01:15:41'),
(380,'43','82','26','85',68.00,5.00,1.00,'71.4','active','2026-11-02 00:57:24','2026-11-02 01:15:41'),
(381,'43','83','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(382,'43','36','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(383,'43','42','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(384,'43','77','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:24','2026-11-02 01:15:40'),
(385,'43','84','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:25','2026-11-02 01:15:40'),
(386,'43','85','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:25','2026-11-02 01:15:40'),
(387,'43','80','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:25','2026-11-02 01:05:10'),
(388,'43','86','29','865',692.00,5.00,1.00,'726.6','active','2026-11-02 00:57:25','2026-11-02 01:05:10'),
(389,'44','82','44','186.25',149.00,5.00,1.00,'156.45','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(390,'44','82','45','421.66',337.33,5.00,1.00,'354.2','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(391,'44','82','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(392,'44','82','47','1436.66',1149.33,5.00,1.00,'1206.8','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(393,'44','87','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(394,'44','88','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(395,'44','89','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(396,'44','90','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(397,'44','91','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(398,'44','67','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(399,'44','65','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:05:13'),
(400,'44','47','46','776.66',621.33,5.00,1.00,'652.4','active','2026-11-02 00:57:30','2026-11-02 01:05:13'),
(401,'45','43','29','493.34',394.67,5.00,1.00,'414.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(402,'45','43','28','140',112.00,5.00,1.00,'117.6','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(403,'45','43','31','2126.66',1701.33,5.00,1.00,'1786.4','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(404,'45','43','30','1130',904.00,5.00,1.00,'949.2','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(405,'46','92','36','15536.62',12429.30,5.00,1.00,'13050.77','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(406,'46','92','37','3146.66',2517.33,5.00,1.00,'2643.2','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(407,'46','92','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(408,'46','46','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(409,'46','93','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(410,'46','47','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(411,'46','94','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(412,'46','55','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(413,'46','95','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(414,'46','96','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:05:13'),
(415,'46','97','38','641.66',513.33,5.00,1.00,'539','active','2026-11-02 00:57:30','2026-11-02 01:05:13'),
(416,'47','43','29','276.25',221.00,5.00,1.00,'232.05','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(417,'47','43','28','1063.34',850.67,5.00,1.00,'893.2','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(418,'47','43','30','2516.66',2013.33,5.00,1.00,'2114','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(419,'47','43','31','4750',3800.00,5.00,1.00,'3990','active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(420,'48','43','31','6878.52',5502.82,5.00,1.00,'5777.96','active','2026-11-02 00:57:30','2026-11-02 01:15:43'),
(421,'48','43','29','1497.17',1197.74,5.00,1.00,'1257.63','active','2026-11-02 00:57:30','2026-11-02 01:15:43'),
(422,'48','43','28','395.48',316.38,5.00,1.00,'332.2','active','2026-11-02 00:57:30','2026-11-02 01:15:43'),
(423,'48','43','27','204.8',163.84,5.00,1.00,'172.03','active','2026-11-02 00:57:30','2026-11-02 01:15:43'),
(424,'48','43','26','113',90.40,5.00,1.00,'94.92','active','2026-11-02 00:57:30','2026-11-02 01:15:43'),
(425,'48','98','29','1497.17',1197.74,5.00,1.00,'1257.63','active','2026-11-02 00:57:31','2026-11-02 01:15:43'),
(426,'48','99','29','1497.17',1197.74,5.00,1.00,'1257.63','active','2026-11-02 00:57:31','2026-11-02 01:15:43'),
(427,'48','100','29','1497.17',1197.74,5.00,1.00,'1257.63','active','2026-11-02 00:57:31','2026-11-02 01:15:43'),
(428,'48','101','29','1497.17',1197.74,5.00,1.00,'1257.63','active','2026-11-02 00:57:31','2026-11-02 01:15:43'),
(429,'48','79','29','1497.17',1197.74,5.00,1.00,'1257.63','active','2026-11-02 00:57:31','2026-11-02 01:15:43'),
(430,'48','102','29','1497.17',1197.74,5.00,1.00,'1257.63','active','2026-11-02 00:57:31','2026-11-02 01:15:43'),
(431,'48','103','29','1299.44',1039.55,5.00,1.00,'1091.53','active','2026-11-02 00:57:31','2026-11-02 01:05:14'),
(432,'48','78','29','1299.44',1039.55,5.00,1.00,'1091.53','active','2026-11-02 00:57:31','2026-11-02 01:05:14'),
(433,'49','104','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 00:57:31','2026-11-02 01:15:43'),
(434,'49','104','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:15:44'),
(435,'49','105','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:15:44'),
(436,'49','1','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:15:44'),
(437,'49','46','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:15:44'),
(438,'49','106','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:15:44'),
(439,'49','107','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:15:44'),
(440,'49','108','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:15:44'),
(441,'49','109','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:05:14'),
(442,'49','110','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:31','2026-11-02 01:05:14'),
(443,'50','104','31','7473.34',5978.67,5.00,1.00,'6277.6','active','2026-11-02 00:57:32','2026-11-02 01:15:44'),
(444,'50','104','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:33','2026-11-02 01:15:44'),
(445,'50','104','28','413.34',330.67,5.00,1.00,'347.2','active','2026-11-02 00:57:34','2026-11-02 01:15:44'),
(446,'50','104','27','220',176.00,5.00,1.00,'184.8','active','2026-11-02 00:57:34','2026-11-02 01:15:44'),
(447,'50','104','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 00:57:34','2026-11-02 01:15:45'),
(448,'50','1','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:34','2026-11-02 01:15:44'),
(449,'50','106','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:34','2026-11-02 01:15:44'),
(450,'50','107','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:35','2026-11-02 01:15:44'),
(451,'50','43','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:35','2026-11-02 01:15:44'),
(452,'50','98','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:35','2026-11-02 01:15:44'),
(453,'50','111','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:35','2026-11-02 01:15:44'),
(454,'50','112','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:35','2026-11-02 01:05:14'),
(455,'50','113','29','1573.34',1258.67,5.00,1.00,'1321.6','active','2026-11-02 00:57:35','2026-11-02 01:05:14'),
(456,'51','114','31','6713.34',5370.67,5.00,1.00,'5639.2','active','2026-11-02 00:57:35','2026-11-02 01:15:45'),
(457,'51','114','29','1400',1120.00,5.00,1.00,'1176','active','2026-11-02 00:57:35','2026-11-02 01:15:45'),
(458,'51','114','28','348.34',278.67,5.00,1.00,'292.6','active','2026-11-02 00:57:35','2026-11-02 01:15:45'),
(459,'51','114','27','193.34',154.67,5.00,1.00,'162.4','active','2026-11-02 00:57:35','2026-11-02 01:15:45'),
(460,'51','114','26','100',80.00,5.00,1.00,'84','active','2026-11-02 00:57:35','2026-11-02 01:15:45'),
(461,'51','115','29','1400',1120.00,5.00,1.00,'1176','active','2026-11-02 00:57:35','2026-11-02 01:15:45'),
(462,'52','116','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(463,'52','117','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(464,'52','118','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(465,'52','119','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(466,'52','120','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(467,'52','121','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(468,'52','122','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(469,'52','123','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(470,'52','124','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(471,'53','125','29','975',780.00,5.00,1.00,'819','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(472,'53','125','28','265',212.00,5.00,1.00,'222.6','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(473,'53','125','27','146.66',117.33,5.00,1.00,'123.2','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(474,'53','125','26','78.34',62.67,5.00,1.00,'65.8','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(475,'53','125','31','4500',3600.00,5.00,1.00,'3780','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(476,'53','126','29','975',780.00,5.00,1.00,'819','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(477,'53','127','29','975',780.00,5.00,1.00,'819','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(478,'53','128','29','975',780.00,5.00,1.00,'819','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(479,'53','129','29','975',780.00,5.00,1.00,'819','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(480,'53','130','29','975',780.00,5.00,1.00,'819','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(481,'53','131','29','975',780.00,5.00,1.00,'819','active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(482,'54','132','31','7355',5884.00,5.00,1.00,'6178.2','active','2026-11-02 00:57:38','2026-11-02 01:15:46'),
(483,'54','132','29','1558.34',1246.67,5.00,1.00,'1309','active','2026-11-02 00:57:38','2026-11-02 01:15:46'),
(484,'54','132','28','410',328.00,5.00,1.00,'344.4','active','2026-11-02 00:57:38','2026-11-02 01:15:47'),
(485,'54','132','27','218.34',174.67,5.00,1.00,'183.4','active','2026-11-02 00:57:38','2026-11-02 01:15:47'),
(486,'54','132','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 00:57:38','2026-11-02 01:15:47'),
(487,'54','133','29','1558.34',1246.67,5.00,1.00,'1309','active','2026-11-02 00:57:38','2026-11-02 01:15:46'),
(488,'54','134','29','1558.34',1246.67,5.00,1.00,'1309','active','2026-11-02 00:57:38','2026-11-02 01:15:46'),
(489,'54','135','29','1558.34',1246.67,5.00,1.00,'1309','active','2026-11-02 00:57:38','2026-11-02 01:15:46'),
(490,'54','136','29','1558.34',1246.67,5.00,1.00,'1309','active','2026-11-02 00:57:38','2026-11-02 01:15:46'),
(491,'54','137','29','1558.34',1246.67,5.00,1.00,'1309','active','2026-11-02 00:57:38','2026-11-02 01:15:46'),
(492,'54','57','29','1558.34',1246.67,5.00,1.00,'1309','active','2026-11-02 00:57:38','2026-11-02 01:15:46'),
(493,'54','138','29','1558.34',1246.67,5.00,1.00,'1309','active','2026-11-02 00:57:38','2026-11-02 01:05:16'),
(494,'55','139','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 01:15:47'),
(495,'55','140','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 01:15:47'),
(496,'55','141','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 01:15:47'),
(497,'55','142','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(498,'55','143','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 01:15:47'),
(499,'55','144','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 01:15:47'),
(500,'55','145','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 01:15:47'),
(501,'55','146','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 01:05:18'),
(502,'55','147','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 00:57:40','2026-11-02 01:05:18'),
(503,'56','148','31','8260',6608.00,5.00,1.00,'6938.4','active','2026-11-02 00:57:44','2026-11-02 01:15:48'),
(504,'56','148','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(505,'56','148','28','433.34',346.67,5.00,1.00,'364','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(506,'56','149','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(507,'56','38','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(508,'56','150','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(509,'56','151','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(510,'56','152','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(511,'56','153','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(512,'56','154','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:05:21'),
(513,'56','155','29','1691.66',1353.33,5.00,1.00,'1421','active','2026-11-02 00:57:45','2026-11-02 01:05:21'),
(514,'57','104','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(515,'57','104','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(516,'57','98','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(517,'57','156','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(518,'57','111','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(519,'57','157','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(520,'57','158','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(521,'57','159','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:15:48'),
(522,'57','160','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:05:21'),
(523,'57','112','28','388.41',310.73,5.00,1.00,'326.27','active','2026-11-02 00:57:45','2026-11-02 01:05:21'),
(524,'39','161','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 01:05:10','2026-11-02 01:05:10'),
(525,'52','162','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(526,'52','163','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(527,'52','164','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(528,'52','165','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(529,'52','166','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(530,'52','167','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(531,'52','168','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(532,'52','169','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(533,'52','170','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(534,'55','171','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 01:05:18','2026-11-02 01:05:18'),
(535,'28','35','26','198.34',158.67,5.00,1.00,'166.6','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(536,'28','36','26','198.34',158.67,5.00,1.00,'166.6','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(537,'28','37','26','198.34',158.67,5.00,1.00,'166.6','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(538,'28','38','26','198.34',158.67,5.00,1.00,'166.6','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(539,'28','39','26','198.34',158.67,5.00,1.00,'166.6','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(540,'28','40','26','198.34',158.67,5.00,1.00,'166.6','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(541,'28','35','27','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(542,'28','36','27','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(543,'28','37','27','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(544,'28','38','27','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(545,'28','39','27','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(546,'28','40','27','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(547,'28','35','28','805',644.00,5.00,1.00,'676.2','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(548,'28','36','28','805',644.00,5.00,1.00,'676.2','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(549,'28','37','28','805',644.00,5.00,1.00,'676.2','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(550,'28','38','28','805',644.00,5.00,1.00,'676.2','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(551,'28','39','28','805',644.00,5.00,1.00,'676.2','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(552,'28','40','28','805',644.00,5.00,1.00,'676.2','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(553,'28','35','29','3143.34',2514.67,5.00,1.00,'2640.4','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(554,'28','36','29','3143.34',2514.67,5.00,1.00,'2640.4','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(555,'28','37','29','3143.34',2514.67,5.00,1.00,'2640.4','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(556,'28','38','29','3143.34',2514.67,5.00,1.00,'2640.4','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(557,'28','39','29','3143.34',2514.67,5.00,1.00,'2640.4','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(558,'28','40','29','3143.34',2514.67,5.00,1.00,'2640.4','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(559,'28','35','30','7658.34',6126.67,5.00,1.00,'6433','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(560,'28','36','30','7658.34',6126.67,5.00,1.00,'6433','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(561,'28','37','30','7658.34',6126.67,5.00,1.00,'6433','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(562,'28','38','30','7658.34',6126.67,5.00,1.00,'6433','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(563,'28','39','30','7658.34',6126.67,5.00,1.00,'6433','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(564,'28','40','30','7658.34',6126.67,5.00,1.00,'6433','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(565,'28','35','31','14850',11880.00,5.00,1.00,'12474','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(566,'28','36','31','14850',11880.00,5.00,1.00,'12474','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(567,'28','37','31','14850',11880.00,5.00,1.00,'12474','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(568,'28','38','31','14850',11880.00,5.00,1.00,'12474','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(569,'28','39','31','14850',11880.00,5.00,1.00,'12474','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(570,'28','40','31','14850',11880.00,5.00,1.00,'12474','active','2026-11-02 01:15:32','2026-11-02 01:15:32'),
(571,'37','46','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(572,'37','47','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(573,'37','48','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(574,'37','49','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(575,'37','50','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(576,'37','51','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(577,'37','46','37','420',336.00,5.00,1.00,'352.8','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(578,'37','47','37','420',336.00,5.00,1.00,'352.8','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(579,'37','48','37','420',336.00,5.00,1.00,'352.8','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(580,'37','49','37','420',336.00,5.00,1.00,'352.8','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(581,'37','50','37','420',336.00,5.00,1.00,'352.8','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(582,'37','51','37','420',336.00,5.00,1.00,'352.8','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(583,'39','172','38','491.66',393.33,5.00,1.00,'413','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(584,'39','46','39','9440',7552.00,5.00,1.00,'7929.6','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(585,'39','173','39','9440',7552.00,5.00,1.00,'7929.6','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(586,'39','47','39','9440',7552.00,5.00,1.00,'7929.6','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(587,'39','55','39','9440',7552.00,5.00,1.00,'7929.6','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(588,'39','56','39','9440',7552.00,5.00,1.00,'7929.6','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(589,'39','57','39','9440',7552.00,5.00,1.00,'7929.6','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(590,'39','46','40','1926.66',1541.33,5.00,1.00,'1618.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(591,'39','174','40','1926.66',1541.33,5.00,1.00,'1618.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(592,'39','47','40','1926.66',1541.33,5.00,1.00,'1618.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(593,'39','55','40','1926.66',1541.33,5.00,1.00,'1618.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(594,'39','56','40','1926.66',1541.33,5.00,1.00,'1618.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(595,'39','57','40','1926.66',1541.33,5.00,1.00,'1618.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(596,'39','46','41','260',208.00,5.00,1.00,'218.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(597,'39','175','41','260',208.00,5.00,1.00,'218.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(598,'39','47','41','260',208.00,5.00,1.00,'218.4','active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(599,'39','55','41','260',208.00,5.00,1.00,'218.4','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(600,'39','56','41','260',208.00,5.00,1.00,'218.4','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(601,'39','57','41','260',208.00,5.00,1.00,'218.4','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(602,'39','46','42','125',100.00,5.00,1.00,'105','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(603,'39','176','42','125',100.00,5.00,1.00,'105','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(604,'39','47','42','125',100.00,5.00,1.00,'105','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(605,'39','55','42','125',100.00,5.00,1.00,'105','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(606,'39','56','42','125',100.00,5.00,1.00,'105','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(607,'39','57','42','125',100.00,5.00,1.00,'105','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(608,'41','69','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(609,'41','70','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(610,'41','71','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(611,'41','72','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(612,'41','73','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(613,'41','74','36','18585',14868.00,5.00,1.00,'15611.4','active','2026-11-02 01:15:38','2026-11-02 01:15:38'),
(614,'41','69','37','3775.41',3020.33,5.00,1.00,'3171.35','active','2026-11-02 01:15:38','2026-11-02 01:15:38'),
(615,'41','70','37','3775.41',3020.33,5.00,1.00,'3171.35','active','2026-11-02 01:15:38','2026-11-02 01:15:38'),
(616,'41','71','37','3775.41',3020.33,5.00,1.00,'3171.35','active','2026-11-02 01:15:38','2026-11-02 01:15:38'),
(617,'41','72','37','3775.41',3020.33,5.00,1.00,'3171.35','active','2026-11-02 01:15:38','2026-11-02 01:15:38'),
(618,'41','73','37','3775.41',3020.33,5.00,1.00,'3171.35','active','2026-11-02 01:15:38','2026-11-02 01:15:38'),
(619,'41','74','37','3775.41',3020.33,5.00,1.00,'3171.35','active','2026-11-02 01:15:38','2026-11-02 01:15:38'),
(620,'42','77','28','1451.66',1161.33,5.00,1.00,'1219.4','active','2026-11-02 01:15:39','2026-11-02 01:15:39'),
(621,'42','78','28','1451.66',1161.33,5.00,1.00,'1219.4','active','2026-11-02 01:15:39','2026-11-02 01:15:39'),
(622,'42','36','28','1451.66',1161.33,5.00,1.00,'1219.4','active','2026-11-02 01:15:39','2026-11-02 01:15:39'),
(623,'42','39','28','1451.66',1161.33,5.00,1.00,'1219.4','active','2026-11-02 01:15:39','2026-11-02 01:15:39'),
(624,'42','79','28','1451.66',1161.33,5.00,1.00,'1219.4','active','2026-11-02 01:15:39','2026-11-02 01:15:39'),
(625,'42','80','28','1451.66',1161.33,5.00,1.00,'1219.4','active','2026-11-02 01:15:39','2026-11-02 01:15:39'),
(626,'42','77','30','3480',2784.00,5.00,1.00,'2923.2','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(627,'42','78','30','3480',2784.00,5.00,1.00,'2923.2','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(628,'42','36','30','3480',2784.00,5.00,1.00,'2923.2','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(629,'42','39','30','3480',2784.00,5.00,1.00,'2923.2','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(630,'42','79','30','3480',2784.00,5.00,1.00,'2923.2','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(631,'42','80','30','3480',2784.00,5.00,1.00,'2923.2','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(632,'42','77','31','6741.66',5393.33,5.00,1.00,'5663','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(633,'42','78','31','6741.66',5393.33,5.00,1.00,'5663','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(634,'42','36','31','6741.66',5393.33,5.00,1.00,'5663','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(635,'42','39','31','6741.66',5393.33,5.00,1.00,'5663','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(636,'42','79','31','6741.66',5393.33,5.00,1.00,'5663','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(637,'42','80','31','6741.66',5393.33,5.00,1.00,'5663','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(638,'43','83','28','223.34',178.67,5.00,1.00,'187.6','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(639,'43','36','28','223.34',178.67,5.00,1.00,'187.6','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(640,'43','42','28','223.34',178.67,5.00,1.00,'187.6','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(641,'43','77','28','223.34',178.67,5.00,1.00,'187.6','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(642,'43','84','28','223.34',178.67,5.00,1.00,'187.6','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(643,'43','85','28','223.34',178.67,5.00,1.00,'187.6','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(644,'43','83','31','3883.34',3106.67,5.00,1.00,'3262','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(645,'43','36','31','3883.34',3106.67,5.00,1.00,'3262','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(646,'43','42','31','3883.34',3106.67,5.00,1.00,'3262','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(647,'43','77','31','3883.34',3106.67,5.00,1.00,'3262','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(648,'43','84','31','3883.34',3106.67,5.00,1.00,'3262','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(649,'43','85','31','3883.34',3106.67,5.00,1.00,'3262','active','2026-11-02 01:15:40','2026-11-02 01:15:40'),
(650,'43','83','30','1991.66',1593.33,5.00,1.00,'1673','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(651,'43','36','30','1991.66',1593.33,5.00,1.00,'1673','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(652,'43','42','30','1991.66',1593.33,5.00,1.00,'1673','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(653,'43','77','30','1991.66',1593.33,5.00,1.00,'1673','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(654,'43','84','30','1991.66',1593.33,5.00,1.00,'1673','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(655,'43','85','30','1991.66',1593.33,5.00,1.00,'1673','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(656,'43','83','27','130',104.00,5.00,1.00,'109.2','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(657,'43','36','27','130',104.00,5.00,1.00,'109.2','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(658,'43','42','27','130',104.00,5.00,1.00,'109.2','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(659,'43','77','27','130',104.00,5.00,1.00,'109.2','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(660,'43','84','27','130',104.00,5.00,1.00,'109.2','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(661,'43','85','27','130',104.00,5.00,1.00,'109.2','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(662,'43','83','26','85',68.00,5.00,1.00,'71.4','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(663,'43','36','26','85',68.00,5.00,1.00,'71.4','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(664,'43','42','26','85',68.00,5.00,1.00,'71.4','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(665,'43','77','26','85',68.00,5.00,1.00,'71.4','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(666,'43','84','26','85',68.00,5.00,1.00,'71.4','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(667,'43','85','26','85',68.00,5.00,1.00,'71.4','active','2026-11-02 01:15:41','2026-11-02 01:15:41'),
(668,'44','87','44','186.25',149.00,5.00,1.00,'156.45','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(669,'44','88','44','186.25',149.00,5.00,1.00,'156.45','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(670,'44','89','44','186.25',149.00,5.00,1.00,'156.45','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(671,'44','90','44','186.25',149.00,5.00,1.00,'156.45','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(672,'44','91','44','186.25',149.00,5.00,1.00,'156.45','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(673,'44','67','44','186.25',149.00,5.00,1.00,'156.45','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(674,'44','87','45','421.66',337.33,5.00,1.00,'354.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(675,'44','88','45','421.66',337.33,5.00,1.00,'354.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(676,'44','89','45','421.66',337.33,5.00,1.00,'354.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(677,'44','90','45','421.66',337.33,5.00,1.00,'354.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(678,'44','91','45','421.66',337.33,5.00,1.00,'354.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(679,'44','67','45','421.66',337.33,5.00,1.00,'354.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(680,'44','87','47','1436.66',1149.33,5.00,1.00,'1206.8','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(681,'44','88','47','1436.66',1149.33,5.00,1.00,'1206.8','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(682,'44','89','47','1436.66',1149.33,5.00,1.00,'1206.8','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(683,'44','90','47','1436.66',1149.33,5.00,1.00,'1206.8','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(684,'44','91','47','1436.66',1149.33,5.00,1.00,'1206.8','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(685,'44','67','47','1436.66',1149.33,5.00,1.00,'1206.8','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(686,'46','46','36','15536.62',12429.30,5.00,1.00,'13050.77','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(687,'46','93','36','15536.62',12429.30,5.00,1.00,'13050.77','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(688,'46','47','36','15536.62',12429.30,5.00,1.00,'13050.77','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(689,'46','94','36','15536.62',12429.30,5.00,1.00,'13050.77','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(690,'46','55','36','15536.62',12429.30,5.00,1.00,'13050.77','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(691,'46','95','36','15536.62',12429.30,5.00,1.00,'13050.77','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(692,'46','46','37','3146.66',2517.33,5.00,1.00,'2643.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(693,'46','93','37','3146.66',2517.33,5.00,1.00,'2643.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(694,'46','47','37','3146.66',2517.33,5.00,1.00,'2643.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(695,'46','94','37','3146.66',2517.33,5.00,1.00,'2643.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(696,'46','55','37','3146.66',2517.33,5.00,1.00,'2643.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(697,'46','95','37','3146.66',2517.33,5.00,1.00,'2643.2','active','2026-11-02 01:15:42','2026-11-02 01:15:42'),
(698,'48','98','31','6878.52',5502.82,5.00,1.00,'5777.96','active','2026-11-02 01:15:42','2026-11-02 01:15:43'),
(699,'48','99','31','6878.52',5502.82,5.00,1.00,'5777.96','active','2026-11-02 01:15:42','2026-11-02 01:15:43'),
(700,'48','100','31','6878.52',5502.82,5.00,1.00,'5777.96','active','2026-11-02 01:15:42','2026-11-02 01:15:43'),
(701,'48','101','31','6878.52',5502.82,5.00,1.00,'5777.96','active','2026-11-02 01:15:42','2026-11-02 01:15:43'),
(702,'48','79','31','6878.52',5502.82,5.00,1.00,'5777.96','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(703,'48','102','31','6878.52',5502.82,5.00,1.00,'5777.96','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(704,'48','98','28','395.48',316.38,5.00,1.00,'332.2','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(705,'48','99','28','395.48',316.38,5.00,1.00,'332.2','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(706,'48','100','28','395.48',316.38,5.00,1.00,'332.2','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(707,'48','101','28','395.48',316.38,5.00,1.00,'332.2','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(708,'48','79','28','395.48',316.38,5.00,1.00,'332.2','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(709,'48','102','28','395.48',316.38,5.00,1.00,'332.2','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(710,'48','98','27','204.8',163.84,5.00,1.00,'172.03','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(711,'48','99','27','204.8',163.84,5.00,1.00,'172.03','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(712,'48','100','27','204.8',163.84,5.00,1.00,'172.03','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(713,'48','101','27','204.8',163.84,5.00,1.00,'172.03','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(714,'48','79','27','204.8',163.84,5.00,1.00,'172.03','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(715,'48','102','27','204.8',163.84,5.00,1.00,'172.03','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(716,'48','98','26','113',90.40,5.00,1.00,'94.92','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(717,'48','99','26','113',90.40,5.00,1.00,'94.92','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(718,'48','100','26','113',90.40,5.00,1.00,'94.92','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(719,'48','101','26','113',90.40,5.00,1.00,'94.92','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(720,'48','79','26','113',90.40,5.00,1.00,'94.92','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(721,'48','102','26','113',90.40,5.00,1.00,'94.92','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(722,'49','105','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(723,'49','1','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(724,'49','46','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(725,'49','106','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:43','2026-11-02 01:15:43'),
(726,'49','107','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(727,'49','108','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(728,'50','1','31','7473.34',5978.67,5.00,1.00,'6277.6','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(729,'50','106','31','7473.34',5978.67,5.00,1.00,'6277.6','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(730,'50','107','31','7473.34',5978.67,5.00,1.00,'6277.6','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(731,'50','43','31','7473.34',5978.67,5.00,1.00,'6277.6','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(732,'50','98','31','7473.34',5978.67,5.00,1.00,'6277.6','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(733,'50','111','31','7473.34',5978.67,5.00,1.00,'6277.6','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(734,'50','1','28','413.34',330.67,5.00,1.00,'347.2','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(735,'50','106','28','413.34',330.67,5.00,1.00,'347.2','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(736,'50','107','28','413.34',330.67,5.00,1.00,'347.2','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(737,'50','43','28','413.34',330.67,5.00,1.00,'347.2','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(738,'50','98','28','413.34',330.67,5.00,1.00,'347.2','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(739,'50','111','28','413.34',330.67,5.00,1.00,'347.2','active','2026-11-02 01:15:44','2026-11-02 01:15:44'),
(740,'50','1','27','220',176.00,5.00,1.00,'184.8','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(741,'50','106','27','220',176.00,5.00,1.00,'184.8','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(742,'50','107','27','220',176.00,5.00,1.00,'184.8','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(743,'50','43','27','220',176.00,5.00,1.00,'184.8','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(744,'50','98','27','220',176.00,5.00,1.00,'184.8','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(745,'50','111','27','220',176.00,5.00,1.00,'184.8','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(746,'50','1','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(747,'50','106','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(748,'50','107','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(749,'50','43','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(750,'50','98','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(751,'50','111','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(752,'51','115','31','6713.34',5370.67,5.00,1.00,'5639.2','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(753,'51','115','28','348.34',278.67,5.00,1.00,'292.6','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(754,'51','115','27','193.34',154.67,5.00,1.00,'162.4','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(755,'51','115','26','100',80.00,5.00,1.00,'84','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(756,'52','177','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(757,'52','178','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(758,'52','179','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(759,'52','180','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(760,'52','181','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(761,'52','182','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(762,'52','183','31','7666.66',6133.33,5.00,1.00,'6440','active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(763,'53','126','28','265',212.00,5.00,1.00,'222.6','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(764,'53','127','28','265',212.00,5.00,1.00,'222.6','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(765,'53','128','28','265',212.00,5.00,1.00,'222.6','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(766,'53','129','28','265',212.00,5.00,1.00,'222.6','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(767,'53','130','28','265',212.00,5.00,1.00,'222.6','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(768,'53','131','28','265',212.00,5.00,1.00,'222.6','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(769,'53','126','27','146.66',117.33,5.00,1.00,'123.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(770,'53','127','27','146.66',117.33,5.00,1.00,'123.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(771,'53','128','27','146.66',117.33,5.00,1.00,'123.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(772,'53','129','27','146.66',117.33,5.00,1.00,'123.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(773,'53','130','27','146.66',117.33,5.00,1.00,'123.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(774,'53','131','27','146.66',117.33,5.00,1.00,'123.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(775,'53','126','26','78.34',62.67,5.00,1.00,'65.8','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(776,'53','127','26','78.34',62.67,5.00,1.00,'65.8','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(777,'53','128','26','78.34',62.67,5.00,1.00,'65.8','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(778,'53','129','26','78.34',62.67,5.00,1.00,'65.8','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(779,'53','130','26','78.34',62.67,5.00,1.00,'65.8','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(780,'53','131','26','78.34',62.67,5.00,1.00,'65.8','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(781,'53','126','31','4500',3600.00,5.00,1.00,'3780','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(782,'53','127','31','4500',3600.00,5.00,1.00,'3780','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(783,'53','128','31','4500',3600.00,5.00,1.00,'3780','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(784,'53','129','31','4500',3600.00,5.00,1.00,'3780','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(785,'53','130','31','4500',3600.00,5.00,1.00,'3780','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(786,'53','131','31','4500',3600.00,5.00,1.00,'3780','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(787,'54','133','31','7355',5884.00,5.00,1.00,'6178.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(788,'54','134','31','7355',5884.00,5.00,1.00,'6178.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(789,'54','135','31','7355',5884.00,5.00,1.00,'6178.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(790,'54','136','31','7355',5884.00,5.00,1.00,'6178.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(791,'54','137','31','7355',5884.00,5.00,1.00,'6178.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(792,'54','57','31','7355',5884.00,5.00,1.00,'6178.2','active','2026-11-02 01:15:46','2026-11-02 01:15:46'),
(793,'54','133','28','410',328.00,5.00,1.00,'344.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(794,'54','134','28','410',328.00,5.00,1.00,'344.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(795,'54','135','28','410',328.00,5.00,1.00,'344.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(796,'54','136','28','410',328.00,5.00,1.00,'344.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(797,'54','137','28','410',328.00,5.00,1.00,'344.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(798,'54','57','28','410',328.00,5.00,1.00,'344.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(799,'54','133','27','218.34',174.67,5.00,1.00,'183.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(800,'54','134','27','218.34',174.67,5.00,1.00,'183.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(801,'54','135','27','218.34',174.67,5.00,1.00,'183.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(802,'54','136','27','218.34',174.67,5.00,1.00,'183.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(803,'54','137','27','218.34',174.67,5.00,1.00,'183.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(804,'54','57','27','218.34',174.67,5.00,1.00,'183.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(805,'54','133','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(806,'54','134','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(807,'54','135','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(808,'54','136','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(809,'54','137','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(810,'54','57','26','110',88.00,5.00,1.00,'92.4','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(811,'55','184','28','1225',980.00,5.00,1.00,'1029','active','2026-11-02 01:15:47','2026-11-02 01:15:47'),
(812,'56','149','31','8260',6608.00,5.00,1.00,'6938.4','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(813,'56','38','31','8260',6608.00,5.00,1.00,'6938.4','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(814,'56','150','31','8260',6608.00,5.00,1.00,'6938.4','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(815,'56','151','31','8260',6608.00,5.00,1.00,'6938.4','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(816,'56','152','31','8260',6608.00,5.00,1.00,'6938.4','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(817,'56','153','31','8260',6608.00,5.00,1.00,'6938.4','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(818,'56','149','28','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(819,'56','38','28','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(820,'56','150','28','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(821,'56','151','28','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(822,'56','152','28','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(823,'56','153','28','433.34',346.67,5.00,1.00,'364','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(824,'57','98','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(825,'57','156','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(826,'57','111','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(827,'57','157','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(828,'57','158','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:48','2026-11-02 01:15:48'),
(829,'57','159','31','7457.62',5966.10,5.00,1.00,'6264.41','active','2026-11-02 01:15:48','2026-11-02 01:15:48');
/*!40000 ALTER TABLE `product_attributes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `review` text DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_reviews`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `product_volume_slabs`
--

DROP TABLE IF EXISTS `product_volume_slabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_volume_slabs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `seller_user_id` bigint(20) unsigned NOT NULL,
  `min_quantity` int(11) NOT NULL,
  `max_quantity` int(11) DEFAULT NULL,
  `discount_type` enum('percentage','fixed_unit_price') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_volume_slabs_product_id_status_index` (`product_id`,`status`),
  KEY `product_volume_slabs_seller_user_id_product_id_index` (`seller_user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_volume_slabs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `product_volume_slabs` WRITE;
/*!40000 ALTER TABLE `product_volume_slabs` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_volume_slabs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `mid` int(11) DEFAULT NULL,
  `cid` int(11) DEFAULT NULL,
  `sid` int(11) DEFAULT NULL,
  `maincategory_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `subcategory_id` bigint(20) unsigned DEFAULT NULL,
  `brand_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `oldprice` decimal(8,2) DEFAULT NULL,
  `capacity` varchar(255) DEFAULT NULL,
  `colorcode` varchar(255) DEFAULT NULL,
  `colorimage` varchar(255) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `assurance` varchar(255) DEFAULT NULL,
  `highlights` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `multipleimages` text DEFAULT NULL,
  `shadecard` text DEFAULT NULL,
  `hsncode` varchar(255) DEFAULT NULL,
  `tax` decimal(8,2) DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `idx_products_user_id` (`user_id`),
  KEY `idx_products_category_id` (`category_id`),
  KEY `idx_products_brand_id` (`brand_id`),
  KEY `idx_products_status` (`status`),
  KEY `idx_products_cat_status` (`category_id`,`status`),
  KEY `idx_products_brand_cat` (`brand_id`,`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(28,623779,NULL,NULL,NULL,1,8,0,19,3,'METALLIC EMULSION  PAINTS','metallic-emulsion-paints-6320','METALLIC EMULSION  PAINTS (Creation)','product/bulk_metallic_emulsion_paints.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine METALLIC EMULSION  PAINTS by CREATION at wholesale factory price Rs.89.33. Available in 100 ML, 200 ML, 500 ML, 1 Ltr, 4 Ltr, 10 Ltr, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_metallic_emulsion_paints.png\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:20','2026-11-02 01:17:36'),
(29,307468,NULL,NULL,NULL,1,9,0,19,3,'CREATION PLATINUM HI-GLOSS EMULSION','creation-platinum-hi-gloss-emulsion-76518','CREATION PLATINUM HI-GLOSS EMULSION (Creation)','product/bulk_creation_platinum_hi_gloss_emulsion.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine CREATION PLATINUM HI-GLOSS EMULSION by CREATION at wholesale factory price Rs.329.33. Available in 1 Ltr, 4 Ltr, 20 Ltr, 10 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_creation_platinum_hi_gloss_emulsion.jpg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:21','2026-11-02 01:15:33'),
(30,838395,NULL,NULL,NULL,1,10,0,19,3,'INTERIOR EMULSION WALL PRIMER','interior-emulsion-wall-primer-68744','INTERIOR EMULSION WALL PRIMER (Creation)','product/bulk_interior_emulsion_wall_primer.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine INTERIOR EMULSION WALL PRIMER by CREATION at wholesale factory price Rs.114.67. Available in 4 Ltr, 1 Ltr, 10 Ltr, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_interior_emulsion_wall_primer.jpg\"]',NULL,'3209',18.00,'Active','2026-11-02 00:57:21','2026-11-02 01:17:36'),
(31,362759,NULL,NULL,NULL,1,11,0,19,3,'EXTERIOR /INTERIOR WALL PRIMER','exterior-interior-wall-primer-42908','EXTERIOR /INTERIOR WALL PRIMER (Creation)','product/bulk_exterior_interior_wall_primer.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine EXTERIOR /INTERIOR WALL PRIMER by CREATION+ at wholesale factory price Rs.109.33. Available in 4 Ltr, 1 Ltr, 10 Ltr, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_exterior_interior_wall_primer.jpg\"]',NULL,'3209',18.00,'Active','2026-11-02 00:57:21','2026-11-02 01:17:36'),
(32,558434,NULL,NULL,NULL,1,11,0,19,3,'EXTERIOR WALL PRIMER','exterior-wall-primer-17851','EXTERIOR WALL PRIMER (Creation)','product/bulk_exterior_wall_primer.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine EXTERIOR WALL PRIMER by CREATION at wholesale factory price Rs.136. Available in 4 Ltr, 1 Ltr, 10 Ltr, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_exterior_wall_primer.jpg\"]',NULL,'3209',18.00,'Active','2026-11-02 00:57:21','2026-11-02 01:17:37'),
(33,323273,NULL,NULL,NULL,1,27,0,19,3,'PLATINUM HIGH GLOSS BASE - CPH-21','platinum-high-gloss-base---cph-21-73015','PLATINUM HIGH GLOSS BASE - CPH-21 (Creation)','product/bulk_platinum_high_gloss_base_cph_21.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine PLATINUM HIGH GLOSS BASE - CPH-21 by PLATINUM HIGH-GLOSS BASE at wholesale factory price Rs.297.33. Available in 900 ML BASES, 3.6 LTR BASES, 9 LTR BASES, 18 LTR BASES. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_platinum_high_gloss_base_cph_21.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:21','2026-11-02 01:17:37'),
(34,812146,NULL,NULL,NULL,1,27,0,20,3,'GOLD LUXURY SILK EMULSION - BASE 11','gold-luxury-silk-emulsion---base-11-51754','GOLD LUXURY SILK EMULSION - BASE 11 (Creation Gold Luxury Silk Emulsion Base)','product/bulk_gold_luxury_silk_emulsion_base_11.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine GOLD LUXURY SILK EMULSION - BASE 11 by CREATION GOLD LUXURY SILK EMULSION BASE at wholesale factory price Rs.1365.33. Available in 900 ML BASES, 3.6 LTR BASES, 9 LTR BASES, 18 LTR BASES. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_gold_luxury_silk_emulsion_base_11.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:22','2026-11-02 01:17:37'),
(35,218747,NULL,NULL,NULL,1,27,0,19,3,'PLATINUM HIGH GLOSS BASE - CPH-10','platinum-high-gloss-base---cph-10-89575','PLATINUM HIGH GLOSS BASE - CPH-10 (Creation)','product/bulk_platinum_high_gloss_base_cph_10.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine PLATINUM HIGH GLOSS BASE - CPH-10 by PLATINUM HIGH-GLOSS BASE at wholesale factory price Rs.284. Available in 900 ML BASES, 3.6 LTR BASES, 9 LTR BASES, 18 LTR BASES. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_platinum_high_gloss_base_cph_10.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:22','2026-11-02 01:17:38'),
(36,839168,NULL,NULL,NULL,1,27,0,19,3,'CREATION GOLD LUXERY SILK EML','creation-gold-luxery-silk-eml-56009','CREATION GOLD LUXERY SILK EML (Creation)','product/bulk_creation_gold_luxery_silk_eml.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine CREATION GOLD LUXERY SILK EML by PLATINUM HIGH-GLOSS BASE at wholesale factory price Rs.378.67. Available in 1 Ltr, 4 Ltr, 10 Ltr, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_creation_gold_luxery_silk_eml.jpg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:23','2026-11-02 01:15:35'),
(37,920614,NULL,NULL,NULL,1,12,0,21,17,'2K PU METAL FINISH','2k-pu-metal-finish-6893','2K PU METAL FINISH (Shrirang)','product/bulk_2k_pu_metal_finish.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine 2K PU METAL FINISH by SHRIRANG at wholesale factory price Rs.336. Available in 25LTR, 5LTR, 1LTR. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_2k_pu_metal_finish.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:23','2026-11-02 01:17:38'),
(38,249096,NULL,NULL,NULL,1,18,0,19,3,'CREATION INTERIOR EMULSION','creation-interior-emulsion-76938','CREATION INTERIOR EMULSION (Creation)','product/bulk_creation_interior_emulsion.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine CREATION INTERIOR EMULSION by CREATION at wholesale factory price Rs.142.67. Available in 4 Ltr, 1 Ltr, 10 Ltr, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_creation_interior_emulsion.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:23','2026-11-02 01:17:39'),
(39,601995,NULL,NULL,NULL,1,1,0,21,17,'1K PU COATINGS','1k-pu-coatings-3613','1K PU COATINGS (Shrirang)','product/bulk_1k_pu_coatings.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine 1K PU COATINGS by SHRIRANG at wholesale factory price Rs.100. Available in 1LTR, 20LTR, 4LTR, 500 ML x 4 Pc, 200 ML x 12 Pc. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_1k_pu_coatings.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:23','2026-11-02 01:17:39'),
(40,380098,NULL,NULL,NULL,1,14,0,19,3,'DISTEMPER POUCH','distemper-pouch-45069','DISTEMPER POUCH (Creation)','product/bulk_distemper_pouch.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine DISTEMPER POUCH by CREATION at wholesale factory price Rs.49.33. Available in 1 kg x 20 Pc. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_distemper_pouch.png\"]',NULL,'3214',18.00,'Active','2026-11-02 00:57:23','2026-11-02 01:17:39'),
(41,919985,NULL,NULL,NULL,1,15,0,21,17,'PU WOOD','pu-wood-81990','PU WOOD (Shrirang)','product/bulk_pu_wood.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine PU WOOD by SHRIRANG at wholesale factory price Rs.613.33. Available in 25LTR, 5LTR, 1LTR. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_pu_wood.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:23','2026-11-02 01:17:40'),
(42,264685,NULL,NULL,NULL,1,9,0,19,3,'SHINE EMULSION','shine-emulsion-50759','SHINE EMULSION (Creation)','product/bulk_shine_emulsion.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine SHINE EMULSION by CREATION at wholesale factory price Rs.296. Available in 4 Ltr, 1 Ltr, 10 Ltr, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_shine_emulsion.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:24','2026-11-02 01:17:40'),
(43,691206,NULL,NULL,NULL,1,16,0,19,3,'EXTERIOR EMULAION WATER BASED','exterior-emulaion-water-based-47606','EXTERIOR EMULAION WATER BASED (Creation)','product/bulk_exterior_emulaion_water_based.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine EXTERIOR EMULAION WATER BASED by CREATION at wholesale factory price Rs.68. Available in 4 Ltr, 1 Ltr, 20 Ltr, 10 Ltr, 500 ML, 200 ML. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_exterior_emulaion_water_based.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:24','2026-11-02 01:17:40'),
(44,828731,NULL,NULL,NULL,1,17,0,19,3,'CREATION ACRYLIC DISTEMPER','creation-acrylic-distemper-73548','CREATION ACRYLIC DISTEMPER (Creation)','product/bulk_creation_acrylic_distemper.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine CREATION ACRYLIC DISTEMPER by CREATION at wholesale factory price Rs.149. Available in 2 KG, 5 KG, 10 KG, 20 KG. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_creation_acrylic_distemper.png\"]',NULL,'3214',18.00,'Active','2026-11-02 00:57:30','2026-11-02 01:15:42'),
(45,137853,NULL,NULL,NULL,1,18,0,19,3,'CP ECO- INTERIOR EMLUSION','cp-eco--interior-emlusion-3505','CP ECO- INTERIOR EMLUSION (Creation)','product/bulk_cp_eco_interior_emlusion.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine CP ECO- INTERIOR EMLUSION by CREATION at wholesale factory price Rs.112. Available in 4 Ltr, 1 Ltr, 20 Ltr, 10 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_cp_eco_interior_emlusion.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:30','2026-11-02 01:17:40'),
(46,896221,NULL,NULL,NULL,1,1,0,21,17,'2K PU INTERIOR FINISHK','2k-pu-interior-finishk-23034','2K PU INTERIOR FINISHK (Shrirang)','product/bulk_2k_pu_interior_finishk.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine 2K PU INTERIOR FINISHK by SHRIRANG at wholesale factory price Rs.513.33. Available in 25LTR, 5LTR, 1LTR. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_2k_pu_interior_finishk.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:30','2026-11-02 01:17:41'),
(47,208442,NULL,NULL,NULL,1,18,0,19,3,'CREATION INTERIOR SHEEN EMULSION','creation-interior-sheen-emulsion-89975','CREATION INTERIOR SHEEN EMULSION (Creation)','product/bulk_creation_interior_sheen_emulsion.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine CREATION INTERIOR SHEEN EMULSION by CREATION at wholesale factory price Rs.221. Available in 4 Ltr, 1 Ltr, 10 Ltr, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_creation_interior_sheen_emulsion.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:30','2026-11-02 01:17:41'),
(48,105278,NULL,NULL,NULL,1,1,0,22,16,'AUTOCOATS ENAMEL','autocoats-enamel-51781','AUTOCOATS ENAMEL (Autocoats Enamel)','product/bulk_autocoats_enamel.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine AUTOCOATS ENAMEL by AUTOCOATS ENAMEL at wholesale factory price Rs.90.4. Available in 20 LTR, 4 LTR, 1 LTR, 500 ML, 200 ML, 20 Ltr, 4 Ltr, 1 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_autocoats_enamel.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:30','2026-11-02 01:17:41'),
(49,235307,NULL,NULL,NULL,1,1,0,23,17,'BRIGHT SYNTHETIC ENAMEL','bright-synthetic-enamel-25870','BRIGHT SYNTHETIC ENAMEL (Bright)','product/bulk_bright_synthetic_enamel.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine BRIGHT SYNTHETIC ENAMEL by BRIGHT at wholesale factory price Rs.310.73. Available in 20 LTR, 1 LTR. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_bright_synthetic_enamel.png\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:31','2026-11-02 01:17:42'),
(50,876039,NULL,NULL,NULL,1,1,0,21,17,'SHRIRANG N.C. PAINT','shrirang-n-c-paint-86140','SHRIRANG N.C. PAINT (Shrirang)','product/bulk_shrirang_n_c_paint.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine SHRIRANG N.C. PAINT by SHRIRANG at wholesale factory price Rs.88. Available in 20 LTR, 4 LTR, 1 LTR, 500 ML, 200 ML. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_shrirang_n_c_paint.png\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:32','2026-11-02 01:17:43'),
(51,702836,NULL,NULL,NULL,1,21,0,21,17,'MELAMINE WOOD FINISH','melamine-wood-finish-41710','MELAMINE WOOD FINISH (Shrirang)','product/bulk_melamine_wood_finish.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine MELAMINE WOOD FINISH by SHRIRANG at wholesale factory price Rs.80. Available in 20 Ltr, 4 Ltr, 1 Ltr, 500 ML, 200 ML. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_melamine_wood_finish.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:35','2026-11-02 01:17:43'),
(52,957715,NULL,NULL,NULL,1,22,0,24,3,'SYNTHETIC ENAMEL OIL PAINT','synthetic-enamel-oil-paint-82332','SYNTHETIC ENAMEL OIL PAINT (Parijat)','product/bulk_synthetic_enamel_oil_paint.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine SYNTHETIC ENAMEL OIL PAINT by PARIJAT at wholesale factory price Rs.6133.33. Available in 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_synthetic_enamel_oil_paint.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:35','2026-11-02 01:17:44'),
(53,537060,NULL,NULL,NULL,1,23,0,21,17,'SHRIRANG','shrirang-77042','SHRIRANG (Shrirang)','product/bulk_shrirang.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine SHRIRANG by SHRIRANG at wholesale factory price Rs.62.67. Available in 4 Ltr, 1 Ltr, 500 ML, 200 ML, 20 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_shrirang.jpg\"]',NULL,'3209',18.00,'Active','2026-11-02 00:57:37','2026-11-02 01:15:46'),
(54,657581,NULL,NULL,NULL,1,24,0,21,17,'FURNITURE ENAMEL','furniture-enamel-1711','FURNITURE ENAMEL (Shrirang)','product/bulk_furniture_enamel.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine FURNITURE ENAMEL by SHRIRANG at wholesale factory price Rs.88. Available in 20 Ltr, 4 Ltr, 1 Ltr, 500 ML, 200 ML. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_furniture_enamel.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:38','2026-11-02 01:17:44'),
(55,716514,NULL,NULL,NULL,1,25,0,21,17,'2K PU PAINTS','2k-pu-paints-38202','2K PU PAINTS (Shrirang)','product/bulk_2k_pu_paints.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine 2K PU PAINTS by SHRIRANG at wholesale factory price Rs.980. Available in 1 LTR. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_2k_pu_paints.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:40','2026-11-02 01:15:47'),
(56,179873,NULL,NULL,NULL,1,26,0,21,17,'HAMMERTONE FINISH','hammertone-finish-76829','HAMMERTONE FINISH (Shrirang)','product/bulk_hammertone_finish.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine HAMMERTONE FINISH by SHRIRANG at wholesale factory price Rs.346.67. Available in 20 Ltr, 4 Ltr, 1 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_hammertone_finish.png\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:44','2026-11-02 01:15:48'),
(57,728996,NULL,NULL,NULL,1,1,0,25,17,'BRIGHT AUTO PAINT','bright-auto-paint-98553','BRIGHT AUTO PAINT (Bright Auto Paint)','product/bulk_bright_auto_paint.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Buy genuine BRIGHT AUTO PAINT by Bright Auto Paint at wholesale factory price Rs.310.73. Available in 20 Ltr, 1 Ltr. Direct manufacturer supply, GST invoice & Pan-India dispatch from Apni Factory.','[\"product\\/bulk_bright_auto_paint.jpeg\"]',NULL,'3208',18.00,'Active','2026-11-02 00:57:45','2026-11-02 01:17:45');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `productshades`
--

DROP TABLE IF EXISTS `productshades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `productshades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` varchar(255) NOT NULL,
  `shade_cards_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productshades`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `productshades` WRITE;
/*!40000 ALTER TABLE `productshades` DISABLE KEYS */;
/*!40000 ALTER TABLE `productshades` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `viewon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profiles`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;
INSERT INTO `profiles` VALUES
(1,'Whatsapp','+919876543210','app','2026-10-31 05:35:44','2026-10-31 05:35:44');
/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `rfq_quotes`
--

DROP TABLE IF EXISTS `rfq_quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rfq_quotes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rfq_request_id` bigint(20) unsigned NOT NULL,
  `seller_user_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `offered_unit_price` decimal(12,2) NOT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `gst_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_charges` decimal(12,2) NOT NULL DEFAULT 0.00,
  `lead_time_days` int(11) NOT NULL DEFAULT 7,
  `valid_until` date NOT NULL,
  `seller_notes` text DEFAULT NULL,
  `payment_terms` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected','expired') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rfq_quotes_rfq_request_id_seller_user_id_index` (`rfq_request_id`,`seller_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rfq_quotes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `rfq_quotes` WRITE;
/*!40000 ALTER TABLE `rfq_quotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `rfq_quotes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `rfq_requests`
--

DROP TABLE IF EXISTS `rfq_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rfq_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rfq_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `seller_user_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `product_title` varchar(255) DEFAULT NULL,
  `target_quantity` decimal(12,2) NOT NULL,
  `unit_of_measure` varchar(50) NOT NULL DEFAULT 'pcs',
  `target_unit_price` decimal(12,2) DEFAULT NULL,
  `delivery_pincode` varchar(10) DEFAULT NULL,
  `delivery_deadline` date DEFAULT NULL,
  `technical_specifications` text DEFAULT NULL,
  `status` enum('draft','submitted','quoted','accepted','rejected','expired') NOT NULL DEFAULT 'submitted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rfq_requests_rfq_number_unique` (`rfq_number`),
  KEY `rfq_requests_customer_id_status_index` (`customer_id`,`status`),
  KEY `rfq_requests_seller_user_id_status_index` (`seller_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rfq_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `rfq_requests` WRITE;
/*!40000 ALTER TABLE `rfq_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `rfq_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `shade_cards`
--

DROP TABLE IF EXISTS `shade_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shade_cards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subcategoryid` bigint(20) unsigned DEFAULT NULL,
  `maincategory_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `hexcode` varchar(100) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `adminmsg` text DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shade_cards`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `shade_cards` WRITE;
/*!40000 ALTER TABLE `shade_cards` DISABLE KEYS */;
INSERT INTO `shade_cards` VALUES
(1,NULL,1,1,NULL,'Super White','#FFFFFF',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(2,NULL,1,1,NULL,'Royal Blue','#1E3A8A',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(3,NULL,1,1,NULL,'Sunset Orange','#EA580C',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(4,NULL,1,1,NULL,'Emerald Green','#059669',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(5,NULL,1,5,NULL,'Pearl Ivory','#FFFBEB',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(6,NULL,1,5,NULL,'Mint Whisper','#ECFDF5',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(7,NULL,1,5,NULL,'Coral Glow','#FFE4E6',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(8,NULL,2,2,NULL,'Bleached White','#FAFAFA',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(9,NULL,2,2,NULL,'Natural Beige','#E5D3B3',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(10,NULL,2,2,NULL,'Indigo Blue','#1E293B',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(11,NULL,2,3,NULL,'Royal Gold','#D97706',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(12,NULL,2,3,NULL,'Ruby Maroon','#881337',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(13,NULL,2,3,NULL,'Peacock Teal','#0F766E',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(14,NULL,3,4,NULL,'Crisp White','#FFFFFF',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(15,NULL,3,4,NULL,'Sky Blue','#38BDF8',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(16,NULL,3,4,NULL,'Charcoal Grey','#334155',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(17,NULL,3,6,NULL,'Deep Crimson','#991B1B',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(18,NULL,3,6,NULL,'Mustard Ochre','#CA8A04',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(19,NULL,3,6,NULL,'Emerald Flora','#047857',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(20,NULL,4,7,NULL,'Pro Standard','#475569',NULL,NULL,'Active','2026-08-24 04:33:34','2026-08-24 04:33:34'),
(21,NULL,1,1,15,'Jet Black','#111827','',NULL,'Active','2026-10-31 01:49:00','2026-10-31 01:49:00'),
(22,NULL,2,2,3,'Optic White','#F8F9FA',NULL,NULL,'Active','2026-11-02 00:29:36','2026-11-02 00:29:36'),
(23,NULL,3,4,3,'Navy Blue','#001F3F',NULL,NULL,'Active','2026-11-02 00:29:36','2026-11-02 00:29:36'),
(24,NULL,1,1,3,'Crimson Red','#DC3232',NULL,NULL,'Active','2026-11-02 00:30:44','2026-11-02 00:30:44'),
(34,NULL,1,1,3,'Copper','#D1793B',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(35,NULL,1,1,3,'Silver','#AAA9AD',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(36,NULL,1,1,3,'Signal Red','#f59da0',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(37,NULL,1,1,3,'24 Carat Gold','#efdeb1',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(38,NULL,1,1,3,'Bronze','#b08668',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(39,NULL,1,1,3,'Oxford Blue','#0256ac',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(40,NULL,1,1,3,'Dark Green','#046976',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(41,NULL,1,1,3,'Purple','#7e427e',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(42,NULL,1,1,3,'Magenta','#C21870',NULL,NULL,'Active','2026-11-02 00:57:20','2026-11-02 00:57:20'),
(43,NULL,1,1,3,'White','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:21','2026-11-02 00:57:21'),
(44,NULL,1,1,3,'Base White','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:21','2026-11-02 00:57:21'),
(45,NULL,1,1,3,'Traffic White','#F2F0E8',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(46,NULL,1,1,3,'Pure White','#F8F8F5',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(47,NULL,1,1,3,'Ivory','#E8DCC3',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(48,NULL,1,1,3,'Light Ivory','#E7D7B5',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(49,NULL,1,1,3,'Signal Yellow','#F5C400',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(50,NULL,1,1,3,'Golden Yellow','#E9AE18',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(51,NULL,1,1,3,'Traffic Orange','#E85B26',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(52,NULL,1,1,3,'Flame Red','#C83232',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(53,NULL,1,1,3,'Signal White','#F4F4F4',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(54,NULL,1,1,3,'Off\\u002DWhite','#FAF0E6',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(55,NULL,1,1,3,'Cream','#FFFDD0',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(56,NULL,1,1,3,'Alabaster','#F2F0EA',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(57,NULL,1,1,3,'Smoke Grey','#848482',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(58,NULL,1,1,3,'Graphite Grey','#383838',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(59,NULL,1,1,3,'Slate Grey','#708090',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(60,NULL,1,1,3,'Aquamarine','#3ebbaa',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(61,NULL,1,1,3,'Electric Blue','#449ac5',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(62,NULL,1,1,3,'Daffodil','#ffff31',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(63,NULL,1,1,3,'Jaipuri','#fda29f',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(64,NULL,1,1,3,'Pale Cream','#f7f4b0',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(65,NULL,1,1,3,'Merrie Pink','#f9cde4',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(66,NULL,1,1,3,'Pastel Green','#6cc76a',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(67,NULL,1,1,3,'Light Green','#92d288',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(68,NULL,1,1,3,'White Ash','#D8C8A9',NULL,NULL,'Active','2026-11-02 00:57:23','2026-11-02 00:57:23'),
(69,NULL,1,1,3,'Light Oak','#CBA875',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(70,NULL,1,1,3,'Golden Oak','#B8894F',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(71,NULL,1,1,3,'Honey Oak','#C79554',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(72,NULL,1,1,3,'Natural Teak','#B9824D',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(73,NULL,1,1,3,'Light Teak','#D0A06A',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(74,NULL,1,1,3,'Golden Teak','#B7773D',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(75,NULL,1,1,3,'Medium Teak','#A86C3F',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(76,NULL,1,1,3,'Dark Teak','#80502F',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(77,NULL,1,1,3,'Terracota','#C65D3A',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(78,NULL,1,1,3,'Mint Green','#98FF98',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(79,NULL,1,1,3,'Black','#000000',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(80,NULL,1,1,3,'Sporty Pink','#d27dbe',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(81,NULL,1,1,3,'Nagpuri Orange','#f08787',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(82,NULL,1,1,3,'Lemon Souffle','#898e02',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(83,NULL,1,1,3,'Jolly Holly','#03671c',NULL,NULL,'Active','2026-11-02 00:57:24','2026-11-02 00:57:24'),
(84,NULL,1,1,3,'Mughal Green','#306030',NULL,NULL,'Active','2026-11-02 00:57:25','2026-11-02 00:57:25'),
(85,NULL,1,1,3,'Sporty Yellow','#EBD307',NULL,NULL,'Active','2026-11-02 00:57:25','2026-11-02 00:57:25'),
(86,NULL,1,1,3,'NAVEY BULE','#000080',NULL,NULL,'Active','2026-11-02 00:57:25','2026-11-02 00:57:25'),
(87,NULL,1,1,3,'Rust','#B7410e',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(88,NULL,1,1,3,'Coffee','#6f4e37',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(89,NULL,1,1,3,'Candy','#ed7855',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(90,NULL,1,1,3,'Wild Lilac','#b09acb',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(91,NULL,1,1,3,'Deep Aquamarine','#008b78',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(92,NULL,1,1,3,'Snow White','#FAFAF7',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(93,NULL,1,1,3,'Warm White','#F3EDE2',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(94,NULL,1,1,3,'Pearl White','#EDE7DD',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(95,NULL,1,1,3,'Champagne','#DCC9A5',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(96,NULL,1,1,3,'Beige','#D8C3A5',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(97,NULL,1,1,3,'Sand Beige','#C9B18D',NULL,NULL,'Active','2026-11-02 00:57:30','2026-11-02 00:57:30'),
(98,NULL,1,1,3,'Lemon yellow','#f5d21f',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(99,NULL,1,1,3,'Golden brown','#8a5a2b',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(100,NULL,1,1,3,'Goden yellow','#eaa11a',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(101,NULL,1,1,3,'P. O. red','#c1272d',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(102,NULL,1,1,3,'ABG White','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(103,NULL,1,1,3,'Off White','#faf9f6',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(104,NULL,1,1,3,'diamond white','#f3f2e9',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(105,NULL,1,1,3,'hm shell white','#f0eee5',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(106,NULL,1,1,3,'brilliant white','#f9f8f6',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(107,NULL,1,1,3,'milky white','#f5f0e5',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(108,NULL,1,1,3,'Dost cream','#e5cca0',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(109,NULL,1,1,3,'New superio white','#f4f3eb',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(110,NULL,1,1,3,'Casablanca white','#f2e8d5',NULL,NULL,'Active','2026-11-02 00:57:31','2026-11-02 00:57:31'),
(111,NULL,1,1,3,'Mahindra red','#b3262c',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(112,NULL,1,1,3,'taxi yellow','#e7c32d',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(113,NULL,1,1,3,'Massey red','#a72225',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(114,NULL,1,1,3,'Clear Varnish','#d8a35d',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(115,NULL,1,1,3,'Copal Varnish','#8b5a2b',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(116,NULL,1,1,3,'Snow \\u002D 201','#BCE4B2',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(117,NULL,1,1,3,'Jade \\u002D 202','#8AD5F2',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(118,NULL,1,1,3,'Dove \\u002D 203','#F7E7DA',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(119,NULL,1,1,3,'Ivory \\u002D 204','#F8F1C7',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(120,NULL,1,1,3,'Light Cream \\u002D 205','#F7E3C2',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(121,NULL,1,1,3,'Jasmine Light \\u002D 206','#F1DBB2',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(122,NULL,1,1,3,'Cream \\u002D 207','#FCE1B4',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(123,NULL,1,1,3,'Cascade Green \\u002D 208','#7BCC7D',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(124,NULL,1,1,3,'Mauve \\u002D 209','#BCA5D9',NULL,NULL,'Active','2026-11-02 00:57:35','2026-11-02 00:57:35'),
(125,NULL,1,1,3,'White Primer','#fffefe',NULL,NULL,'Active','2026-11-02 00:57:37','2026-11-02 00:57:37'),
(126,NULL,1,1,3,'Red Oxide Glossy','#982717',NULL,NULL,'Active','2026-11-02 00:57:37','2026-11-02 00:57:37'),
(127,NULL,1,1,3,'Black Japan','#1f202e',NULL,NULL,'Active','2026-11-02 00:57:37','2026-11-02 00:57:37'),
(128,NULL,1,1,3,'Rubber Seal Compound','#000000',NULL,NULL,'Active','2026-11-02 00:57:37','2026-11-02 00:57:37'),
(129,NULL,1,1,3,'Surfacer Primer Grey','#c4c4c4',NULL,NULL,'Active','2026-11-02 00:57:37','2026-11-02 00:57:37'),
(130,NULL,1,1,3,'Zinc Chromate Yellow Primer','#dec82c',NULL,NULL,'Active','2026-11-02 00:57:37','2026-11-02 00:57:37'),
(131,NULL,1,1,3,'Surfacer Primer White','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:37','2026-11-02 00:57:37'),
(132,NULL,1,1,3,'Nickel Grey','#c1bcaf',NULL,NULL,'Active','2026-11-02 00:57:38','2026-11-02 00:57:38'),
(133,NULL,1,1,3,'Tata Grey','#22424e',NULL,NULL,'Active','2026-11-02 00:57:38','2026-11-02 00:57:38'),
(134,NULL,1,1,3,'New T. A. Grey','#8f9695',NULL,NULL,'Active','2026-11-02 00:57:38','2026-11-02 00:57:38'),
(135,NULL,1,1,3,'Steel Grey','#a0a3a2',NULL,NULL,'Active','2026-11-02 00:57:38','2026-11-02 00:57:38'),
(136,NULL,1,1,3,'Light Grey','#d3d3d3',NULL,NULL,'Active','2026-11-02 00:57:38','2026-11-02 00:57:38'),
(137,NULL,1,1,3,'Sandstone','#c9ae74',NULL,NULL,'Active','2026-11-02 00:57:38','2026-11-02 00:57:38'),
(138,NULL,1,1,3,'Olive Green','#808000',NULL,NULL,'Active','2026-11-02 00:57:38','2026-11-02 00:57:38'),
(139,NULL,1,1,3,'MARUTI PASSION RED','#B51224',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(140,NULL,1,1,3,'MARUTI BLAZING RED','#D71920',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(141,NULL,1,1,3,'MARUTI FIRE BRICK RED','#B22222',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(142,NULL,1,1,3,'MARUTI BRIGHT RED \\u002D SOLID','#D71920',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(143,NULL,1,1,3,'MARUTI SILKY SILVER','#A7A9AC',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(144,NULL,1,1,3,'MARUTI GRANITE GREY','#555B60',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(145,NULL,1,1,3,'MARUTI PREMIUM SILVER','#B8BABD',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(146,NULL,1,1,3,'MARUTI GLISTRING GREY','#777A7D',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(147,NULL,1,1,3,'MARUTI AZURE GREY','#687783',NULL,NULL,'Active','2026-11-02 00:57:40','2026-11-02 00:57:40'),
(148,NULL,1,1,3,'Silver Ash','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:44','2026-11-02 00:57:44'),
(149,NULL,1,1,3,'Opal Green','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(150,NULL,1,1,3,'Pale Gold','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(151,NULL,1,1,3,'Dark Grey','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(152,NULL,1,1,3,'Crimson','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(153,NULL,1,1,3,'Deep Blue','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(154,NULL,1,1,3,'Lilac','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(155,NULL,1,1,3,'Mid Grey','#ffffff',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(156,NULL,1,1,3,'Dawn Glow','#E87D38',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(157,NULL,1,1,3,'ITL Blue','#1F5DA0',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(158,NULL,1,1,3,'Light Blue','#75AADB',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(159,NULL,1,1,3,'Met. Aluminium','#C0C6C9',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(160,NULL,1,1,3,'H.M. Shell White','#F0EEE5',NULL,NULL,'Active','2026-11-02 00:57:45','2026-11-02 00:57:45'),
(161,NULL,1,1,3,'Off\\u002DWhite','#FAF0E6',NULL,NULL,'Active','2026-11-02 01:05:10','2026-11-02 01:05:10'),
(162,NULL,1,1,3,'Snow \\u002D 201','#BCE4B2',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(163,NULL,1,1,3,'Jade \\u002D 202','#8AD5F2',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(164,NULL,1,1,3,'Dove \\u002D 203','#F7E7DA',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(165,NULL,1,1,3,'Ivory \\u002D 204','#F8F1C7',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(166,NULL,1,1,3,'Light Cream \\u002D 205','#F7E3C2',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(167,NULL,1,1,3,'Jasmine Light \\u002D 206','#F1DBB2',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(168,NULL,1,1,3,'Cream \\u002D 207','#FCE1B4',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(169,NULL,1,1,3,'Cascade Green \\u002D 208','#7BCC7D',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(170,NULL,1,1,3,'Mauve \\u002D 209','#BCA5D9',NULL,NULL,'Active','2026-11-02 01:05:14','2026-11-02 01:05:14'),
(171,NULL,1,1,3,'MARUTI BRIGHT RED \\u002D SOLID','#D71920',NULL,NULL,'Active','2026-11-02 01:05:18','2026-11-02 01:05:18'),
(172,NULL,1,1,17,'Off\\u002DWhite','#FAF0E6',NULL,NULL,'Active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(173,NULL,1,1,17,'Off\\u002DWhite','#FAF0E6',NULL,NULL,'Active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(174,NULL,1,1,17,'Off\\u002DWhite','#FAF0E6',NULL,NULL,'Active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(175,NULL,1,1,17,'Off\\u002DWhite','#FAF0E6',NULL,NULL,'Active','2026-11-02 01:15:36','2026-11-02 01:15:36'),
(176,NULL,1,1,17,'Off\\u002DWhite','#FAF0E6',NULL,NULL,'Active','2026-11-02 01:15:37','2026-11-02 01:15:37'),
(177,NULL,1,22,3,'Snow \\u002D 201','#BCE4B2',NULL,NULL,'Active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(178,NULL,1,22,3,'Jade \\u002D 202','#8AD5F2',NULL,NULL,'Active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(179,NULL,1,22,3,'Dove \\u002D 203','#F7E7DA',NULL,NULL,'Active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(180,NULL,1,22,3,'Ivory \\u002D 204','#F8F1C7',NULL,NULL,'Active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(181,NULL,1,22,3,'Light Cream \\u002D 205','#F7E3C2',NULL,NULL,'Active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(182,NULL,1,22,3,'Jasmine Light \\u002D 206','#F1DBB2',NULL,NULL,'Active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(183,NULL,1,22,3,'Cream \\u002D 207','#FCE1B4',NULL,NULL,'Active','2026-11-02 01:15:45','2026-11-02 01:15:45'),
(184,NULL,1,25,17,'MARUTI BRIGHT RED \\u002D SOLID','#D71920',NULL,NULL,'Active','2026-11-02 01:15:47','2026-11-02 01:15:47');
/*!40000 ALTER TABLE `shade_cards` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sizes`
--

DROP TABLE IF EXISTS `sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sizes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `status` enum('Active','Deactive') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sizes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sizes` WRITE;
/*!40000 ALTER TABLE `sizes` DISABLE KEYS */;
/*!40000 ALTER TABLE `sizes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sliders`
--

DROP TABLE IF EXISTS `sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sliders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `screen` varchar(255) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sliders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sliders` WRITE;
/*!40000 ALTER TABLE `sliders` DISABLE KEYS */;
INSERT INTO `sliders` VALUES
(1,'Premium Paints Collection','slider/slider1.png','homescreen','2026-01-01','2030-12-31',NULL,NULL,'Active','2026-10-31 05:32:20','2026-10-31 05:32:20'),
(2,'Exclusive Fabrics Offer','slider/banner1.jpg','homescreen','2026-01-01','2030-12-31',NULL,NULL,'Active','2026-10-31 05:32:20','2026-10-31 05:32:20'),
(3,'New Season Arrivals','slider/slider2.png','homescreen','2026-01-01','2030-12-31',NULL,NULL,'Active','2026-10-31 05:32:20','2026-10-31 05:32:20'),
(4,'Industrial Grade Supplies','slider/banner2.jpg','homescreen','2026-01-01','2030-12-31',NULL,NULL,'Active','2026-10-31 05:32:20','2026-10-31 05:32:20'),
(5,'Category Spotlight','slider/banner.jpg','CategoryScreen','2026-01-01','2030-12-31',NULL,NULL,'Active','2026-10-31 05:32:20','2026-10-31 05:32:20'),
(6,'Company Spotlight','slider/Banner 1.jpg','CompanyScreen','2026-01-01','2030-12-31',NULL,NULL,'Active','2026-10-31 05:32:20','2026-10-31 05:32:20');
/*!40000 ALTER TABLE `sliders` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sub_categories`
--

DROP TABLE IF EXISTS `sub_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mid` bigint(20) unsigned DEFAULT NULL,
  `cid` bigint(20) unsigned DEFAULT NULL,
  `maincategory_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Active','Deactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sub_categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sub_categories` WRITE;
/*!40000 ALTER TABLE `sub_categories` DISABLE KEYS */;
INSERT INTO `sub_categories` VALUES
(1,1,1,1,1,'Premium Enamel','Premium Enamel Paints','default.png','Active','2026-08-22 03:38:23','2026-08-22 03:38:23'),
(2,2,2,2,2,'Plain Cotton','Plain Cotton','default.png','Active','2026-08-22 03:38:23','2026-08-22 03:38:23');
/*!40000 ALTER TABLE `sub_categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tbl_otp`
--

DROP TABLE IF EXISTS `tbl_otp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_otp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `otpon` varchar(255) DEFAULT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_otp`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tbl_otp` WRITE;
/*!40000 ALTER TABLE `tbl_otp` DISABLE KEYS */;
INSERT INTO `tbl_otp` VALUES
(1,'9876543210','1234','2026-10-30 23:29:41','2026-10-30 23:29:41'),
(2,'9999999999','1234','2026-10-30 23:29:41','2026-10-30 23:29:41'),
(3,'customer@apnifactory.local','1234','2026-10-30 23:29:41','2026-10-30 23:29:41'),
(4,'demo@apnifactory.local','1234','2026-10-30 23:29:41','2026-10-30 23:29:41');
/*!40000 ALTER TABLE `tbl_otp` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `topic` varchar(255) DEFAULT NULL,
  `msg` text DEFAULT NULL,
  `adminmsg` text DEFAULT NULL,
  `addby` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('Pending','Completed','Reject') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES
(1,'Inquiry about bulk yarn order','Looking for 500 meters of pure linen fabric in custom beige shade.','Sample sent for review.',NULL,1,'Pending','2026-08-22 03:38:24','2026-08-22 03:38:24');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `transections`
--

DROP TABLE IF EXISTS `transections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `order_no` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `txnid` varchar(255) DEFAULT NULL,
  `txndetail` text DEFAULT NULL,
  `txnresponse` text DEFAULT NULL,
  `txnmethod` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transections`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `transections` WRITE;
/*!40000 ALTER TABLE `transections` DISABLE KEYS */;
INSERT INTO `transections` VALUES
(1,1,'AF20260822-001',1,1,'success','TXN_6a896708345c7','Paid via UPI Test Gateway','SUCCESS','UPI','2026-08-22 03:38:24','2026-08-22 03:38:24'),
(4,1,'AF20260822-001',1,1,'Refunded','REF_6A8C21D7E219E_1','{\"type\":\"refund\",\"amount\":500,\"reason\":\"Automated Test Refund SLA Expired\",\"gateway_success\":false,\"gateway_response\":null,\"timestamp\":\"2026-08-24 10:49:59\"}',NULL,'Refund','2026-08-24 05:19:59','2026-08-24 05:19:59'),
(5,1,'AF20260822-001',1,1,'Refunded','REF_6A8C21F4B636F_1','{\"type\":\"refund\",\"amount\":500,\"reason\":\"Automated Test Refund SLA Expired\",\"gateway_success\":false,\"gateway_response\":null,\"timestamp\":\"2026-08-24 10:50:28\"}',NULL,'Refund','2026-08-24 05:20:28','2026-08-24 05:20:28'),
(6,1,'AF20260822-001',1,1,'Refunded','REF_6A8C220769E06_1','{\"type\":\"refund\",\"amount\":500,\"reason\":\"Automated Test Refund SLA Expired\",\"gateway_success\":false,\"gateway_response\":null,\"timestamp\":\"2026-08-24 10:50:47\"}',NULL,'Refund','2026-08-24 05:20:47','2026-08-24 05:20:47'),
(7,1,'AF20260822-001',1,1,'Refunded','REF_6A9FECC97AF96_1','{\"type\":\"refund\",\"amount\":500,\"reason\":\"Automated Test Refund SLA Expired\",\"gateway_success\":false,\"gateway_response\":null,\"timestamp\":\"2026-09-08 11:08:57\"}',NULL,'Refund','2026-09-08 05:38:57','2026-09-08 05:38:57');
/*!40000 ALTER TABLE `transections` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Super Admin','admin@apnifactory.local',NULL,'$2y$10$klyDvl77/yj0D9JlD/ZU3eoCEc.hHPUfC8TD.QdkoFQV/.5UkkOtG',NULL,'2026-08-22 03:38:23','2026-09-23 00:36:08'),
(2,'Demo Customer','customer@apnifactory.local',NULL,'$2y$10$ML0/CV3qtmIYdjfEL3BFgu3T3OZhEIp19GABsGi9pFukIcAnEmYtK',NULL,'2026-10-30 23:29:41','2026-10-30 23:29:41'),
(3,'Demo Seller','seller@apnifactory.local',NULL,'$2y$10$klyDvl77/yj0D9JlD/ZU3eoCEc.hHPUfC8TD.QdkoFQV/.5UkkOtG',NULL,'2026-08-24 02:49:21','2026-09-23 00:36:08'),
(10,'Apex Coatings & Paints Ltd','apex.coatings@apnifactory.local',NULL,'$2y$10$klyDvl77/yj0D9JlD/ZU3eoCEc.hHPUfC8TD.QdkoFQV/.5UkkOtG',NULL,'2026-08-24 04:33:34','2026-09-23 00:36:08'),
(11,'Surat Silk & Cotton Mills','surat.textiles@apnifactory.local',NULL,'$2y$10$klyDvl77/yj0D9JlD/ZU3eoCEc.hHPUfC8TD.QdkoFQV/.5UkkOtG',NULL,'2026-08-24 04:33:35','2026-09-23 00:36:08'),
(12,'MasterCraft Hardware Mart','mastercraft.tools@apnifactory.local',NULL,'$2y$10$klyDvl77/yj0D9JlD/ZU3eoCEc.hHPUfC8TD.QdkoFQV/.5UkkOtG',NULL,'2026-08-24 04:33:35','2026-09-23 00:36:08'),
(13,'Premier Garments & Apparels','premier.garments@apnifactory.local',NULL,'$2y$10$klyDvl77/yj0D9JlD/ZU3eoCEc.hHPUfC8TD.QdkoFQV/.5UkkOtG',NULL,'2026-08-24 04:33:35','2026-09-23 00:36:08'),
(14,'Apni Factory User','demo@apnifactory.local',NULL,'$2y$10$ML0/CV3qtmIYdjfEL3BFgu3T3OZhEIp19GABsGi9pFukIcAnEmYtK',NULL,'2026-10-30 23:29:41','2026-10-30 23:29:41'),
(15,'UltraTech Industries Seller','ultratech@apnifactory.local',NULL,'$2y$10$gQYsJ7QFFN9ihsIeGme8kO/kLmZ/26HPi2Zzlu43HLRVtH0Xl/BGC',NULL,'2026-10-31 01:49:00','2026-10-31 01:49:00'),
(16,'Autocoats Industrial Finishes','autocoats@apnifactory.local',NULL,'$2y$10$RuxYGBZf63FTSmjiGegG9.iDKGNbM.xDlOvGGNzmQLQBCXtfPjhTO',NULL,'2026-11-02 01:15:31','2026-11-02 01:15:31'),
(17,'Shrirang Automotive Coatings','shrirang@apnifactory.local',NULL,'$2y$10$Kj/85zU43zStYvrv2qJ/y.1thV.ZDhypF5G3XLaGwFOuAMu/IhDT.',NULL,'2026-11-02 01:15:31','2026-11-02 01:15:31');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `wallet`
--

DROP TABLE IF EXISTS `wallet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wallet` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `orderno` varchar(255) DEFAULT NULL,
  `value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `commission` decimal(12,2) NOT NULL DEFAULT 0.00,
  `refundtobuyer` decimal(12,2) NOT NULL DEFAULT 0.00,
  `debit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `type` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `addby` varchar(255) DEFAULT NULL,
  `msg` text DEFAULT NULL,
  `creditcreated` varchar(255) NOT NULL DEFAULT 'N',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `wallet` WRITE;
/*!40000 ALTER TABLE `wallet` DISABLE KEYS */;
INSERT INTO `wallet` VALUES
(1,1,1,'AF20260822-001',2500.00,125.00,0.00,0.00,2375.00,2375.00,2375.00,'credit','credit','system','Order credit for AF20260822-001','N','2026-08-22 03:38:24','2026-08-22 03:38:24'),
(2,1,1,'AF20260822-001',500.00,0.00,500.00,0.00,500.00,500.00,0.00,'refund','refund','system','Refund for Order #AF20260822-001 - Automated Test Refund SLA Expired','N','2026-08-24 05:19:59','2026-08-24 05:19:59'),
(3,1,1,'AF20260822-001',500.00,0.00,500.00,0.00,500.00,500.00,0.00,'refund','refund','system','Refund for Order #AF20260822-001 - Automated Test Refund SLA Expired','N','2026-08-24 05:20:28','2026-08-24 05:20:28'),
(4,1,1,'AF20260822-001',500.00,0.00,500.00,0.00,500.00,500.00,0.00,'refund','refund','system','Refund for Order #AF20260822-001 - Automated Test Refund SLA Expired','N','2026-08-24 05:20:47','2026-08-24 05:20:47'),
(5,1,1,'AF20260822-001',500.00,0.00,500.00,0.00,500.00,500.00,0.00,'refund','refund','system','Refund for Order #AF20260822-001 - Automated Test Refund SLA Expired','N','2026-09-08 05:38:57','2026-09-08 05:38:57');
/*!40000 ALTER TABLE `wallet` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `whatsappmsgs`
--

DROP TABLE IF EXISTS `whatsappmsgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsappmsgs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(255) NOT NULL,
  `templateid` varchar(255) NOT NULL,
  `actionon` varchar(255) NOT NULL,
  `variables` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsappmsgs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `whatsappmsgs` WRITE;
/*!40000 ALTER TABLE `whatsappmsgs` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsappmsgs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `productid` bigint(20) unsigned DEFAULT NULL,
  `userid` bigint(20) unsigned DEFAULT NULL,
  `productimage` varchar(255) DEFAULT NULL,
  `productname` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
INSERT INTO `wishlist` VALUES
(1,30,1,'product/bulk_interior_emulsion_wall_primer.jpg','INTERIOR EMULSION WALL PRIMER',NULL,NULL);
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-11-03 16:48:30
