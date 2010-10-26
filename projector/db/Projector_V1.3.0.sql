
-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : V1.3.0                           //
-- // Date : 2010-10-07                                     //
-- ///////////////////////////////////////////////////////////
--
--

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `order`) VALUES
(8, 'reportWorkPlan', 2, 'workPlan.php', 6);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `order`, `defaultValue`) VALUES 
(11, 8, 'idProject', 'projectList', 10, 'currentProject');

ALTER TABLE `${prefix}action` ADD INDEX actionType (idActionType);

ALTER TABLE `${prefix}decision` ADD INDEX decisionProject (idProject),
ADD INDEX decisionType (idDecisionType),
ADD INDEX decisionUser (idUser),
ADD INDEX decisionResource (idResource),
ADD INDEX decisionStatus (idStatus);

ALTER TABLE `${prefix}filter` ADD INDEX filterUser (idUser);

ALTER TABLE `${prefix}filtercriteria` ADD INDEX filtercriteriaFilter (idFilter);

ALTER TABLE `${prefix}issue` ADD INDEX issuePriority (idPriority);

ALTER TABLE `${prefix}mail` ADD INDEX mailProject (idProject),
ADD INDEX mailUser (idUser),
ADD INDEX mailRef (refType, refId),
ADD INDEX mailStatus (idStatus);

ALTER TABLE `${prefix}meeting` ADD INDEX meetingProject (idProject),
ADD INDEX meetingType (idMeetingType),
ADD INDEX meetingUser (idUser),
ADD INDEX meetingResource (idResource),
ADD INDEX meetingStatus (idStatus);

ALTER TABLE `${prefix}planningelement` ADD INDEX planningelementPlanningMode (idPlanningMode);

ALTER TABLE `${prefix}project` ADD INDEX projectUser (idUser);

ALTER TABLE `${prefix}question` ADD INDEX questionProject (idProject),
ADD INDEX questionType (idQuestionType),
ADD INDEX questionUser (idUser),
ADD INDEX questionResource (idResource),
ADD INDEX questionStatus (idStatus);

ALTER TABLE `${prefix}report` ADD INDEX reportCategory (idReportCategory);

ALTER TABLE `${prefix}reportParameter` ADD INDEX reportparameterReport (idReport);

ALTER TABLE `${prefix}risk` ADD INDEX riskSeverity (idSeverity),
ADD INDEX riskLikelihood (idLikelihood),
ADD INDEX riskCriticality (idCriticality);

ALTER TABLE `${prefix}statusmail` ADD INDEX statusmailStatus (idStatus),
ADD INDEX statusmailMailable (idMailable);

ALTER TABLE `${prefix}ticket` ADD INDEX ticketUrgency (idUrgency),
ADD INDEX ticketPriority (idPriority),
ADD INDEX ticketCriticality (idCriticality);

ALTER TABLE `${prefix}type` ADD INDEX typeScope (scope);

ALTER TABLE `${prefix}user` ADD INDEX userProfile (idProfile),
ADD INDEX userTeam (idTeam);

ALTER TABLE `${prefix}workflowstatus` ADD INDEX workflowstatusProfile (idProfile),
ADD INDEX workflowstatusWorkflow (idWorkflow),
ADD INDEX workflowstatusStatusFrom (idStatusFrom),
ADD INDEX workflowstatusStatusTo (idStatusTo);

INSERT INTO `${prefix}reportcategory` (`id`, `name`, `order`) VALUES
(3, 'reportCategoryTicket', 30);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `order`) VALUES
(9, 'reportTicketYearly', 3, 'ticketYearlyReport.php', 10);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `order`, `defaultValue`) VALUES 
(12, 9, 'idProject', 'projectList', 10, 'currentProject'),
(13, 9, 'year', 'year', 20, 'currentYear'),
(14, 9, 'idTicketType', 'ticketType', 30, null),
(15, 9, 'issuer', 'userList', 40, null),
(16, 9, 'responsible', 'resourceList', 50, null);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `order`) VALUES
(10, 'reportTicketYearlyByType', 3, 'ticketYearlyReportByType.php', 20);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `order`, `defaultValue`) VALUES 
(17, 10, 'idProject', 'projectList', 10, 'currentProject'),
(18, 10, 'year', 'year', 20, 'currentYear'),
(19, 10, 'issuer', 'userList', 40, null),
(20, 10, 'responsible', 'resourceList', 50, null);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `order`) VALUES
(11, 'reportTicketWeeklyCrossReport', 3, 'ticketReport.php', 30),
(12, 'reportTicketMonthlyCrossReport', 3, 'ticketReport.php', 40),
(13, 'reportTicketYearlyCrossReport', 3, 'ticketReport.php', 50),
(14, 'reportTicketWeeklySynthesis', 3, 'ticketSynthesis.php', 60),
(15, 'reportTicketMonthlySynthesis', 3, 'ticketSynthesis.php', 70),
(16, 'reportTicketYearlySynthesis', 3, 'ticketSynthesis.php', 80);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `order`, `defaultValue`) VALUES 
(21, 11, 'idProject', 'projectList', 10, 'currentProject'),
(22, 11, 'week', 'week', 20, 'currentWeek'),
(23, 11, 'issuer', 'userList', 30, null),
(24, 11, 'responsible', 'resourceList', 40, null),
(25, 12, 'idProject', 'projectList', 10, 'currentProject'),
(26, 12, 'month', 'month', 20, 'currentMonth'),
(27, 12, 'issuer', 'userList', 30, null),
(28, 12, 'responsible', 'resourceList', 40, null),
(29, 13, 'idProject', 'projectList', 10, 'currentProject'),
(30, 13, 'year', 'year', 20, 'currentYear'),
(31, 13, 'issuer', 'userList', 30, null),
(32, 13, 'responsible', 'resourceList', 40, null),
(33, 14, 'idProject', 'projectList', 10, 'currentProject'),
(34, 14, 'week', 'week', 20, 'currentWeek'),
(35, 14, 'issuer', 'userList', 30, null),
(36, 14, 'responsible', 'resourceList', 40, null),
(37, 15, 'idProject', 'projectList', 10, 'currentProject'),
(38, 15, 'month', 'month', 20, 'currentMonth'),
(39, 15, 'issuer', 'userList', 30, null),
(40, 15, 'responsible', 'resourceList', 40, null),
(41, 16, 'idProject', 'projectList', 10, 'currentProject'),
(42, 16, 'year', 'year', 20, 'currentYear'),
(43, 16, 'issuer', 'userList', 30, null),
(44, 16, 'responsible', 'resourceList', 40, null);

UPDATE `${prefix}priority` set name='Critical priority'
where id=4 and name='Critical priority (immediate action required)';

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `order`) VALUES
(17, 'reportTicketGlobalCrossReport', 3, 'ticketReport.php', 55),
(18, 'reportTicketGlobalSynthesis', 3, 'ticketSynthesis.php', 85);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `order`, `defaultValue`) VALUES 
(45, 17, 'idProject', 'projectList', 10, 'currentProject'),
(46, 17, 'issuer', 'userList', 20, null),
(47, 17, 'responsible', 'resourceList', 30, null),
(48, 18, 'idProject', 'projectList', 10, 'currentProject'),
(49, 18, 'issuer', 'userList', 20, null),
(50, 18, 'responsible', 'resourceList', 30, null);

UPDATE `${prefix}report` set `order`=10 where `id`=7;

UPDATE `${prefix}report` set `order`=20 where `id`=8;

UPDATE `${prefix}report` set `order`=30 where `id`=4;

UPDATE `${prefix}report` set `order`=40 where `id`=5;

UPDATE `${prefix}report` set `order`=50 where `id`=6;

INSERT INTO `${prefix}reportcategory` (`id`, `name`, `order`) VALUES
(4, 'reportCategoryStatus', 40),
(5, 'reportCategoryHistory', 90);

INSERT INTO `${prefix}report` (`id`, `name`, `idReportCategory`, `file`, `order`) VALUES
(19, 'reportGlobalWorkPlanningWeekly', 2, 'globalWorkPlanning.php?scale=week', 60),
(20, 'reportGlobalWorkPlanningMonthly', 2, 'globalWorkPlanning.php?scale=month', 70),
(21, 'reportStatusOngoing', 4, 'status.php', 10),
(22, 'reportStatusAll', 4, 'status.php?showIdle=true', 20),
(23, 'reportRiskManagementPlan', 4, 'riskManagementPlan.php', 30),
(24, 'reportHistoryDeteled', 5, 'history.php?scope=deleted', 20),
(25, 'reportHistoryDetail', 5, 'history.php?scope=item', 20);

INSERT INTO `${prefix}reportparameter` (`id`, `idReport`, `name`, `paramType`, `order`, `defaultValue`) VALUES 
(51, 19, 'idProject', 'projectList', 10, 'currentProject'),
(52, 20, 'idProject', 'projectList', 10, 'currentProject'),
(53, 21, 'idProject', 'projectList', 10, 'currentProject'),
(54, 21, 'issuer', 'userList', 20, null),
(55, 21, 'responsible', 'resourceList', 30, null),
(56, 22, 'idProject', 'projectList', 10, 'currentProject'),
(57, 22, 'issuer', 'userList', 20, null),
(58, 22, 'responsible', 'resourceList', 30, null),
(59, 23, 'idProject', 'projectList', 10, 'currentProject'),
(61, 25, 'refType', 'objectList', 10, null),
(62, 25, 'refId', 'id', 20, null);

ALTER TABLE `${prefix}type` ADD `mandatoryDescription` int(1) unsigned DEFAULT '0',
ADD `mandatoryResultOnDone` int(1) unsigned DEFAULT '0',
ADD `mandatoryResourceOnHandled` int(1) unsigned DEFAULT '0';

UPDATE  `${prefix}type` set `mandatoryResultOnDone`=1,
`mandatoryResourceOnHandled`=1;

ALTER TABLE `${prefix}status` ADD `setHandledStatus` int(1) unsigned DEFAULT '0';

UPDATE `${prefix}status` set `setHandledStatus`=1
where `sortOrder`>=275;

UPDATE `${prefix}status` set `setdoneStatus`=0 
where `setdoneStatus` is null;

ALTER TABLE `${prefix}workflow` ADD `sortOrder` int(3) DEFAULT NULL;

ALTER TABLE `${prefix}type` ADD `lockHandled` int(1) unsigned DEFAULT '0',
ADD `lockDone` int(1) unsigned DEFAULT '0',
ADD `lockIdle` int(1) unsigned DEFAULT '0';

UPDATE `${prefix}type` SET `lockHandled`=1,
`lockDone`=1,
`lockIdle`=1;

ALTER TABLE `${prefix}ticket` ADD `handled` int(1) unsigned DEFAULT '0',
ADD `handledDateTime` datetime DEFAULT NULL;

ALTER TABLE `${prefix}activity` ADD `handled` int(1) unsigned DEFAULT '0',
ADD `handledDate` date DEFAULT NULL;

ALTER TABLE `${prefix}milestone` ADD `handled` int(1) unsigned DEFAULT '0',
ADD `handledDate` date DEFAULT NULL;

ALTER TABLE `${prefix}risk` ADD `handled` int(1) unsigned DEFAULT '0',
ADD `handledDate` date DEFAULT NULL;

ALTER TABLE `${prefix}action` ADD `handled` int(1) unsigned DEFAULT '0',
ADD `handledDate` date DEFAULT NULL;

ALTER TABLE `${prefix}issue` ADD `handled` int(1) unsigned DEFAULT '0',
ADD `handledDate` date DEFAULT NULL;

ALTER TABLE `${prefix}question` ADD `handled` int(1) unsigned DEFAULT '0',
ADD `handledDate` date DEFAULT NULL;

ALTER TABLE `${prefix}meeting` ADD `idleDate` date DEFAULT NULL,
ADD `handled` int(1) unsigned DEFAULT '0',
ADD `handledDate` date DEFAULT NULL,
ADD `done` int(1) unsigned DEFAULT '0',
ADD `doneDate` date DEFAULT NULL;

UPDATE `${prefix}ticket` set handled=1
where done=1;

UPDATE `${prefix}activity` set handled=1
where done=1;

UPDATE `${prefix}milestone` set handled=1
where done=1;

UPDATE `${prefix}risk` set handled=1
where done=1;

UPDATE `${prefix}action` set handled=1
where done=1;

UPDATE `${prefix}issue` set handled=1
where done=1;

UPDATE `${prefix}question` set handled=1
where done=1;

UPDATE `${prefix}ticket` set handled=1, done=1
where idle=1;

