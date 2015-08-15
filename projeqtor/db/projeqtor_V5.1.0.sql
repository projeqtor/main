-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.1.0                                       //
-- // Date : 2015-07-30                                     //
-- ///////////////////////////////////////////////////////////

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`, `menuClass`) VALUES
(136, 'menuPlugin', 13, 'item', 977, NULL, 0, 'Admin ');
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 136, 1),
(2, 136, 0),
(3, 136, 0),
(4, 136, 0),
(5, 136, 0),
(6, 136, 0),
(7, 136, 0);

ALTER TABLE `${prefix}plugin` ADD `comment` varchar(4000) DEFAULT NULL,
ADD `uniqueCode` varchar (100) DEFAULT null;

CREATE TABLE `${prefix}projecthistory` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject` int(12) unsigned DEFAULT NULL,
  `day` varchar(10) DEFAULT NULL,
  `realWork` DECIMAL(9,5) UNSIGNED,
  `leftWork` DECIMAL(9,5) UNSIGNED,
  `realCost` DECIMAL(11,2) UNSIGNED,
  `leftCost` DECIMAL(11,2) UNSIGNED,
  `totalRealCost` DECIMAL(11,2) UNSIGNED,
  `totalLeftCost` DECIMAL(11,2) UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX projecthistoryProjectDay ON `${prefix}projecthistory` (idProject,day);
