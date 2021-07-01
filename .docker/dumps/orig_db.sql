/*
SQLyog Ultimate v13.1.2 (64 bit)
MySQL - 10.5.9-MariaDB-1:10.5.9+maria~focal : Database - zssn
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`zssn` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `zssn`;

/*Table structure for table `inventory` */

DROP TABLE IF EXISTS `inventory`;

CREATE TABLE `inventory` (
  `id_survivor` int(11) NOT NULL AUTO_INCREMENT,
  `item` text DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_survivor`),
  CONSTRAINT `id_survivor` FOREIGN KEY (`id_survivor`) REFERENCES `survivors` (`id_survivor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `inventory` */

/*Table structure for table `survivors` */

DROP TABLE IF EXISTS `survivors`;

CREATE TABLE `survivors` (
  `id_survivor` int(11) NOT NULL AUTO_INCREMENT,
  `name` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` text DEFAULT NULL,
  `location` text DEFAULT NULL,
  `infected` smallint(6) DEFAULT NULL,
  `reported` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id_survivor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `survivors` */

/*Table structure for table `trade_points` */

DROP TABLE IF EXISTS `trade_points`;

CREATE TABLE `trade_points` (
  `item` text DEFAULT NULL,
  `points` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `trade_points` */

insert  into `trade_points`(`item`,`points`) values 
('Water',4),
('Food',3),
('Medication',2),
('Ammunition',1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
