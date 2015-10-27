CREATE DATABASE IF NOT EXISTS `working_tasks` CHARACTER SET utf8 COLLATE utf8_bin;
USE `working_tasks`; 
CREATE TABLE IF NOT EXISTS `regs` (
 `user_id` int(11) NOT NULL default '0',
 `login` varchar(50) NOT NULL default '',
 `reg_date` datetime NOT NULL default '0000-00-00 00:00:00',
 PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='test task table for the UNION-IT'
