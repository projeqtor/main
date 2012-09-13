
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V2.6.0                                      //
-- // Date : 2012-09-06                                     //
-- ///////////////////////////////////////////////////////////
--
--

ALTER TABLE `${prefix}note` ADD COLUMN `idPrivacy` int(12) unsigned default 1,
ADD COLUMN `idTeam` int(12) unsigned default 1;

UPDATE `${prefix}note` SET idTeam = (select idTeam from ${prefix}user USR where USR.id=idUser);

ALTER TABLE `${prefix}attachement` ADD COLUMN `idPrivacy` int(12) unsigned default 1,
ADD COLUMN `idTeam` int(12) unsigned default 1;

UPDATE `${prefix}attachement` SET idTeam = (select idTeam from ${prefix}user USR where USR.id=idUser);

CREATE TABLE `${prefix}privacy` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `sortOrder` int(3) unsigned DEFAULT NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `${prefix}privacy` (`id`, `name`, `color`, `sortOrder`, `idle`) VALUES
(1,'public','#003399',100,0),
(2,'team','#99FF99',200,0),
(3,'private','#FF9966',300,0);

CREATE TABLE `${prefix}predefinedtext` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100) default NULL,
  `idTextable` int(12) unsigned default NULL,
  `idType` int(12) unsigned default NULL,
  `name` varchar(100) default NULL,
  `text` varchar(4000) default NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `${prefix}textable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) default NULL,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=innoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `${prefix}textable` (`id`,`name`,`idle`) VALUES 
(1,'Action',0),
(2,'Activity',0),
(3,'Bill',0),
(4,'Decision',0),
(5,'IndividualExpense',0),
(6,'Issue',0),
(7,'Meeting',0),
(8,'Milestone',0),
(9,'Product',0),
(10,'Project',0),
(11,'ProjectExpense',0),
(12,'Question',0),
(13,'Requirement',0),
(14,'Risk',0),
(15,'Term',0),
(16,'TestCase',0),
(17,'TestSession',0),
(18,'Ticket',0),
(19,'Version',0);

INSERT INTO `${prefix}menu` (`id`,`name`,`idMenu`,`type`,`sortOrder`,`level`,`idle`) VALUES 
(116,'menuPredefinedNote',36,'object',763,NULL,0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 116, 1),
(2, 116, 0),
(3, 116, 0),
(4, 116, 0),
(5, 116, 0),
(6, 116, 0),
(7, 116, 0);