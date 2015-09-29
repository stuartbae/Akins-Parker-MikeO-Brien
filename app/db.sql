-- MySQL dump 10.13  Distrib 5.6.24, for osx10.10 (x86_64)
--
-- Host: localhost    Database: swim
-- ------------------------------------------------------
-- Server version	5.6.24

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
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `street` varchar(255) NOT NULL,
  `street2` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `billing` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (9,1,'3100 CHINO HILLS PKWY #335',NULL,'Chino Hills','California','91709',NULL),(12,125,'1334 parkview Ave',NULL,'Manhattan Beach','California','90266',NULL),(13,126,'3100 CHINO HILLS PKWY #335',NULL,'Chino Hills','California','91709',NULL),(14,128,'3100 Chino Hills Pkwy Suite 335',NULL,'Chino Hills','CA','91709',NULL);
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `expire_at` date NOT NULL,
  PRIMARY KEY (`coupon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (6,'test','2016-10-26');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exp_levels`
--

DROP TABLE IF EXISTS `exp_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exp_levels` (
  `level_id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exp_levels`
--

LOCK TABLES `exp_levels` WRITE;
/*!40000 ALTER TABLE `exp_levels` DISABLE KEYS */;
INSERT INTO `exp_levels` VALUES (6,'Does not swim'),(7,'Some swiming, not independent'),(8,'Returning student to this program'),(9,'Good basic skills, ready for stroke'),(10,'Advanced stroke technique');
/*!40000 ALTER TABLE `exp_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) NOT NULL,
  `seats` int(11) NOT NULL,
  `starts_at` int(11) NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `group_code` varchar(255) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (25,1,4,1443263626,0,'XXX'),(26,2,4,1443269999,0,'XXX'),(27,2,4,1443233333,0,'XXX');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons` (
  `lesson_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL,
  `pool_id` int(11) NOT NULL,
  `tuition` int(11) NOT NULL DEFAULT '380',
  `deposit` int(11) NOT NULL DEFAULT '190',
  `approved` tinyint(1) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (1,1,6,380,190,NULL,1111885200,1111885200),(2,1,6,380,190,NULL,1111885200,1111885200);
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `placements`
--

DROP TABLE IF EXISTS `placements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `placements` (
  `placement_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`placement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `placements`
--

LOCK TABLES `placements` WRITE;
/*!40000 ALTER TABLE `placements` DISABLE KEYS */;
/*!40000 ALTER TABLE `placements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pools`
--

DROP TABLE IF EXISTS `pools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pools` (
  `pool_id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `access_info` text,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pool_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pools`
--

LOCK TABLES `pools` WRITE;
/*!40000 ALTER TABLE `pools` DISABLE KEYS */;
INSERT INTO `pools` VALUES (6,9,'call me at the gate',NULL,'2015-09-28 18:54:34');
/*!40000 ALTER TABLE `pools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `level_id` int(11) NOT NULL,
  `note` text,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `salt` varchar(23) NOT NULL,
  `password` varchar(88) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `spouse_firstname` varchar(255) DEFAULT NULL,
  `spouse_lastname` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `home` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','44550680455f35ccf1214b','HU1hjxyF5q8OORn2rwicGa4uY41BNBFUtdOwcz1GpgLvXZgcT6gUqOoiqHKQ4/VWBzWJ3eNlSdXfvk0OVsU6cw==','Stuart','Bae',NULL,NULL,NULL,NULL,'stu.pae@gmail.com','ROLE_ADMIN',1379889332),(121,'dkkdk@mail.com','1595312376560aaec33963d','ZKYAyYdG8KbvF3FgWGGMaUPjo91ti9c4gJmiHFi5zO5FjiBDczAWihqxT4hibMJ4EwNWhsR6qRTMMQHENCte6g==','Suok Pae','Pae','Dana','Choe','2139158380','2139158380','dkkdk@mail.com','ROLE_USER',1443540675),(122,'dkkdk@mail.comd','584704766560aaf1603ea0','tdmdBIjdnepYTMcsLa8KQiS+blwqxgjjybiyVPKOV/1pm0FoHvI1iL+GXTdQt9cRcTMKn38JPtVhiqzN5ZcWIQ==','Suok Pae','Pae','Dana','Choe','2139158380','2139158380','dkkdk@mail.comd','ROLE_USER',1443540758),(123,'dfdfd@dldld.com3','1786555085560aaf487c278','vL3YyNpfWr+FHil1IFKpTsoYSlcyGIinQgHMkyC+0yotCuEJ1ZrS45RjjCmeBWv7bGIPFCE84qIx3QwUXdSSGg==','Sdd','Df','Dana','Choe','2139158380','2139158380','dfdfd@dldld.com3','ROLE_USER',1443540808),(125,'danichoe@live.com','1204603586560aafc713d0d','7JSfPnID3VZNfNSvVQZ7nv0xdU6RidsmQGE0DL+0xz0a/Cr3vlCTuM0i5461h3bIc2Ua0bUJ8xO4nGHQXYBtlw==','Dana','Choe','Dani','Choe','3108028822','3108028822','danichoe@live.com','ROLE_USER',1443540935),(126,'xx@x.xx','1649414707560ab0a889aba','hLqZl6GxP0jo9VLPNghdyY8mQUDAjr4YHjK9sIlHbExO0rYXsOIL4GCzpE1emTM1Tdkb3IDBpbhFk7EvcpNI0A==','Stu Hotmail','Pae','Dana','Choe','2139158380','2139158380','xx@x.xx','ROLE_USER',1443541160),(127,'stu.pae@gmail.com2','1922022673560ab2476eec0','If675+5FdlQEZ7/nEXfXWgqj1U53QZ7Gj/E0572cLBd+/PrRfAzdCX7JHdo8fwE6A7riPyHG3jVnoLAFoGIFEA==','Dana','Choe','Stu','Pae','2135263','2135263','stu.pae@gmail.com2','ROLE_USER',1443541575),(128,'stu.pae@gmail.com3','1333422427560ab2a81237f','sCs+HSuae7ROtT+EKQNnZCZoeRVIY7MxsUCaO6xt1m8KSDRK7UvgrkkGnnWBBH3Vc75y14cCgH8ZzjyqpDrVwQ==','Dana','Choe','Stu','Pae','2135263','2135263','stu.pae@gmail.com3','ROLE_USER',1443541672);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-09-29 10:34:15
