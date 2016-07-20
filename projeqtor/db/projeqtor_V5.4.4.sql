-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.4.4                                       //
-- // Date : 2016-07-07                                     //
-- ///////////////////////////////////////////////////////////

CREATE TABLE `${prefix}cronexecution` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `cron` varchar(100),
  `fileExecuted` varchar(500),
  `idle` int(1),
  `fonctionName` varchar(256),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;