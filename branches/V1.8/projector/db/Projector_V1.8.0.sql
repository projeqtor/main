
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.8.0                                      //
-- // Date : 2010-09-07                                     //
-- ///////////////////////////////////////////////////////////
--
--

CREATE TABLE `${prefix}indicator` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `code` varchar(10),
  `type` varchar(10),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}indicator` (`id`, `code`, `type`, `name`, `idle`) VALUES
  (1, 'IDDT', 'delay', 'initialDueDateTime', 0),
  (2, 'ADDT', 'delay', 'actualDueDateTime', 0),
  (3, 'IDD', 'delay', 'initialDueDate', 0),
  (4, 'ADD', 'delay', 'actualDueDate', 0),
  (5, 'IED', 'delay', 'initialEndDate', 0),
  (6, 'VED', 'delay', 'validatedEndDate', 0),
  (7, 'PED', 'delay', 'plannedEndDate', 0),
  (8, 'ISD', 'delay', 'initialStartDate', 0),
  (9, 'VSD', 'delay', 'validatedStartDate', 0),
  (10, 'PSD', 'delay', 'plannedStartDate', 0),
  (11, 'PCOVC', 'percent', 'PlannedCostOverValidatedCost', 0),
  (12, 'PCOAC', 'percent', 'PlannedCostOverAssignedCost', 0),
  (13, 'PWOVW', 'percent', 'PlannedWorkOverValidatedWork', 0),
  (14, 'PWOAW', 'percent', 'PlannedWorkOverAssignedWork', 0);

CREATE TABLE `${prefix}indicatorable` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}indicatorable` (`id`, `name`, `idle`) VALUES
(1, 'Ticket', 0),
(2, 'Activity', 0),
(3, 'Milestone', 0),
(4, 'Risk', 0),
(5, 'Action', 0),
(6, 'Issue', 0),
(7, 'Question', 0),
(8, 'Project', 0);

CREATE TABLE `${prefix}indicatorableindicator` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idIndicatorable` int(12) unsigned,
  `nameIndicatorable` varchar(100), 
  `idIndicator` int(12) unsigned,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

ALTER TABLE `${prefix}indicatorableindicator` ADD INDEX indicatorableindicatorIndicatorable (idIndicatorable),
ADD INDEX indicatorableindicatorIndicator (idIndicator);

INSERT INTO `${prefix}indicatorableindicator` (`idIndicator`, `idIndicatorable`, `nameIndicatorable`, `idle`) VALUES
(1, 1, 'Ticket', 0),
(2, 1, 'Ticket', 0),
(3, 4, 'Risk', 0),
(3, 5, 'Action', 0),
(3, 6, 'Issue', 0),
(3, 7, 'Question', 0),
(4, 4, 'Risk', 0),
(4, 5, 'Action', 0),
(4, 6, 'Issue', 0),
(4, 7, 'Question', 0),
(5, 2, 'Activity',0),
(5, 3, 'Milestone',0),
(5, 8, 'Project',0),
(6, 2, 'Activity',0),
(6, 3, 'Milestone',0),
(6, 8, 'Project',0),
(7, 2, 'Activity',0),
(7, 3, 'Milestone',0),
(7, 8, 'Project',0),
(8, 2, 'Activity',0),
(8, 8, 'Project',0),
(9, 2, 'Activity',0),
(9, 8, 'Project',0),
(10, 2, 'Activity',0),
(10, 8, 'Project',0),
(11, 2, 'Activity',0),
(11, 8, 'Project',0),
(12, 2, 'Activity',0),
(12, 8, 'Project',0),
(13, 2, 'Activity',0),
(13, 8, 'Project',0),
(14, 2, 'Activity',0),
(14, 8, 'Project',0);

CREATE TABLE `${prefix}indicatordefinition` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idIndicatorable` int(12) unsigned,
  `nameIndicatorable` varchar(100),
  `idIndicator` int(12) unsigned,
  `codeIndicator` varchar(10),
  `typeIndicator` varchar(10),
  `idType` int(12) unsigned,
  `warningValue` decimal(6,3),
  `idWarningDelayUnit` int(12) unsigned,
  `codeWarningDelayUnit` varchar(10),
  `alertValue` decimal(6,3), 
  `idAlertDelayUnit` int(12) unsigned,
  `codeAlertDelayUnit` varchar(10),
  `mailToUser` int(1) unsigned DEFAULT 0,
  `mailToResource` int(1) unsigned DEFAULT 0,
  `mailToProject` int(1) unsigned DEFAULT 0,
  `mailToContact` int(1) unsigned DEFAULT 0,
  `mailToLeader` int(1) unsigned DEFAULT 0,
  `mailToOther` int(1) unsigned DEFAULT 0,
  `alertToUser` int(1) unsigned DEFAULT 0,
  `alertToResource` int(1) unsigned DEFAULT 0,
  `alertToProject` int(1) unsigned DEFAULT 0,
  `alertToContact` int(1) unsigned DEFAULT 0,
  `alertToLeader` int(1) unsigned DEFAULT 0,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

ALTER TABLE `${prefix}indicatordefinition` ADD INDEX indicatordefinitionIndicatorable (idIndicatorable),
ADD INDEX indicatordefinitionIndicator (idIndicator), 
ADD INDEX indicatordefinitionType (idType);

CREATE TABLE `${prefix}indicatorvalue` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `refType` varchar(100),
  `refId` int(10) unsigned,
  `idIndicatorDefinition` int(10) unsigned,
  `targetDateTime` datetime,
  `targetValue` decimal(11,2),
  `code` varchar(10),
  `type` varchar(10),
  `warningTargetDateTime` datetime,
  `warningTargetValue` decimal(11,2),
  `warningSent` int(1) unsigned default 0, 
  `alertTargetDateTime` datetime,
  `alertTargetValue` decimal(11,2),
  `alertSent` int(1) unsigned default 0,
  `handled`  int(1) unsigned default 0,
  `done`  int(1) unsigned default 0,
  `idle`  int(1) unsigned default 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

ALTER TABLE `${prefix}indicatorvalue` ADD INDEX indicatorvalueIndicatordefinition (idIndicatorDefinition),
ADD INDEX indicatorvalueReference (refType, refId);

CREATE TABLE `${prefix}alert` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `idProject`  int(12) unsigned DEFAULT NULL,
  `refType` int(12) unsigned DEFAULT NULL,
  `refId` int(12) unsigned DEFAULT NULL,
  `idIndicatorValue` int(12) unsigned,
  `idUser`  int(12) unsigned DEFAULT NULL,
  `alertType` varchar(10), 
  `alertTargetDateTime` datetime,
  `alertDateTime` datetime DEFAULT NULL,
  `read` int(1) unsigned DEFAULT '0',
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


INSERT INTO `${prefix}parameter` (idUser, idProject, parameterCode, parameterValue) VALUES
(null, null, 'startAM','08:00'),
(null, null, 'endAM','12:00'),
(null, null, 'startPM','14:00'),
(null, null, 'endPM','18:00'),
(null, null, 'dayTime','8.00');

CREATE TABLE `${prefix}delayunit` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10),
  `name` varchar(100),
  `type` varchar(10),
  `sortOrder` int(3),
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
  
INSERT INTO `${prefix}delayunit` (id, code, name, type, sortOrder, idle) VALUES 
(1,'HH','hours', 'delay', 100, 0),
(2,'OH','openHours', 'delay', 200, 0),
(3,'DD','days', 'delay', 300, 0),
(4,'OD','openDays', 'delay', 400, 0),
(5,'PCT','percent', 'percent', 500,0);

CREATE TABLE `${prefix}delay` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(100),
  `idType` int(12) unsigned,
  `idUrgency` int(12) unsigned,
  `value` decimal(6,3),
  `idDelayUnit` int(12) unsigned,
  `idle` int(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}delay` (`id`, `scope`, `idType`, `idUrgency`, `value`, `idDelayUnit`, `idle`) VALUES
(1,'Ticket',16,1,2,2,0),
(2,'Ticket',16,2,1,4,0),
(3,'Ticket',17,1,1,4,0),
(4,'Ticket',17,1,4,4,0),
(5,'Ticket',18,1,4,2,0),
(6,'Ticket',18,2,2,4,0);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(88, 'menuAutomation', 13, 'menu', 765, Null, 0);

INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 88, 1),
(2, 88, 1),
(3, 88, 1);

UPDATE `${prefix}menu` SET idMenu=88 where id=59;
UPDATE `${prefix}menu` SET idMenu=88 where id=68;

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(89, 'menuTicketDelay', 88, 'object', 785, Null, 0);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 89, 1),
(2, 89, 1),
(3, 89, 1);

INSERT INTO `${prefix}menu` (`id`, `name`, `idMenu`, `type`, `sortOrder`, `level`, `idle`) VALUES
(90, 'menuIndicatorDefinition', 88, 'object', 790, Null, 0);
INSERT INTO `${prefix}habilitation` (`idProfile`, `idMenu`, `allowAccess`) VALUES
(1, 90, 1),
(2, 90, 1),
(3, 90, 1);

ALTER TABLE `${prefix}affectation` ADD idResourceSelect int(12) unsigned;

UPDATE `${prefix}affectation` SET idResourceSelect=idResource where
exists (select 'x' from `${prefix}user` user where user.id=idResource and user.isResource='1');
 