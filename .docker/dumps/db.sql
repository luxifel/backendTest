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
  `id_inventory` int(11) NOT NULL AUTO_INCREMENT,
  `id_survivor` int(11) DEFAULT NULL,
  `item` text DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_inventory`),
  KEY `id_survivor` (`id_survivor`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`id_survivor`) REFERENCES `survivors` (`id_survivor`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4;

/*Data for the table `inventory` */

insert  into `inventory`(`id_inventory`,`id_survivor`,`item`,`qty`) values 
(27,42,'water',6),
(28,42,'food',0),
(29,42,'medication',3),
(30,42,'ammunition',0),
(31,43,'water',2),
(32,43,'food',0),
(33,43,'medication',5),
(34,43,'ammunition',3),
(35,44,'water',0),
(36,44,'food',5),
(37,44,'medication',2),
(38,44,'ammunition',5),
(39,45,'water',8),
(40,45,'food',1),
(41,45,'medication',0),
(42,45,'ammunition',4),
(43,46,'water',1),
(44,46,'food',1),
(45,46,'medication',2),
(46,46,'ammunition',4);

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
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4;

/*Data for the table `survivors` */

insert  into `survivors`(`id_survivor`,`name`,`age`,`gender`,`location`,`infected`,`reported`) values 
(42,'testerino',12,'male','+1234/+8901',0,0),
(43,'testerina',39,'female','-9298338/+1234900',0,0),
(44,'gigi',17,'female','+12345/-14',0,6),
(45,'gigi2',98,'male','+4321/+9382',0,2),
(46,'franco',24,'male','+0391/+933838',0,0);

/*Table structure for table `trade_points` */

DROP TABLE IF EXISTS `trade_points`;

CREATE TABLE `trade_points` (
  `item` text DEFAULT NULL,
  `points` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `trade_points` */

insert  into `trade_points`(`item`,`points`) values 
('water',4),
('food',3),
('medication',2),
('ammunition',1);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
