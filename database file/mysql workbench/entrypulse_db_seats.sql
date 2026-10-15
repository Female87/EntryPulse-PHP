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
-- Table structure for table `seats`
--

DROP TABLE IF EXISTS `seats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seats` (
  `seat_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `event_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tier_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `section_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `seat_label` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'AVAILABLE',
  `locked_by_user_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lock_expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`seat_id`),
  KEY `event_id` (`event_id`),
  KEY `tier_id` (`tier_id`),
  KEY `locked_by_user_id` (`locked_by_user_id`),
  CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `seats_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `seat_tiers` (`tier_id`) ON DELETE CASCADE,
  CONSTRAINT `seats_ibfk_3` FOREIGN KEY (`locked_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seats`
--

LOCK TABLES `seats` WRITE;
/*!40000 ALTER TABLE `seats` DISABLE KEYS */;
INSERT INTO `seats` VALUES ('st_101','evt_001','tr_001','SEC_A1','A-01','HELD','usr_102','2026-09-20 06:01:44'),('st_102','evt_001','tr_001','SEC_A1','A-02','AVAILABLE',NULL,NULL),('st_103','evt_001','tr_001','SEC_A1','A-03','SOLD',NULL,NULL),('st_104','evt_001','tr_002','SEC_B1','B-01','AVAILABLE',NULL,NULL),('st_105','evt_001','tr_002','SEC_B1','B-02','HELD','usr_102','2026-09-20 06:01:44');
/*!40000 ALTER TABLE `seats` ENABLE KEYS */;
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
