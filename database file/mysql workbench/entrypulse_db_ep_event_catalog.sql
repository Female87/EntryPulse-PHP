-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: entrypulse_db
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ep_event_catalog`
--

DROP TABLE IF EXISTS `ep_event_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ep_event_catalog` (
  `event_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_artist` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vip_price` decimal(10,2) NOT NULL,
  `lower_price` decimal(10,2) NOT NULL,
  `upper_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`event_id`),
  CONSTRAINT `chk_ep_event_active` CHECK ((`is_active` in (0,1))),
  CONSTRAINT `chk_ep_event_prices` CHECK (((`vip_price` >= 0) and (`lower_price` >= 0) and (`upper_price` >= 0)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ep_event_catalog`
--

LOCK TABLES `ep_event_catalog` WRITE;
/*!40000 ALTER TABLE `ep_event_catalog` DISABLE KEYS */;
INSERT INTO `ep_event_catalog` VALUES ('BD-PPOP','Babydolls','P-Pop Showcase','Skydome, SM North EDSA','P-Pop & OPM',4500.00,3000.00,1200.00,1),('BINI-GB','BINI','Grand BINIverse','Smart Araneta Coliseum','P-Pop & OPM',11195.00,7000.00,2000.00,1),('BM-MNL','Bruno Mars','Live in Manila','Philippine Arena (Bulacan)','International',23850.00,15000.00,5000.00,1),('BP-BP','BLACKPINK','BORN PINK World Tour','Philippine Arena (Bulacan)','International',19400.00,14500.00,6000.00,1),('BSB-DNA','Backstreet Boys','DNA World Tour','SM Mall of Asia Arena','International',18350.00,14000.00,5000.00,1),('BTS-WT','BTS','World Tour','Philippine Arena (Bulacan)','International',25000.00,16000.00,7500.00,1),('EN-FATE','ENHYPEN','FATE World Tour','New Clark City Stadium / MOA','International',17000.00,12500.00,6500.00,1),('F4-MG','F4 (Meteor Garden)','Reunion Concert (Custom)','SM Mall of Asia Arena','International',50000.00,20000.00,6000.00,1),('JMC-XMAS','Jose Mari Chan','Christmas Special Live','Newport Performing Arts Theater','P-Pop & OPM',8500.00,5500.00,2200.00,1),('KAIA-CS','KAIA','P-Pop Concert Series','Music Museum (Greenhills)','P-Pop & OPM',5500.00,3500.00,1500.00,1),('RD-SOLO','Rob Deniel','Solo Live Concert','New Frontier Theater','P-Pop & OPM',6000.00,4000.00,2000.00,1),('SB19-PAG','SB19','Pagtatag / Simula at Wakas','Smart Araneta Coliseum','P-Pop & OPM',15000.00,8000.00,2500.00,1),('TS-ERAS','Taylor Swift','The Eras Tour (Custom)','Philippine Stadium (Bulacan)','International',50000.00,22000.00,6500.00,1),('TW-AH','The Weeknd','After Hours Tour (Custom)','Philippine Arena (Bulacan)','International',50000.00,18000.00,7000.00,1),('WL-WD','Westlife','The Wild Dreams Tour','SM Mall of Asia Arena','International',18000.00,13500.00,4500.00,1),('YC-BB','Yeng Constantino','Biyaheng Bente','Smart Araneta Coliseum','P-Pop & OPM',8600.00,5000.00,2900.00,1);
/*!40000 ALTER TABLE `ep_event_catalog` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-15 18:27:41
