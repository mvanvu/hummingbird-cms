SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `#__config_data`
--

CREATE TABLE IF NOT EXISTS `#__config_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `context` varchar(191) NOT NULL,
  `data` mediumtext,
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`(191))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__config_data`
--

INSERT INTO `#__config_data` (`id`,`context`,`data`,`ordering`) VALUES ('1','cms.menu.type','MainMenu','0'),('2','cms.config','{\"siteOffline\":\"N\",\"siteName\":\"Hummingbird CMS site name\",\"siteOfflineMsg\":\"This site is down for maintenance.<br \\/>Please check back again soon.\",\"listLimit\":15,\"siteMetaDesc\":\"\",\"siteMetaKeys\":\"\",\"siteRobots\":\"\",\"siteContentRights\":\"\",\"multilingual\":\"N\",\"siteLanguage\":\"en-GB\",\"administratorLanguage\":\"en-GB\",\"mainCurrency\":\"USD\",\"timezone\":\"Asia\\/Ho_Chi_Minh\",\"allowUserRegistration\":\"N\",\"allowUserApiRegistration\":\"N\",\"userEmailAsUsername\":\"Y\",\"newUserActivation\":\"E\",\"newUserApiActivation\":\"E\",\"mailToAdminWhenNewUser\":\"Y\",\"adminEmail\":\"admin@email.com\",\"gzip\":\"N\",\"development\":\"Y\",\"adminPrefix\":\"admin\",\"apiSecretKey\":\"82f4bc22-638e-4ac1-bca8-3e32e96acda9\",\"reCaptchaSiteKey\":\"\",\"reCaptchaSecretKey\":\"\",\"packagesChannel\":\"\",\"sysSendFromMail\":\"admin@email.com\",\"sysSendFromName\":\"Admin\",\"sysSmtpHost\":\"smtp.gmail.com\",\"sysSmtpPort\":0,\"sysSmtpSecurity\":\"ssl\",\"sysSmtpUsername\":\"\",\"sysSmtpPassword\":\"\"}','0'),('3','cms.menu.item','{\"id\":0,\"title\":\"My account\",\"icon\":\"user-o\",\"menu\":\"MainMenu\",\"type\":\"user-account\",\"target\":\"\",\"nofollow\":\"N\",\"templateId\":0,\"params\":[],\"parentId\":0}','0');

--
-- Table structure for table `#__currencies`
--

CREATE TABLE IF NOT EXISTS `#__currencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `state` enum('U','P','T') NOT NULL DEFAULT 'U',
  `code` char(7) NOT NULL,
  `rate` decimal(10,6) unsigned NOT NULL DEFAULT '1.000000',
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text,
  PRIMARY KEY (`id`),
  KEY `idx_code` (`code`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__currencies`
--

INSERT INTO `#__currencies` (`id`,`name`,`state`,`code`,`rate`,`createdAt`,`createdBy`,`modifiedAt`,`modifiedBy`,`checkedAt`,`checkedBy`,`params`) VALUES ('1','US dollar','P','USD','1.000000','2021-03-18 04:06:06','1','2021-03-18 04:30:16','1','','0','{\"symbol\":\"$\",\"decimals\":2,\"separator\":\",\",\"point\":\".\",\"format\":\"{symbol}{value}\"}'),('2','Vietnam Dong','P','VND','0.000044','2021-03-18 04:09:06','1','2021-03-18 04:27:11','1','','0','{\"symbol\":\"\\u20ab\",\"decimals\":0,\"separator\":\".\",\"point\":\",\",\"format\":\"{value}{symbol}\"}'),('3','Euro','P','EUR','0.900000','2021-03-18 04:29:23','1','2021-03-18 04:29:24','1','','0','{\"symbol\":\"\\u20ac\",\"decimals\":2,\"separator\":\",\",\"point\":\".\",\"format\":\"{symbol}{value}\"}');

--
-- Table structure for table `#__languages`
--

CREATE TABLE IF NOT EXISTS `#__languages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `state` enum('P','U','T') NOT NULL DEFAULT 'U',
  `code` char(7) NOT NULL,
  `sef` char(7) NOT NULL,
  `iso` char(3) NOT NULL,
  `protected` enum('Y','N') NOT NULL DEFAULT 'N',
  `direction` enum('LTR','RTL') NOT NULL DEFAULT 'LTR',
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text,
  PRIMARY KEY (`id`),
  KEY `idx_code` (`code`),
  KEY `idx_sef` (`sef`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__languages`
--

INSERT INTO `#__languages` (`id`,`name`,`state`,`code`,`sef`,`iso`,`protected`,`direction`,`createdAt`,`createdBy`,`modifiedAt`,`modifiedBy`,`checkedAt`,`checkedBy`,`params`) VALUES ('1','English (en-GB)','P','en-GB','en','GBR','Y','LTR','2021-03-17 16:09:21','1','2021-03-18 03:33:49','1','','0','{\"dateFormat\":\"l, d F Y\",\"dateTimeFormat\":\"l, d F Y H:i\"}'),('2','Tiếng việt','P','vi-VN','vi','VNM','Y','LTR','2021-03-17 16:09:27','1','2021-03-18 03:33:16','1','','0','{\"dateFormat\":\"l, d\\/m\\/Y\",\"dateTimeFormat\":\"l, d\\/m\\/Y H:i\"}');

--
-- Table structure for table `#__logs`
--

CREATE TABLE IF NOT EXISTS `#__logs` (
  `id` varchar(191) NOT NULL,
  `context` varchar(50) NOT NULL DEFAULT 'system',
  `message` text,
  `createdAt` datetime NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL,
  `userAgent` varchar(1000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__media`
--

CREATE TABLE IF NOT EXISTS `#__media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file` varchar(1000) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(50) NOT NULL,
  `mime` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__plugins`
--

CREATE TABLE IF NOT EXISTS `#__plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `group` varchar(191) NOT NULL,
  `version` char(6) NOT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'N',
  `protected` enum('Y','N') DEFAULT 'N',
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `params` json DEFAULT NULL,
  `manifest` json DEFAULT NULL,
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_active` (`active`),
  KEY `idx_group_name` (`group`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__plugins`
--

INSERT INTO `#__plugins` (`id`,`name`,`group`,`version`,`active`,`protected`,`createdAt`,`createdBy`,`checkedAt`,`checkedBy`,`modifiedAt`,`modifiedBy`,`params`,`manifest`,`ordering`) VALUES ('1','System','Cms','1.0','Y','Y','2021-02-03 20:40:07','3','','0','2021-03-16 07:57:29','1','{}','{\"name\": \"System\", \"group\": \"Cms\", \"title\": \"cms-plugin-title\", \"author\": \"Mai Vu\", \"version\": \"1.0\", \"authorUrl\": \"https://github.com/mvanvu\", \"updateUrl\": null, \"authorEmail\": \"mvanvu@gmail.com\", \"description\": \"cms-plugin-desc\"}','1');

--
-- Table structure for table `#__queue_jobs`
--

CREATE TABLE IF NOT EXISTS `#__queue_jobs` (
  `queueJobId` varchar(32) NOT NULL,
  `handler` varchar(255) NOT NULL,
  `state` enum('HANDLING','SCHEDULED','FAILED') NOT NULL DEFAULT 'HANDLING',
  `payload` longtext,
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL,
  `failedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`queueJobId`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__roles`
--

CREATE TABLE IF NOT EXISTS `#__roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('R','M','S') NOT NULL DEFAULT 'R',
  `description` varchar(500) NOT NULL DEFAULT '',
  `permissions` json DEFAULT NULL,
  `protected` enum('Y','N') NOT NULL DEFAULT 'N',
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__roles`
--

INSERT INTO `#__roles` (`id`,`name`,`type`,`description`,`permissions`,`protected`,`createdAt`,`createdBy`,`modifiedAt`,`modifiedBy`,`checkedAt`,`checkedBy`) VALUES ('1','Super user','S','Master super user','{}','Y','2021-02-07 11:19:52','1','2021-02-22 02:18:49','1','','0'),('2','Manager','M','Can manage the site but can&#39;t config','{\"tag\": {\"edit\": \"Y\", \"admin\": \"N\", \"create\": \"Y\", \"delete\": \"N\", \"manage\": \"Y\", \"editState\": \"N\", \"manageOwn\": \"Y\"}, \"post\": {\"edit\": \"Y\", \"admin\": \"N\", \"create\": \"N\", \"delete\": \"Y\", \"manage\": \"Y\", \"editState\": \"N\", \"manageOwn\": \"N\", \"manageField\": \"Y\", \"manageComment\": \"Y\", \"manageCategory\": \"Y\"}, \"user\": {\"edit\": \"Y\", \"admin\": \"N\", \"create\": \"Y\", \"delete\": \"N\", \"manage\": \"Y\", \"activate\": \"N\", \"manageOwn\": \"Y\"}, \"media\": {\"admin\": \"N\", \"delete\": \"N\", \"manage\": \"Y\", \"upload\": \"Y\", \"manageOwn\": \"Y\"}, \"cms-post\": {\"edit\": \"N\", \"admin\": \"N\", \"create\": \"N\", \"delete\": \"N\", \"manage\": \"N\", \"editState\": \"N\", \"manageOwn\": \"N\", \"manageField\": \"N\", \"manageComment\": \"N\", \"manageCategory\": \"N\"}, \"post-category\": {\"edit\": \"N\", \"admin\": \"N\", \"create\": \"N\", \"delete\": \"N\", \"manage\": \"Y\", \"editState\": \"Y\", \"manageOwn\": \"Y\"}}','Y','2021-02-07 11:19:52','1','2021-02-22 02:18:44','1','','0'),('3','Registered','R','Default user register','{}','Y','2021-02-07 11:19:52','1','2021-03-01 15:38:31','1','','0');

--
-- Table structure for table `#__sessions`
--

CREATE TABLE IF NOT EXISTS `#__sessions` (
  `id` varchar(191) NOT NULL,
  `data` longtext,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__socket_data`
--

CREATE TABLE IF NOT EXISTS `#__socket_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `context` varchar(191) NOT NULL,
  `message` json DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__tags`
--

CREATE TABLE IF NOT EXISTS `#__tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Table structure for table `#__templates`
--

CREATE TABLE IF NOT EXISTS `#__templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `isDefault` enum('Y','N') NOT NULL DEFAULT 'N',
  `params` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__templates`
--

INSERT INTO `#__templates` (`id`,`name`,`createdAt`,`createdBy`,`checkedAt`,`checkedBy`,`modifiedAt`,`modifiedBy`,`isDefault`,`params`) VALUES ('1','Sparrow - default template','2021-02-07 11:19:52','1','','0','2021-03-18 03:26:40','1','Y','{\"positions\":\"Aside\\r\\nFooter\"}');

--
-- Table structure for table `#__translations`
--

DROP TABLE IF EXISTS `#__translations`;
CREATE TABLE IF NOT EXISTS `#__translations` (
  `translationId` varchar(191) NOT NULL,
  `translatedValue` json DEFAULT NULL,
  PRIMARY KEY (`translationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__ucm_comments`
--

CREATE TABLE IF NOT EXISTS `#__ucm_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `state` enum('P','U','T') NOT NULL DEFAULT 'U',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `referenceContext` varchar(191) NOT NULL,
  `referenceId` bigint(20) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `userIp` varchar(191) NOT NULL,
  `userAgent` varchar(1000) DEFAULT NULL,
  `userName` varchar(191) NOT NULL,
  `userEmail` varchar(191) NOT NULL,
  `userComment` text NOT NULL,
  `userVote` int(1) unsigned NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_parentId` (`parentId`),
  KEY `idx_referenceContext` (`referenceContext`),
  KEY `idx_referenceId` (`referenceId`),
  KEY `idx_state` (`state`),
  KEY `idx_userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__ucm_field_groups`
--

CREATE TABLE IF NOT EXISTS `#__ucm_field_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `context` varchar(191) NOT NULL,
  `title` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__ucm_field_values`
--

CREATE TABLE IF NOT EXISTS `#__ucm_field_values` (
  `fieldId` int(10) unsigned NOT NULL,
  `itemId` int(10) unsigned NOT NULL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`fieldId`,`itemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Table structure for table `#__ucm_fields`
--

CREATE TABLE IF NOT EXISTS `#__ucm_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` int(10) unsigned NOT NULL DEFAULT '0',
  `context` varchar(191) NOT NULL,
  `state` enum('P','U','T') NOT NULL DEFAULT 'P',
  `label` varchar(255) NOT NULL,
  `type` char(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `params` mediumtext,
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`(191)),
  KEY `idx_groupId` (`groupId`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__ucm_item_map`
--

CREATE TABLE IF NOT EXISTS `#__ucm_item_map` (
  `context` varchar(191) NOT NULL,
  `itemId1` bigint(20) unsigned NOT NULL,
  `itemId2` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`itemId1`,`itemId2`,`context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__ucm_items`
--

CREATE TABLE IF NOT EXISTS `#__ucm_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `context` varchar(191) NOT NULL,
  `state` enum('P','U','T') NOT NULL DEFAULT 'P',
  `parentId` bigint(20) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `route` varchar(191) NOT NULL DEFAULT '',
  `image` text,
  `summary` varchar(1000) DEFAULT '',
  `description` mediumtext,
  `level` int(3) unsigned NOT NULL DEFAULT '1',
  `lft` int(6) NOT NULL,
  `rgt` int(6) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(255) NOT NULL DEFAULT '',
  `metaDesc` varchar(255) NOT NULL DEFAULT '',
  `metaKeys` varchar(255) NOT NULL DEFAULT '',
  `metaRobots` varchar(20) NOT NULL DEFAULT '',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `params` mediumtext,
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`(191)),
  KEY `idx_lft_rgt` (`lft`,`rgt`),
  KEY `idx_parentId` (`parentId`),
  KEY `idx_route` (`route`(191)),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Table structure for table `#__users`
--

CREATE TABLE IF NOT EXISTS `#__users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(225) NOT NULL,
  `roleId` int(10) unsigned NOT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'N',
  `lastVisitedDate` datetime DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `secret` varchar(255) NOT NULL,
  `params` mediumtext,
  `token` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_roleId` (`roleId`),
  KEY `idx_name` (`name`(100)),
  KEY `idx_active` (`roleId`),
  UNIQUE KEY `idx_username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__users`
--

INSERT INTO `#__users` (`id`,`name`,`email`,`username`,`password`,`roleId`,`active`,`lastVisitedDate`,`createdAt`,`createdBy`,`modifiedAt`,`modifiedBy`,`checkedAt`,`checkedBy`,`secret`,`params`,`token`) VALUES ('1','Super user','admin@email.com','admin','admin','1','Y','2019-09-21 07:18:35','2021-02-07 11:19:52','0','2021-03-17 15:16:44','1','','0','secret','{\"avatar\":null,\"timezone\":\"Asia\\/Ho_Chi_Minh\"}','');

SET FOREIGN_KEY_CHECKS = 1;