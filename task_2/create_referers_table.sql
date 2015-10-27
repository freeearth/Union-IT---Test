
CREATE DATABASE IF NOT EXISTS `working_tasks` CHARACTER SET utf8 COLLATE utf8_bin;
CREATE TABLE IF NOT EXISTS `referers_daily` (
`refer_id` int(30) NOT NULL default '0' COMMENT 'REFER_ID',
`referer` varchar(256) NOT NULL default '' COMMENT 'referer',
`count_` int(30) NOT NULL default '0' COMMENT 'count clicks per day',
`date_` date NOT NULL default '0000-00-00' COMMENT 'date of refering' 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='test task_2 table for the UNION-IT'
