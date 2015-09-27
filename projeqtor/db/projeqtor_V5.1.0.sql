-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 5.1.0                                       //
-- // Date : 2015-07-30                                     //
-- ///////////////////////////////////////////////////////////
CREATE TABLE `${prefix}paymentdelay` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name`varchar(100),
  `days` int(3) unsigned DEFAULT NULL,
  `endOfMonth` int(1) DEFAULT 0,
  `sortOrder` int(3) DEFAULT 0,
  `idle` int(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}paymentdelay` (`id`, `name`, `days`, `endOfMonth`, `sortOrder`, `idle`) VALUES
(1, '15 days', 15, 0, 10, 0),
(2, '15 days end of month', 15, 1, 20, 0),
(3, '30 days', 30, 0, 30, 0),
(4, '30 days end of month', 30, 1, 40, 0),
(5, '45 days', 45, 0, 50, 0),
(6, '45 days end of month', 45, 1, 60, 0),
(7, '60 days', 60, 0, 70, 0),
(8, 'on order', 0, 0, 80, 0;

CREATE TABLE `${prefix}paymentmode` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name`varchar(100),
  `sortOrder` int(3) DEFAULT 0,
  `idle` int(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}paymentmode` (`id`, `name`, `sortOrder`, `idle`) VALUES
(1, 'bank transfer', 10, 0),
(2, 'cheque', 20, 0),
(4, 'credit card', 30, 0),
(5, 'virtual payment terminal', 40, 0),
(6, 'paypal', 50, 0);

CREATE TABLE `${prefix}payment` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name`varchar(100),
  `idBill` int(12) unsigned DEFAULT NULL,
  `paymentDate` date,
  `idPaymentMode` int(12) unsigned DEFAULT NULL,
  `idle` int(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE INDEX paymentBill ON `${prefix}payment` (idBill);

ALTER TABLE `${prefix}client` ADD `numTax` varchar(100) DEFAULT NULL,
ADD `idPaymentDelay` int(12) unsigned DEFAULT NULL,
CHANGE `designation` `designation`  varchar (100),
CHANGE `street` `street`  varchar (100),
CHANGE `complement` `complement`  varchar (100),
CHANGE `city` `city`  varchar (100),
CHANGE `state` `state`  varchar (100),
CHANGE `country` `country`  varchar (100);

CREATE TABLE `${prefix}unit` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `name`varchar(100),
  `namePlural` varchar(100),
  `sortOrder` int(3) DEFAULT 0,
  `idle` int(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

INSERT INTO `${prefix}unit` (`id`, `name`, `namePlural`, `sortOrder`, `idle`) VALUES
(1, 'piece', 'pieces', 10, 0),
(2, 'lot', 'lots', 20, 0),
(3, 'day', 'days', 30, 0),
(4, 'month', 'months', 40, 0);

ALTER TABLE `${prefix}billline` ADD `idUnit` int(12) unsigned DEFAULT NULL,
ADD `extra` int(1) UNSIGNED DEFAULT 0;

ALTER TABLE `${prefix}bill` ADD `idPaymentDelay` int(12) unsigned DEFAULT null,
ADD `paymentDueDate` date DEFAULT NULL;

ALTER TABLE `${prefix}quotation` ADD `idPaymentDelay` int(12) unsigned DEFAULT null,
CHANGE `initialWork` `untaxedAmount` DECIMAL(11,2) UNSIGNED,
ADD `tax` decimal(5,2) DEFAULT NULL,
ADD `fullAmount` decimal(12,2) DEFAULT NULL;

ALTER TABLE `${prefix}command` ADD `idPaymentDelay` int(12) unsigned DEFAULT null,
CHANGE `initialAmount` `untaxedAmount` DECIMAL(11,2) UNSIGNED,
ADD `tax` decimal(5,2) DEFAULT NULL,
ADD `fullAmount` decimal(12,2) DEFAULT NULL,
CHANGE `addAmount` `addUntaxedAmount` DECIMAL(11,2) UNSIGNED,
ADD `addFullAmount` decimal(12,2) DEFAULT NULL,
CHANGE `validatedAmount` `totalUntaxedAmount` DECIMAL(11,2) UNSIGNED,
ADD `totalFullAmount` decimal(12,2) DEFAULT NULL;