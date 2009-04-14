/*
SQLyog Enterprise - MySQL GUI v7.15 
MySQL - 5.1.30-community : Database - test2
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `feedtr_feed_posts` */

DROP TABLE IF EXISTS `feedtr_feed_posts`;

CREATE TABLE `feedtr_feed_posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned DEFAULT NULL,
  `feed_uid` varchar(32) DEFAULT NULL,
  `post_date` int(11) unsigned DEFAULT '0',
  `post_title` varchar(250) DEFAULT NULL,
  `post_url` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `feedtr_feed_posts` */

/*Table structure for table `feedtr_feeds` */

DROP TABLE IF EXISTS `feedtr_feeds`;

CREATE TABLE `feedtr_feeds` (
  `feed_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Feed-ID',
  `feed_url` varchar(250) DEFAULT NULL COMMENT 'URL des Feed',
  `feed_interval` int(11) unsigned DEFAULT NULL COMMENT 'Intervall',
  `feed_last_poll` int(11) unsigned DEFAULT NULL COMMENT 'Letzter Poll',
  `feed_status` int(11) unsigned DEFAULT NULL COMMENT 'Aktueller Status',
  `feed_error_count` int(11) unsigned DEFAULT NULL COMMENT 'Fehler-Counter',
  PRIMARY KEY (`feed_id`),
  KEY `feed_last_poll` (`feed_interval`,`feed_last_poll`,`feed_status`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `feedtr_feeds` */

insert  into `feedtr_feeds`(`feed_id`,`feed_url`,`feed_interval`,`feed_last_poll`,`feed_status`,`feed_error_count`) values (1,'http://feeds2.feedburner.com/tagdocs',600,0,0,0);
insert  into `feedtr_feeds`(`feed_id`,`feed_url`,`feed_interval`,`feed_last_poll`,`feed_status`,`feed_error_count`) values (2,'http://www.heise.de/newsticker/heise-atom.xml',600,0,0,0);
insert  into `feedtr_feeds`(`feed_id`,`feed_url`,`feed_interval`,`feed_last_poll`,`feed_status`,`feed_error_count`) values (3,'http://rss.golem.de/rss.php?feed=RSS1.0',300,0,0,0);

/*Table structure for table `feedtr_log_errors` */

DROP TABLE IF EXISTS `feedtr_log_errors`;

CREATE TABLE `feedtr_log_errors` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_timestamp` int(11) unsigned DEFAULT NULL,
  `log_feed_id` int(11) unsigned DEFAULT NULL,
  `log_error` text,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
