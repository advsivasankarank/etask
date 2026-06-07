-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: compliance_mgmt
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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `module_code` varchar(60) NOT NULL,
  `action_code` varchar(60) NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_activity_logs_user` (`user_id`),
  KEY `idx_activity_logs_entity` (`entity_type`,`entity_id`),
  KEY `idx_activity_logs_module` (`module_code`,`action_code`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'AUTH','LOGOUT','users',1,'User logged out',NULL,NULL,'2026-04-09 23:29:09'),(2,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-09 23:29:11'),(3,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-09 23:49:38'),(4,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-10 00:18:59'),(5,1,'AUTH','LOGOUT','users',1,'User logged out',NULL,NULL,'2026-04-10 00:20:16'),(6,2,'AUTH','LOGIN','users',2,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-10 00:20:41'),(7,2,'AUTH','LOGOUT','users',2,'User logged out',NULL,NULL,'2026-04-10 00:33:50'),(8,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-10 00:33:55'),(9,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-10 13:53:38'),(10,1,'AUTH','LOGOUT','users',1,'User logged out',NULL,NULL,'2026-04-10 18:23:02'),(11,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-10 20:12:25'),(12,1,'USERS','CREATE','users',3,'User created: admin',NULL,NULL,'2026-04-10 20:27:54'),(13,1,'AUTH','LOGOUT','users',1,'User logged out',NULL,NULL,'2026-04-10 20:47:21'),(14,3,'AUTH','LOGIN','users',3,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-10 20:47:37'),(15,3,'AUTH','LOGOUT','users',3,'User logged out',NULL,NULL,'2026-04-10 20:50:12'),(16,3,'AUTH','LOGIN','users',3,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-10 20:53:34'),(17,3,'AUTH','LOGOUT','users',3,'User logged out',NULL,NULL,'2026-04-10 21:17:18'),(18,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-10 21:17:23'),(19,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','2026-04-12 19:51:38'),(20,1,'AUTH','LOGOUT','users',1,'User logged out',NULL,NULL,'2026-04-12 19:51:55'),(21,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-07 12:05:55'),(22,1,'USERS','CHANGE_PASSWORD','users',1,'User completed first-login password change.',NULL,NULL,'2026-06-07 12:06:21'),(23,1,'AUTH','LOGIN','users',1,'User logged in','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-07 12:10:51'),(24,1,'USERS','RESET_PASSWORD','users',3,'Password reset for user: admin',NULL,NULL,'2026-06-07 12:13:00');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_activity_logs`
--

DROP TABLE IF EXISTS `attendance_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_session_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `service_order_task_id` bigint(20) unsigned DEFAULT NULL,
  `activity_type` enum('ACTIVE','IDLE','TASK_LINKED','TASK_UNLINKED','BREAK','RESUME') NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `duration_seconds` int(11) NOT NULL DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_attendance_activity_session` (`attendance_session_id`),
  KEY `fk_attendance_activity_task` (`service_order_task_id`),
  KEY `idx_attendance_activity_user` (`user_id`,`started_at`),
  CONSTRAINT `fk_attendance_activity_session` FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions` (`id`),
  CONSTRAINT `fk_attendance_activity_task` FOREIGN KEY (`service_order_task_id`) REFERENCES `service_order_tasks` (`id`),
  CONSTRAINT `fk_attendance_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_activity_logs`
--

LOCK TABLES `attendance_activity_logs` WRITE;
/*!40000 ALTER TABLE `attendance_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_sessions`
--

DROP TABLE IF EXISTS `attendance_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `login_at` datetime NOT NULL,
  `logout_at` datetime DEFAULT NULL,
  `total_active_seconds` int(11) NOT NULL DEFAULT 0,
  `total_idle_seconds` int(11) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_attendance_sessions_user` (`user_id`,`login_at`),
  CONSTRAINT `fk_attendance_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_sessions`
--

LOCK TABLES `attendance_sessions` WRITE;
/*!40000 ALTER TABLE `attendance_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` bigint(20) unsigned NOT NULL,
  `action_type` enum('INSERT','UPDATE','DELETE_BLOCKED','LOGIN','LOGOUT','STATUS_CHANGE') NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `action_note` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_audit_logs_user` (`user_id`),
  KEY `idx_audit_logs_table_record` (`table_name`,`record_id`),
  KEY `idx_audit_logs_created_at` (`created_at`),
  CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_contacts`
--

DROP TABLE IF EXISTS `client_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `contact_name` varchar(190) NOT NULL,
  `designation` varchar(120) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `can_login` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_client_contacts_client` (`client_id`),
  CONSTRAINT `fk_client_contacts_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contacts`
--

LOCK TABLES `client_contacts` WRITE;
/*!40000 ALTER TABLE `client_contacts` DISABLE KEYS */;
INSERT INTO `client_contacts` VALUES (1,1,'Demo Client User','Authorized Signatory','client.demo@localhost.test','9876543210',1,1,'2026-04-10 00:19:40','2026-04-10 16:22:25');
/*!40000 ALTER TABLE `client_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_portal_credentials`
--

DROP TABLE IF EXISTS `client_portal_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_portal_credentials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `portal_code` varchar(50) NOT NULL,
  `portal_label` varchar(190) DEFAULT NULL,
  `user_identifier` varchar(190) DEFAULT NULL,
  `password_ciphertext` text DEFAULT NULL,
  `password_iv` varchar(255) DEFAULT NULL,
  `portal_url` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `last_verified_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_client_portal` (`client_id`,`portal_code`),
  KEY `idx_client_portal_client` (`client_id`),
  KEY `fk_client_portal_credentials_created_by` (`created_by`),
  KEY `fk_client_portal_credentials_updated_by` (`updated_by`),
  CONSTRAINT `fk_client_portal_credentials_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk_client_portal_credentials_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_client_portal_credentials_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_portal_credentials`
--

LOCK TABLES `client_portal_credentials` WRITE;
/*!40000 ALTER TABLE `client_portal_credentials` DISABLE KEYS */;
INSERT INTO `client_portal_credentials` VALUES (1,1,'INCOME_TAX','Income Tax Portal','superadmin','60815l9in4g/Ng1pxTaznA==','qU3LpUjAFiqcKuFtcg/ZBQ==',NULL,NULL,NULL,1,1,1,'2026-04-10 16:16:01','2026-04-10 16:16:01');
/*!40000 ALTER TABLE `client_portal_credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_code` varchar(40) NOT NULL,
  `client_type` enum('INDIVIDUAL','PROPRIETORSHIP','PARTNERSHIP','LLP','PRIVATE_LIMITED','PUBLIC_LIMITED','TRUST','SOCIETY','OTHER') NOT NULL,
  `legal_name` varchar(255) NOT NULL,
  `trade_name` varchar(255) DEFAULT NULL,
  `pan` varchar(20) DEFAULT NULL,
  `tan` varchar(20) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `aadhaar_no` varchar(20) DEFAULT NULL,
  `aadhaar_ciphertext` text DEFAULT NULL,
  `aadhaar_iv` varchar(255) DEFAULT NULL,
  `aadhaar_last4` char(4) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `alternate_mobile` varchar(20) DEFAULT NULL,
  `landline` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state_name` varchar(120) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country_id` bigint(20) unsigned DEFAULT NULL,
  `default_company_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_crm_id` bigint(20) unsigned DEFAULT NULL,
  `onboarded_at` datetime DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `archive_reason` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_code` (`client_code`),
  UNIQUE KEY `uk_clients_pan` (`pan`),
  KEY `fk_clients_country` (`country_id`),
  KEY `fk_clients_default_company` (`default_company_id`),
  KEY `idx_clients_pan` (`pan`),
  KEY `idx_clients_tan` (`tan`),
  KEY `idx_clients_mobile` (`mobile`),
  KEY `idx_clients_legal_name` (`legal_name`),
  KEY `fk_clients_assigned_crm` (`assigned_crm_id`),
  CONSTRAINT `fk_clients_assigned_crm` FOREIGN KEY (`assigned_crm_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_clients_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `fk_clients_default_company` FOREIGN KEY (`default_company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'CLT-DEMO-001','INDIVIDUAL','Demo Client',NULL,'ABCDE1234F',NULL,NULL,NULL,NULL,NULL,NULL,'client.demo@localhost.test','9876543210',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-10 00:19:40',NULL,NULL,1,'2026-04-10 00:19:40','2026-04-10 16:22:25');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_clients_no_delete
BEFORE DELETE ON clients
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of clients is not allowed.';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `legal_name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `company_type` enum('ADVOCATE','PRIVATE_LIMITED','PARTNERSHIP','PROPRIETORSHIP','OTHER') NOT NULL DEFAULT 'OTHER',
  `pan` varchar(20) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `tan` varchar(20) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `state_name` varchar(120) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_companies_country` (`country_id`),
  CONSTRAINT `fk_companies_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'ETAX','E Tax Advisors Pvt Ltd','E Tax Advisors Pvt Ltd','PRIVATE_LIMITED',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-04-09 23:27:32','2026-04-09 23:27:32'),(2,'ADV','K. Sivasankaran (Advocate)','K. Sivasankaran (Advocate)','ADVOCATE',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-04-09 23:27:32','2026-04-09 23:27:32');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_service_type_map`
--

DROP TABLE IF EXISTS `company_service_type_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_service_type_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `service_type_id` bigint(20) unsigned NOT NULL,
  `is_default_company` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_company_service_type` (`company_id`,`service_type_id`),
  KEY `fk_company_service_service_type` (`service_type_id`),
  CONSTRAINT `fk_company_service_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_company_service_service_type` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_service_type_map`
--

LOCK TABLES `company_service_type_map` WRITE;
/*!40000 ALTER TABLE `company_service_type_map` DISABLE KEYS */;
INSERT INTO `company_service_type_map` VALUES (1,1,2,1,'2026-04-09 23:27:32'),(2,2,1,1,'2026-04-09 23:27:32'),(3,1,3,1,'2026-04-09 23:27:32');
/*!40000 ALTER TABLE `company_service_type_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultant_assignments`
--

DROP TABLE IF EXISTS `consultant_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `consultant_user_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `internal_reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('ASSIGNED','WORK_SUBMITTED','UNDER_INTERNAL_REVIEW','APPROVED','REJECTED') NOT NULL DEFAULT 'ASSIGNED',
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_consultant_assignment` (`service_order_id`,`consultant_user_id`),
  KEY `fk_consultant_assignment_consultant` (`consultant_user_id`),
  KEY `fk_consultant_assignment_assigned_by` (`assigned_by`),
  KEY `fk_consultant_assignment_reviewer` (`internal_reviewer_id`),
  CONSTRAINT `fk_consultant_assignment_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_consultant_assignment_consultant` FOREIGN KEY (`consultant_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_consultant_assignment_reviewer` FOREIGN KEY (`internal_reviewer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_consultant_assignment_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultant_assignments`
--

LOCK TABLES `consultant_assignments` WRITE;
/*!40000 ALTER TABLE `consultant_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `consultant_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultant_bills`
--

DROP TABLE IF EXISTS `consultant_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_bills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `consultant_assignment_id` bigint(20) unsigned NOT NULL,
  `bill_no` varchar(80) NOT NULL,
  `bill_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `document_id` bigint(20) unsigned DEFAULT NULL,
  `review_status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_consultant_bill_assignment_billno` (`consultant_assignment_id`,`bill_no`),
  KEY `fk_consultant_bill_document` (`document_id`),
  KEY `fk_consultant_bill_reviewed_by` (`reviewed_by`),
  CONSTRAINT `fk_consultant_bill_assignment` FOREIGN KEY (`consultant_assignment_id`) REFERENCES `consultant_assignments` (`id`),
  CONSTRAINT `fk_consultant_bill_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `fk_consultant_bill_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultant_bills`
--

LOCK TABLES `consultant_bills` WRITE;
/*!40000 ALTER TABLE `consultant_bills` DISABLE KEYS */;
/*!40000 ALTER TABLE `consultant_bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultant_deliverables`
--

DROP TABLE IF EXISTS `consultant_deliverables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_deliverables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `consultant_assignment_id` bigint(20) unsigned NOT NULL,
  `document_id` bigint(20) unsigned NOT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `review_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_consultant_deliverable_assignment` (`consultant_assignment_id`),
  KEY `fk_consultant_deliverable_document` (`document_id`),
  KEY `fk_consultant_deliverable_reviewed_by` (`reviewed_by`),
  CONSTRAINT `fk_consultant_deliverable_assignment` FOREIGN KEY (`consultant_assignment_id`) REFERENCES `consultant_assignments` (`id`),
  CONSTRAINT `fk_consultant_deliverable_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `fk_consultant_deliverable_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultant_deliverables`
--

LOCK TABLES `consultant_deliverables` WRITE;
/*!40000 ALTER TABLE `consultant_deliverables` DISABLE KEYS */;
/*!40000 ALTER TABLE `consultant_deliverables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultant_payments`
--

DROP TABLE IF EXISTS `consultant_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consultant_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `consultant_bill_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_mode` enum('CASH','BANK_TRANSFER','CHEQUE','UPI','OTHER') NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `paid_by` bigint(20) unsigned NOT NULL,
  `proof_document_id` bigint(20) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_consultant_payment_paid_by` (`paid_by`),
  KEY `fk_consultant_payment_proof` (`proof_document_id`),
  KEY `idx_consultant_payments_bill` (`consultant_bill_id`),
  CONSTRAINT `fk_consultant_payment_bill` FOREIGN KEY (`consultant_bill_id`) REFERENCES `consultant_bills` (`id`),
  CONSTRAINT `fk_consultant_payment_paid_by` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_consultant_payment_proof` FOREIGN KEY (`proof_document_id`) REFERENCES `documents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultant_payments`
--

LOCK TABLES `consultant_payments` WRITE;
/*!40000 ALTER TABLE `consultant_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `consultant_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `iso_code` char(2) NOT NULL,
  `phone_code` varchar(10) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso_code` (`iso_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disbursements`
--

DROP TABLE IF EXISTS `disbursements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disbursements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `expense_date` date NOT NULL,
  `expense_type` varchar(80) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `is_recoverable` tinyint(1) NOT NULL DEFAULT 1,
  `proof_document_id` bigint(20) unsigned DEFAULT NULL,
  `paid_to` varchar(190) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `added_by` bigint(20) unsigned NOT NULL,
  `invoiced_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_disbursement_proof` (`proof_document_id`),
  KEY `fk_disbursement_added_by` (`added_by`),
  KEY `idx_disbursement_so` (`service_order_id`),
  CONSTRAINT `fk_disbursement_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_disbursement_proof` FOREIGN KEY (`proof_document_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `fk_disbursement_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disbursements`
--

LOCK TABLES `disbursements` WRITE;
/*!40000 ALTER TABLE `disbursements` DISABLE KEYS */;
/*!40000 ALTER TABLE `disbursements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_versions`
--

DROP TABLE IF EXISTS `document_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` bigint(20) unsigned NOT NULL,
  `version_no` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `checksum_sha256` char(64) DEFAULT NULL,
  `change_note` varchar(255) DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_document_version` (`document_id`,`version_no`),
  KEY `fk_document_versions_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_document_versions_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `fk_document_versions_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_versions`
--

LOCK TABLES `document_versions` WRITE;
/*!40000 ALTER TABLE `document_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `linked_module` enum('PSO','SO','CONSULTANT','BILLING','CLIENT','GENERAL') NOT NULL,
  `linked_id` bigint(20) unsigned NOT NULL,
  `document_category` varchar(80) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `current_version_no` int(11) NOT NULL DEFAULT 1,
  `latest_file_name` varchar(255) NOT NULL,
  `latest_file_path` varchar(500) NOT NULL,
  `mime_type` varchar(120) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `checksum_sha256` char(64) DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `archived_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_documents_module` (`linked_module`,`linked_id`),
  KEY `idx_documents_client` (`client_id`),
  KEY `fk_documents_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_documents_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_documents_no_delete
BEFORE DELETE ON documents
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of documents is not allowed.';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `financial_years`
--

DROP TABLE IF EXISTS `financial_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financial_years` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `label` varchar(30) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `financial_years`
--

LOCK TABLES `financial_years` WRITE;
/*!40000 ALTER TABLE `financial_years` DISABLE KEYS */;
INSERT INTO `financial_years` VALUES (1,'2025-26','FY 2025-26','2025-04-01','2026-03-31',1,'2026-04-09 23:35:41'),(2,'2026-27','FY 2026-27','2026-04-01','2027-03-31',1,'2026-04-09 23:35:41');
/*!40000 ALTER TABLE `financial_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `line_type` enum('SERVICE_FEE','DISBURSEMENT','TAX','ADJUSTMENT') NOT NULL,
  `reference_type` enum('SERVICE_ORDER','DISBURSEMENT','PAYMENT','OTHER') NOT NULL DEFAULT 'OTHER',
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_invoice_items_invoice` (`invoice_id`),
  CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `financial_year_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `invoice_type` enum('ADVANCE','FINAL','DEBIT_NOTE') NOT NULL DEFAULT 'FINAL',
  `service_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `disbursement_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gross_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `advance_adjusted` decimal(15,2) NOT NULL DEFAULT 0.00,
  `net_payable` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('UNPAID','PARTIALLY_PAID','PAID') NOT NULL DEFAULT 'UNPAID',
  `accounting_status` enum('DRAFT','APPROVED','ISSUED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `fk_invoices_company` (`company_id`),
  KEY `fk_invoices_financial_year` (`financial_year_id`),
  KEY `fk_invoices_client` (`client_id`),
  KEY `fk_invoices_approved_by` (`approved_by`),
  KEY `fk_invoices_created_by` (`created_by`),
  KEY `idx_invoices_service_order` (`service_order_id`),
  KEY `idx_invoices_payment_status` (`payment_status`),
  CONSTRAINT `fk_invoices_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk_invoices_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_invoices_financial_year` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`),
  CONSTRAINT `fk_invoices_service_order` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_invoices_no_delete
BEFORE DELETE ON invoices
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of invoices is not allowed.';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `client_contact_id` bigint(20) unsigned DEFAULT NULL,
  `channel` enum('EMAIL','SMS','WHATSAPP','IN_APP') NOT NULL DEFAULT 'IN_APP',
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `linked_module` enum('PSO','SO','INVOICE','PAYMENT','REMINDER','GENERAL') NOT NULL DEFAULT 'GENERAL',
  `linked_id` bigint(20) unsigned DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `delivery_status` enum('PENDING','SENT','FAILED','READ') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`,`delivery_status`),
  KEY `idx_notifications_contact` (`client_contact_id`,`delivery_status`),
  CONSTRAINT `fk_notifications_client_contact` FOREIGN KEY (`client_contact_id`) REFERENCES `client_contacts` (`id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `numbering_sequences`
--

DROP TABLE IF EXISTS `numbering_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `numbering_sequences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) unsigned NOT NULL,
  `financial_year_id` bigint(20) unsigned NOT NULL,
  `sequence_type` enum('PSO','SO','INV','RCPT') NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_numbering_sequence` (`company_id`,`financial_year_id`,`sequence_type`),
  KEY `fk_numbering_sequence_financial_year` (`financial_year_id`),
  CONSTRAINT `fk_numbering_sequence_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_numbering_sequence_financial_year` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `numbering_sequences`
--

LOCK TABLES `numbering_sequences` WRITE;
/*!40000 ALTER TABLE `numbering_sequences` DISABLE KEYS */;
/*!40000 ALTER TABLE `numbering_sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_allocations`
--

DROP TABLE IF EXISTS `payment_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_allocations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL,
  `allocated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `allocated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_payment_alloc_allocated_by` (`allocated_by`),
  KEY `idx_payment_alloc_payment` (`payment_id`),
  KEY `idx_payment_alloc_invoice` (`invoice_id`),
  CONSTRAINT `fk_payment_alloc_allocated_by` FOREIGN KEY (`allocated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_payment_alloc_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `fk_payment_alloc_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_allocations`
--

LOCK TABLES `payment_allocations` WRITE;
/*!40000 ALTER TABLE `payment_allocations` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_receipt_items`
--

DROP TABLE IF EXISTS `payment_receipt_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_receipt_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `receipt_id` bigint(20) unsigned NOT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `allocated_amount` decimal(15,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_payment_receipt_item_invoice` (`invoice_id`),
  KEY `idx_payment_receipt_item_receipt` (`receipt_id`),
  CONSTRAINT `fk_payment_receipt_item_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `fk_payment_receipt_item_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `receipts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_receipt_items`
--

LOCK TABLES `payment_receipt_items` WRITE;
/*!40000 ALTER TABLE `payment_receipt_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_receipt_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `service_order_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_mode` enum('RAZORPAY','CASH','BANK_TRANSFER','CHEQUE','UPI','OTHER') NOT NULL,
  `transaction_type` enum('ADVANCE','INVOICE_PAYMENT','REFUND','ADJUSTMENT') NOT NULL DEFAULT 'INVOICE_PAYMENT',
  `reference_no` varchar(120) DEFAULT NULL,
  `gateway_order_id` varchar(120) DEFAULT NULL,
  `gateway_payment_id` varchar(120) DEFAULT NULL,
  `gateway_signature` varchar(255) DEFAULT NULL,
  `status` enum('INITIATED','SUCCESS','FAILED','REFUNDED','CANCELLED') NOT NULL DEFAULT 'SUCCESS',
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_payments_client` (`client_id`),
  KEY `fk_payments_company` (`company_id`),
  KEY `fk_payments_received_by` (`received_by`),
  KEY `idx_payments_invoice` (`invoice_id`),
  KEY `idx_payments_service_order` (`service_order_id`),
  KEY `idx_payments_reference` (`reference_no`),
  CONSTRAINT `fk_payments_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk_payments_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_payments_service_order` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_payments_no_delete
BEFORE DELETE ON payments
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of payments is not allowed.';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module_code` varchar(60) NOT NULL,
  `action_code` varchar(60) NOT NULL,
  `label` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permissions_module_action` (`module_code`,`action_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pre_service_orders`
--

DROP TABLE IF EXISTS `pre_service_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pre_service_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pso_no` varchar(50) NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `financial_year_id` bigint(20) unsigned NOT NULL,
  `service_type_id` bigint(20) unsigned NOT NULL,
  `requested_for_period` varchar(60) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requested_by_contact_id` bigint(20) unsigned NOT NULL,
  `current_status` enum('DRAFT','SUBMITTED','UNDER_REVIEW','APPROVED','REJECTED','CONVERTED_TO_SO') NOT NULL DEFAULT 'SUBMITTED',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `admin_rejected_by` bigint(20) unsigned DEFAULT NULL,
  `admin_rejected_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `converted_so_id` bigint(20) unsigned DEFAULT NULL,
  `notification_sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pso_no` (`pso_no`),
  KEY `fk_pso_company` (`company_id`),
  KEY `fk_pso_financial_year` (`financial_year_id`),
  KEY `fk_pso_service_type` (`service_type_id`),
  KEY `fk_pso_requested_by_contact` (`requested_by_contact_id`),
  KEY `fk_pso_reviewed_by` (`reviewed_by`),
  KEY `fk_pso_admin_rejected_by` (`admin_rejected_by`),
  KEY `idx_pso_client` (`client_id`),
  KEY `idx_pso_status` (`current_status`),
  KEY `fk_pso_converted_so` (`converted_so_id`),
  CONSTRAINT `fk_pso_admin_rejected_by` FOREIGN KEY (`admin_rejected_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_pso_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk_pso_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_pso_converted_so` FOREIGN KEY (`converted_so_id`) REFERENCES `service_orders` (`id`),
  CONSTRAINT `fk_pso_financial_year` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`),
  CONSTRAINT `fk_pso_requested_by_contact` FOREIGN KEY (`requested_by_contact_id`) REFERENCES `client_contacts` (`id`),
  CONSTRAINT `fk_pso_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_pso_service_type` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pre_service_orders`
--

LOCK TABLES `pre_service_orders` WRITE;
/*!40000 ALTER TABLE `pre_service_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `pre_service_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_pre_service_orders_reject_admin_only
BEFORE UPDATE ON pre_service_orders
FOR EACH ROW
BEGIN
    IF NEW.current_status = 'REJECTED'
       AND OLD.current_status <> 'REJECTED'
       AND NEW.admin_rejected_by IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only Admin can reject PSO.';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_pre_service_orders_no_delete
BEFORE DELETE ON pre_service_orders
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of pre-service orders is not allowed.';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pso_documents`
--

DROP TABLE IF EXISTS `pso_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pso_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pso_id` bigint(20) unsigned NOT NULL,
  `document_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pso_document` (`pso_id`,`document_id`),
  KEY `fk_pso_documents_document` (`document_id`),
  CONSTRAINT `fk_pso_documents_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `fk_pso_documents_pso` FOREIGN KEY (`pso_id`) REFERENCES `pre_service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pso_documents`
--

LOCK TABLES `pso_documents` WRITE;
/*!40000 ALTER TABLE `pso_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `pso_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pso_reviews`
--

DROP TABLE IF EXISTS `pso_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pso_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pso_id` bigint(20) unsigned NOT NULL,
  `review_action` enum('SUBMITTED','COMMENTED','RECOMMENDED_APPROVAL','RECOMMENDED_REJECTION','APPROVED','REJECTED') NOT NULL,
  `remarks` text DEFAULT NULL,
  `acted_by` bigint(20) unsigned NOT NULL,
  `acted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_pso_reviews_acted_by` (`acted_by`),
  KEY `idx_pso_reviews_pso` (`pso_id`,`acted_at`),
  CONSTRAINT `fk_pso_reviews_acted_by` FOREIGN KEY (`acted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_pso_reviews_pso` FOREIGN KEY (`pso_id`) REFERENCES `pre_service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pso_reviews`
--

LOCK TABLES `pso_reviews` WRITE;
/*!40000 ALTER TABLE `pso_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `pso_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receipts`
--

DROP TABLE IF EXISTS `receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `receipt_no` varchar(50) NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `financial_year_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `payment_id` bigint(20) unsigned NOT NULL,
  `receipt_date` date NOT NULL,
  `receipt_amount` decimal(15,2) NOT NULL,
  `generated_by` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_no` (`receipt_no`),
  KEY `fk_receipts_company` (`company_id`),
  KEY `fk_receipts_financial_year` (`financial_year_id`),
  KEY `fk_receipts_client` (`client_id`),
  KEY `fk_receipts_generated_by` (`generated_by`),
  KEY `idx_receipts_payment` (`payment_id`),
  CONSTRAINT `fk_receipts_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk_receipts_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_receipts_financial_year` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`),
  CONSTRAINT `fk_receipts_generated_by` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_receipts_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receipts`
--

LOCK TABLES `receipts` WRITE;
/*!40000 ALTER TABLE `receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `receipts` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_receipts_receipt_no_immutable
BEFORE UPDATE ON receipts
FOR EACH ROW
BEGIN
    IF NEW.receipt_no <> OLD.receipt_no THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Receipt number is immutable.';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `reminder_logs`
--

DROP TABLE IF EXISTS `reminder_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reminder_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reminder_id` bigint(20) unsigned NOT NULL,
  `action_type` enum('CREATED','NOTIFIED','FOLLOW_UP_LOGGED','COMPLETED','OVERDUE_MARKED','SKIPPED') NOT NULL,
  `action_by` bigint(20) unsigned DEFAULT NULL,
  `action_note` text DEFAULT NULL,
  `action_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_reminder_logs_action_by` (`action_by`),
  KEY `idx_reminder_logs_reminder` (`reminder_id`,`action_at`),
  CONSTRAINT `fk_reminder_logs_action_by` FOREIGN KEY (`action_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_reminder_logs_reminder` FOREIGN KEY (`reminder_id`) REFERENCES `reminders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reminder_logs`
--

LOCK TABLES `reminder_logs` WRITE;
/*!40000 ALTER TABLE `reminder_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `reminder_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reminders`
--

DROP TABLE IF EXISTS `reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reminders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `reminder_type` enum('E_VERIFICATION','SLA_ESCALATION','PAYMENT_FOLLOWUP','DOCUMENT_FOLLOWUP','GENERAL') NOT NULL,
  `schedule_day_no` int(11) DEFAULT NULL,
  `due_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `status` enum('PENDING','SENT','DONE','SKIPPED','OVERDUE') NOT NULL DEFAULT 'PENDING',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_reminders_assigned_to` (`assigned_to`),
  KEY `fk_reminders_created_by` (`created_by`),
  KEY `idx_reminders_due` (`due_at`,`status`),
  KEY `idx_reminders_so` (`service_order_id`,`reminder_type`),
  CONSTRAINT `fk_reminders_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_reminders_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_reminders_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reminders`
--

LOCK TABLES `reminders` WRITE;
/*!40000 ALTER TABLE `reminders` DISABLE KEYS */;
/*!40000 ALTER TABLE `reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `label` varchar(120) NOT NULL,
  `scope` enum('SYSTEM','PORTAL') NOT NULL DEFAULT 'SYSTEM',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'SUPER_ADMIN','Super Admin','SYSTEM',1,'2026-04-09 23:27:32'),(2,'ADMIN','Admin / CEO','SYSTEM',1,'2026-04-09 23:27:32'),(3,'CRM','CRM','SYSTEM',1,'2026-04-09 23:27:32'),(4,'ASSISTANT_CRM','Assistant CRM','SYSTEM',1,'2026-04-09 23:27:32'),(5,'BACKEND_STAFF','Backend Staff','SYSTEM',1,'2026-04-09 23:27:32'),(6,'DEO','DEO','SYSTEM',1,'2026-04-09 23:27:32'),(7,'ACCOUNTS','Accounts','SYSTEM',1,'2026-04-09 23:27:32'),(8,'CONSULTANT','Consultant','SYSTEM',1,'2026-04-09 23:27:32'),(9,'CLIENT','Client','PORTAL',1,'2026-04-09 23:27:32');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schema_migrations`
--

DROP TABLE IF EXISTS `schema_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schema_migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(190) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `checksum_sha256` char(64) NOT NULL,
  `applied_at` datetime NOT NULL,
  `execution_ms` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_name` (`migration_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schema_migrations`
--

LOCK TABLES `schema_migrations` WRITE;
/*!40000 ALTER TABLE `schema_migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `schema_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_order_closures`
--

DROP TABLE IF EXISTS `service_order_closures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_order_closures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `closure_type` enum('PROCEDURAL','ACCOUNTING','FINAL') NOT NULL,
  `closure_status` enum('PENDING','COMPLETED','BLOCKED') NOT NULL DEFAULT 'PENDING',
  `closure_at` datetime DEFAULT NULL,
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `block_reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_so_closure_type` (`service_order_id`,`closure_type`),
  KEY `fk_so_closure_closed_by` (`closed_by`),
  CONSTRAINT `fk_so_closure_closed_by` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_closure_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_order_closures`
--

LOCK TABLES `service_order_closures` WRITE;
/*!40000 ALTER TABLE `service_order_closures` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_order_closures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_order_queries`
--

DROP TABLE IF EXISTS `service_order_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_order_queries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `raised_by` bigint(20) unsigned NOT NULL,
  `addressed_to_user_id` bigint(20) unsigned DEFAULT NULL,
  `addressed_to_contact_id` bigint(20) unsigned DEFAULT NULL,
  `query_text` text NOT NULL,
  `response_text` text DEFAULT NULL,
  `raised_at` datetime NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  `status` enum('OPEN','RESPONDED','CLOSED') NOT NULL DEFAULT 'OPEN',
  PRIMARY KEY (`id`),
  KEY `fk_so_queries_raised_by` (`raised_by`),
  KEY `fk_so_queries_addressed_user` (`addressed_to_user_id`),
  KEY `fk_so_queries_addressed_contact` (`addressed_to_contact_id`),
  KEY `idx_so_queries_so` (`service_order_id`,`status`),
  CONSTRAINT `fk_so_queries_addressed_contact` FOREIGN KEY (`addressed_to_contact_id`) REFERENCES `client_contacts` (`id`),
  CONSTRAINT `fk_so_queries_addressed_user` FOREIGN KEY (`addressed_to_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_queries_raised_by` FOREIGN KEY (`raised_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_queries_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_order_queries`
--

LOCK TABLES `service_order_queries` WRITE;
/*!40000 ALTER TABLE `service_order_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_order_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_order_status_flags`
--

DROP TABLE IF EXISTS `service_order_status_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_order_status_flags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `is_document_pending` tinyint(1) NOT NULL DEFAULT 1,
  `is_payment_pending` tinyint(1) NOT NULL DEFAULT 0,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `is_filing_done` tinyint(1) NOT NULL DEFAULT 0,
  `is_acknowledgement_captured` tinyint(1) NOT NULL DEFAULT 0,
  `is_e_verification_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_e_verification_done` tinyint(1) NOT NULL DEFAULT 0,
  `e_verification_due_date` date DEFAULT NULL,
  `is_overdue` tinyint(1) NOT NULL DEFAULT 0,
  `is_client_paid` tinyint(1) NOT NULL DEFAULT 0,
  `is_consultant_payment_pending` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_so_status_flag` (`service_order_id`),
  CONSTRAINT `fk_so_status_flag_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_order_status_flags`
--

LOCK TABLES `service_order_status_flags` WRITE;
/*!40000 ALTER TABLE `service_order_status_flags` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_order_status_flags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_order_tasks`
--

DROP TABLE IF EXISTS `service_order_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_order_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `task_title` varchar(255) NOT NULL,
  `task_type` enum('DOCUMENT','PREPARATION','REVIEW','FILING','FOLLOW_UP','BILLING','CONSULTANT','GENERAL') NOT NULL DEFAULT 'GENERAL',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `due_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `status` enum('OPEN','IN_PROGRESS','DONE','BLOCKED') NOT NULL DEFAULT 'OPEN',
  `remarks` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_so_tasks_assigned_to` (`assigned_to`),
  KEY `fk_so_tasks_created_by` (`created_by`),
  KEY `idx_so_tasks_so` (`service_order_id`,`status`),
  CONSTRAINT `fk_so_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_tasks_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_tasks_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_order_tasks`
--

LOCK TABLES `service_order_tasks` WRITE;
/*!40000 ALTER TABLE `service_order_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_order_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_orders`
--

DROP TABLE IF EXISTS `service_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `so_no` varchar(50) NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `financial_year_id` bigint(20) unsigned NOT NULL,
  `pre_service_order_id` bigint(20) unsigned DEFAULT NULL,
  `service_type_id` bigint(20) unsigned NOT NULL,
  `workflow_definition_id` bigint(20) unsigned NOT NULL,
  `work_basis` enum('ANNUAL','MONTHLY','QUARTERLY') DEFAULT NULL,
  `compliance_subtype` varchar(40) DEFAULT NULL,
  `assessment_year` varchar(9) DEFAULT NULL,
  `period_month` tinyint(3) unsigned DEFAULT NULL,
  `period_quarter` enum('Q1','Q2','Q3','Q4') DEFAULT NULL,
  `period_year` smallint(5) unsigned DEFAULT NULL,
  `period_label` varchar(60) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority_level` enum('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL DEFAULT 'MEDIUM',
  `assigned_crm_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_assistant_crm_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_backend_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_deo_id` bigint(20) unsigned DEFAULT NULL,
  `current_stage_code` varchar(60) NOT NULL,
  `payment_reference_no` varchar(100) DEFAULT NULL,
  `payment_recorded_at` datetime DEFAULT NULL,
  `filing_reference_no` varchar(100) DEFAULT NULL,
  `acknowledgement_no` varchar(100) DEFAULT NULL,
  `acknowledgement_captured_at` datetime DEFAULT NULL,
  `e_verification_completed_at` datetime DEFAULT NULL,
  `last_stage_changed_at` datetime DEFAULT NULL,
  `procedural_closed_at` datetime DEFAULT NULL,
  `accounting_closed_at` datetime DEFAULT NULL,
  `final_closed_at` datetime DEFAULT NULL,
  `final_closed_by` bigint(20) unsigned DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `lock_reason` varchar(255) DEFAULT NULL,
  `admin_override_unlocked_by` bigint(20) unsigned DEFAULT NULL,
  `admin_override_unlocked_at` datetime DEFAULT NULL,
  `sla_due_at` datetime DEFAULT NULL,
  `escalated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `so_no` (`so_no`),
  KEY `fk_so_financial_year` (`financial_year_id`),
  KEY `fk_so_pso` (`pre_service_order_id`),
  KEY `fk_so_service_type` (`service_type_id`),
  KEY `fk_so_workflow_definition` (`workflow_definition_id`),
  KEY `fk_so_assigned_assistant_crm` (`assigned_assistant_crm_id`),
  KEY `fk_so_assigned_backend` (`assigned_backend_id`),
  KEY `fk_so_assigned_deo` (`assigned_deo_id`),
  KEY `fk_so_final_closed_by` (`final_closed_by`),
  KEY `fk_so_admin_override_unlocked_by` (`admin_override_unlocked_by`),
  KEY `fk_so_created_by` (`created_by`),
  KEY `idx_so_client` (`client_id`),
  KEY `idx_so_company_fy` (`company_id`,`financial_year_id`),
  KEY `idx_so_current_stage` (`current_stage_code`),
  KEY `idx_so_assigned_crm` (`assigned_crm_id`),
  KEY `idx_so_sla_due` (`sla_due_at`),
  CONSTRAINT `fk_so_admin_override_unlocked_by` FOREIGN KEY (`admin_override_unlocked_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_assigned_assistant_crm` FOREIGN KEY (`assigned_assistant_crm_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_assigned_backend` FOREIGN KEY (`assigned_backend_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_assigned_crm` FOREIGN KEY (`assigned_crm_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_assigned_deo` FOREIGN KEY (`assigned_deo_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk_so_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_so_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_final_closed_by` FOREIGN KEY (`final_closed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_so_financial_year` FOREIGN KEY (`financial_year_id`) REFERENCES `financial_years` (`id`),
  CONSTRAINT `fk_so_pso` FOREIGN KEY (`pre_service_order_id`) REFERENCES `pre_service_orders` (`id`),
  CONSTRAINT `fk_so_service_type` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`),
  CONSTRAINT `fk_so_workflow_definition` FOREIGN KEY (`workflow_definition_id`) REFERENCES `workflow_definitions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_orders`
--

LOCK TABLES `service_orders` WRITE;
/*!40000 ALTER TABLE `service_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_orders` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_service_orders_so_no_immutable
BEFORE UPDATE ON service_orders
FOR EACH ROW
BEGIN
    IF NEW.so_no <> OLD.so_no THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Service Order number is immutable.';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_service_orders_lock_after_final_close
BEFORE UPDATE ON service_orders
FOR EACH ROW
BEGIN
    IF OLD.final_closed_at IS NOT NULL AND NEW.is_locked = 0 AND NEW.admin_override_unlocked_by IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Final closed service order can be unlocked only via Admin override.';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_service_orders_no_delete
BEFORE DELETE ON service_orders
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of service orders is not allowed.';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `service_types`
--

DROP TABLE IF EXISTS `service_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(120) NOT NULL,
  `service_group` enum('ITR','GST','TDS','OTHER') NOT NULL,
  `requires_payment_stage` tinyint(1) NOT NULL DEFAULT 0,
  `requires_e_verification` tinyint(1) NOT NULL DEFAULT 0,
  `default_sla_days` int(11) NOT NULL DEFAULT 2,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_types`
--

LOCK TABLES `service_types` WRITE;
/*!40000 ALTER TABLE `service_types` DISABLE KEYS */;
INSERT INTO `service_types` VALUES (1,'ITR','Income Tax Return','ITR',1,1,2,NULL,1,'2026-04-09 23:27:32'),(2,'GST','GST Compliance','GST',1,0,2,NULL,1,'2026-04-09 23:27:32'),(3,'TDS','TDS Compliance','TDS',0,0,2,NULL,1,'2026-04-09 23:27:32');
/*!40000 ALTER TABLE `service_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(60) NOT NULL,
  `code` varchar(60) NOT NULL,
  `label` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_statuses_category_code` (`category`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statuses`
--

LOCK TABLES `statuses` WRITE;
/*!40000 ALTER TABLE `statuses` DISABLE KEYS */;
/*!40000 ALTER TABLE `statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_company_map`
--

DROP TABLE IF EXISTS `user_company_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_company_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_company` (`user_id`,`company_id`),
  KEY `fk_user_company_company` (`company_id`),
  CONSTRAINT `fk_user_company_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fk_user_company_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_company_map`
--

LOCK TABLES `user_company_map` WRITE;
/*!40000 ALTER TABLE `user_company_map` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_company_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_role_map`
--

DROP TABLE IF EXISTS `user_role_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_role` (`user_id`,`role_id`),
  KEY `fk_user_role_role` (`role_id`),
  KEY `fk_user_role_assigned_by` (`assigned_by`),
  CONSTRAINT `fk_user_role_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `fk_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role_map`
--

LOCK TABLES `user_role_map` WRITE;
/*!40000 ALTER TABLE `user_role_map` DISABLE KEYS */;
INSERT INTO `user_role_map` VALUES (1,1,1,NULL,'2026-04-09 23:28:12'),(2,2,9,NULL,'2026-04-10 00:19:40'),(3,3,3,1,'2026-04-10 20:27:54');
/*!40000 ALTER TABLE `user_role_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(40) DEFAULT NULL,
  `client_contact_id` bigint(20) unsigned DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(190) NOT NULL,
  `email` varchar(190) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `auth_type` enum('LOCAL') NOT NULL DEFAULT 'LOCAL',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `last_password_changed_at` datetime DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `employee_code` (`employee_code`),
  KEY `fk_users_client_contact` (`client_contact_id`),
  CONSTRAINT `fk_users_client_contact` FOREIGN KEY (`client_contact_id`) REFERENCES `client_contacts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'EMP-SUPER-001',NULL,'superadmin','$2y$10$Njb0ZMx6o.mEG22a9jaRruwqO.NjPwneAxQAIp3yb2ZiOs3SqjGOS','System Super Admin','superadmin@localhost.test','9999999999','LOCAL',0,'2026-06-07 14:35:28','2026-06-07 12:06:21',0,NULL,1,'2026-04-09 23:28:12','2026-06-07 14:35:28'),(2,NULL,1,'clientdemo','$2y$10$lDNibUvDCxNb1x4owXg3CeCRC7wW/GvyJPJxLVF6gfKognM50oqhW','Demo Client User','client.demo@localhost.test','9876543210','LOCAL',1,'2026-04-10 00:20:41',NULL,0,NULL,1,'2026-04-10 00:19:40','2026-04-10 00:20:41'),(3,'01',NULL,'admin','$2y$10$sjvD/514bKlAH1c4DWpT0ucq3CCnmuHXy3nlJ7nq8Wfmrvs0i0Xre','siva','etaxpdy@gmail.com','9894626300','LOCAL',1,'2026-04-10 20:53:34','2026-06-07 12:13:00',0,NULL,1,'2026-04-10 20:27:54','2026-06-07 12:13:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_definitions`
--

DROP TABLE IF EXISTS `workflow_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_definitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_type_id` bigint(20) unsigned NOT NULL,
  `version_no` int(11) NOT NULL DEFAULT 1,
  `name` varchar(190) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_workflow_def` (`service_type_id`,`version_no`),
  KEY `fk_workflow_def_created_by` (`created_by`),
  CONSTRAINT `fk_workflow_def_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_workflow_def_service_type` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_definitions`
--

LOCK TABLES `workflow_definitions` WRITE;
/*!40000 ALTER TABLE `workflow_definitions` DISABLE KEYS */;
INSERT INTO `workflow_definitions` VALUES (1,1,1,'Income Tax Return Default Workflow',1,'2026-04-09 23:27:32',NULL),(2,2,1,'GST Compliance Default Workflow',1,'2026-04-09 23:27:32',NULL),(3,3,1,'TDS Compliance Default Workflow',1,'2026-04-09 23:27:32',NULL);
/*!40000 ALTER TABLE `workflow_definitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_stage_definitions`
--

DROP TABLE IF EXISTS `workflow_stage_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_stage_definitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `workflow_definition_id` bigint(20) unsigned NOT NULL,
  `stage_code` varchar(60) NOT NULL,
  `stage_name` varchar(120) NOT NULL,
  `stage_group` enum('COMMON','ITR','GST','TDS','CLOSURE') NOT NULL DEFAULT 'COMMON',
  `sort_order` int(11) NOT NULL,
  `is_milestone_click_required` tinyint(1) NOT NULL DEFAULT 1,
  `auto_trigger_on` varchar(60) DEFAULT NULL,
  `is_terminal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_workflow_stage_def` (`workflow_definition_id`,`stage_code`),
  CONSTRAINT `fk_workflow_stage_def_workflow` FOREIGN KEY (`workflow_definition_id`) REFERENCES `workflow_definitions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_stage_definitions`
--

LOCK TABLES `workflow_stage_definitions` WRITE;
/*!40000 ALTER TABLE `workflow_stage_definitions` DISABLE KEYS */;
INSERT INTO `workflow_stage_definitions` VALUES (1,1,'DOCUMENT_PENDING','Document Pending','COMMON',1,1,NULL,0,'2026-04-09 23:27:32'),(2,2,'DOCUMENT_PENDING','Document Pending','COMMON',1,1,NULL,0,'2026-04-09 23:27:32'),(3,3,'DOCUMENT_PENDING','Document Pending','COMMON',1,1,NULL,0,'2026-04-09 23:27:32'),(4,1,'PREPARATION','Preparation','COMMON',2,1,NULL,0,'2026-04-09 23:27:32'),(5,2,'PREPARATION','Preparation','COMMON',2,1,NULL,0,'2026-04-09 23:27:32'),(6,3,'PREPARATION','Preparation','COMMON',2,1,NULL,0,'2026-04-09 23:27:32'),(7,1,'REVIEW','Review','COMMON',3,1,NULL,0,'2026-04-09 23:27:32'),(8,2,'REVIEW','Review','COMMON',3,1,NULL,0,'2026-04-09 23:27:32'),(9,3,'REVIEW','Review','COMMON',3,1,NULL,0,'2026-04-09 23:27:32'),(10,1,'PAYMENT_PENDING','Payment Pending','ITR',4,0,'PAYMENT_ENTRY',0,'2026-04-09 23:27:32'),(11,1,'PAID','Paid','ITR',5,1,NULL,0,'2026-04-09 23:27:32'),(12,2,'PAYMENT_PENDING','Payment Pending','GST',4,0,'PAYMENT_ENTRY',0,'2026-04-09 23:27:32'),(13,2,'PAID','Paid','GST',5,1,NULL,0,'2026-04-09 23:27:32'),(14,1,'FILING_PENDING','Filing Pending','COMMON',6,1,NULL,0,'2026-04-09 23:27:32'),(15,2,'FILING_PENDING','Filing Pending','COMMON',6,1,NULL,0,'2026-04-09 23:27:32'),(16,3,'FILING_PENDING','Filing Pending','COMMON',6,1,NULL,0,'2026-04-09 23:27:32'),(17,1,'FILING_DONE','Filing Done','COMMON',7,1,NULL,0,'2026-04-09 23:27:32'),(18,2,'FILING_DONE','Filing Done','COMMON',7,1,NULL,0,'2026-04-09 23:27:32'),(19,3,'FILING_DONE','Filing Done','COMMON',7,1,NULL,0,'2026-04-09 23:27:32'),(20,1,'ACKNOWLEDGEMENT_CAPTURED','Acknowledgement Captured','COMMON',8,0,'ACK_UPLOAD',0,'2026-04-09 23:27:32'),(21,2,'ACKNOWLEDGEMENT_CAPTURED','Acknowledgement Captured','COMMON',8,0,'ACK_UPLOAD',0,'2026-04-09 23:27:32'),(22,3,'ACKNOWLEDGEMENT_CAPTURED','Acknowledgement Captured','COMMON',8,0,'ACK_UPLOAD',0,'2026-04-09 23:27:32'),(23,1,'E_VERIFICATION_PENDING','E-Verification Pending','ITR',9,1,NULL,0,'2026-04-09 23:27:32'),(24,1,'E_VERIFICATION_DONE','E-Verification Done','ITR',10,1,NULL,0,'2026-04-09 23:27:32'),(25,1,'PROCEDURALLY_CLOSED','Procedurally Closed','CLOSURE',11,1,NULL,1,'2026-04-09 23:27:32'),(26,2,'PROCEDURALLY_CLOSED','Procedurally Closed','CLOSURE',11,1,NULL,1,'2026-04-09 23:27:32'),(27,3,'PROCEDURALLY_CLOSED','Procedurally Closed','CLOSURE',11,1,NULL,1,'2026-04-09 23:27:32');
/*!40000 ALTER TABLE `workflow_stage_definitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_stage_history`
--

DROP TABLE IF EXISTS `workflow_stage_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_stage_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `stage_code` varchar(60) NOT NULL,
  `stage_name` varchar(120) NOT NULL,
  `entered_at` datetime NOT NULL DEFAULT current_timestamp(),
  `exited_at` datetime DEFAULT NULL,
  `entered_by` bigint(20) unsigned NOT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_workflow_stage_history_entered_by` (`entered_by`),
  KEY `idx_workflow_stage_history_so` (`service_order_id`,`entered_at`),
  CONSTRAINT `fk_workflow_stage_history_entered_by` FOREIGN KEY (`entered_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_workflow_stage_history_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_stage_history`
--

LOCK TABLES `workflow_stage_history` WRITE;
/*!40000 ALTER TABLE `workflow_stage_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_stage_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_transition_logs`
--

DROP TABLE IF EXISTS `workflow_transition_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_transition_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_order_id` bigint(20) unsigned NOT NULL,
  `from_stage_code` varchar(60) DEFAULT NULL,
  `to_stage_code` varchar(60) NOT NULL,
  `transition_type` enum('MANUAL_MILESTONE','AUTO_PAYMENT','AUTO_ARN_UPLOAD','AUTO_ACK_UPLOAD','SYSTEM') NOT NULL,
  `transition_notes` text DEFAULT NULL,
  `triggered_by` bigint(20) unsigned DEFAULT NULL,
  `triggered_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_workflow_transition_triggered_by` (`triggered_by`),
  KEY `idx_workflow_transition_so` (`service_order_id`,`triggered_at`),
  CONSTRAINT `fk_workflow_transition_so` FOREIGN KEY (`service_order_id`) REFERENCES `service_orders` (`id`),
  CONSTRAINT `fk_workflow_transition_triggered_by` FOREIGN KEY (`triggered_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_transition_logs`
--

LOCK TABLES `workflow_transition_logs` WRITE;
/*!40000 ALTER TABLE `workflow_transition_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_transition_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'compliance_mgmt'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-07 14:42:44
