-- MySQL dump 10.9
--
-- Host: localhost    Database: gnome
-- ------------------------------------------------------
-- Server version	4.1.12-Debian_1-log
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ftpmirrors`
--

CREATE TABLE `ftpmirrors` (
  `name` varchar(20) default NULL,
  `url` varchar(100) default NULL,
  `location` enum('United States and Canada','Australia','Europe','Asia','South America','Other') default NULL,
  `email` varchar(40) default NULL,
  `comments` text,
  `description` text,
  `id` int(11) NOT NULL auto_increment,
  `active` int(11) default '1',
  `last_update` timestamp NOT NULL,
  PRIMARY KEY  (`id`)
);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

