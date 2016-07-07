CREATE DATABASE  IF NOT EXISTS `citizen_science` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `citizen_science`;
-- MySQL dump 10.13  Distrib 5.6.24, for Win64 (x86_64)
--
-- Host: citizen-science.ni.gy    Database: citizen_science
-- ------------------------------------------------------
-- Server version	5.5.49-0+deb8u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `image_flags`
--

DROP TABLE IF EXISTS `image_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `image_flags` (
  `source_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`source_id`,`username`),
  KEY `image_flags_fk_2_idx` (`username`),
  CONSTRAINT `image_flags_fk_1` FOREIGN KEY (`source_id`) REFERENCES `image_source` (`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `image_processed`
--

DROP TABLE IF EXISTS `image_processed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `image_processed` (
  `processed_id` int(10) NOT NULL AUTO_INCREMENT,
  `source_id` int(10) NOT NULL,
  `save_location` varchar(255) NOT NULL,
  `metadata` longtext,
  PRIMARY KEY (`processed_id`),
  UNIQUE KEY `processed_id_UNIQUE` (`processed_id`),
  KEY `source_id` (`source_id`),
  CONSTRAINT `image_processed_ibfk_1` FOREIGN KEY (`source_id`) REFERENCES `image_source` (`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `image_queue`
--

DROP TABLE IF EXISTS `image_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `image_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `image_queue_fk1_idx` (`source_id`),
  CONSTRAINT `image_queue_fk1` FOREIGN KEY (`source_id`) REFERENCES `image_source` (`source_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Processing queue for the database. The lowest id is the start, the highest the end.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `image_source`
--

DROP TABLE IF EXISTS `image_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `image_source` (
  `source_id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `save_location` varchar(255) NOT NULL,
  `uploaded_lat` double DEFAULT NULL,
  `uploaded_lng` double DEFAULT NULL,
  `uploaded_acc` double DEFAULT NULL,
  `uploaded_alt` double DEFAULT NULL,
  `uploaded_aac` double DEFAULT NULL,
  `uploaded_h` double DEFAULT NULL,
  `uploaded_s` double DEFAULT NULL,
  `uploaded_dt` datetime DEFAULT NULL,
  `uploaded_fn` varchar(255) NOT NULL,
  `processed` datetime DEFAULT NULL,
  `tags` mediumtext NOT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `salinity` decimal(10,5) DEFAULT NULL,
  `depth` decimal(10,5) DEFAULT NULL,
  `altitude` decimal(10,5) DEFAULT NULL,
  `light` decimal(10,5) DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`source_id`),
  UNIQUE KEY `source_id_UNIQUE` (`source_id`),
  UNIQUE KEY `save_location_UNIQUE` (`save_location`),
  KEY `username` (`username`),
  CONSTRAINT `image_source_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=322 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `image_tags`
--

DROP TABLE IF EXISTS `image_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `image_tags` (
  `tags_id` int(10) NOT NULL AUTO_INCREMENT,
  `processed_id` int(10) NOT NULL,
  `tag` varchar(50) NOT NULL,
  PRIMARY KEY (`tags_id`),
  UNIQUE KEY `tags_id_UNIQUE` (`tags_id`),
  KEY `processed_id` (`processed_id`),
  KEY `image_tags_ibfk_2_idx` (`tag`),
  CONSTRAINT `image_tags_ibfk_1` FOREIGN KEY (`processed_id`) REFERENCES `image_processed` (`processed_id`),
  CONSTRAINT `image_tags_ibfk_2` FOREIGN KEY (`tag`) REFERENCES `tags` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tag` varchar(50) NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `username` varchar(50) NOT NULL,
  `email` varchar(254) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `salt` varchar(13) NOT NULL,
  `fname` varchar(25) DEFAULT NULL,
  `lname` varchar(25) DEFAULT NULL,
  `organisation` varchar(50) DEFAULT NULL,
  `created` date NOT NULL COMMENT 'The date this account was created.',
  `updated` date NOT NULL COMMENT 'Date of the last time this account''s information was updated.',
  `deleted` int(11) NOT NULL DEFAULT '0',
  `password_reset_code` varchar(64) DEFAULT NULL,
  `password_reset_expiration` datetime NOT NULL DEFAULT '2000-00-00 00:00:00',
  PRIMARY KEY (`username`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Incomplete, will be working more on this after 20/04. Trying to find references to save for documentation as I go, list any changes you needed to make below.\n\nChanges: ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_perms`
--

DROP TABLE IF EXISTS `user_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_perms` (
  `perm` varchar(25) NOT NULL,
  `username` varchar(50) NOT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`perm`,`username`),
  KEY `fk_user_perms_1_idx` (`username`),
  CONSTRAINT `fk_user_perms_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-07-07 11:00:25
