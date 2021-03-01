SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `#__config_data`
--

CREATE TABLE `#__config_data`
(
    `id`       int(10) unsigned                        NOT NULL AUTO_INCREMENT,
    `context`  varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `data`     mediumtext COLLATE utf8mb4_unicode_ci,
    `ordering` int(10) unsigned                        NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `#__config_data_context_index` (`context`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 4
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `#__config_data`
--

INSERT INTO `#__config_data`
VALUES (1, 'cms.menu.type', 'MainMenu', 0),
       (2, 'cms.config',
        '{\"siteOffline\":\"N\",\"siteName\":\"Hummingbird CMS\",\"siteOfflineMsg\":\"This site is down for maintenance.<br \\/>Please check back again soon.\",\"listLimit\":15,\"siteMetaDesc\":\"\",\"siteMetaKeys\":\"\",\"siteRobots\":\"\",\"siteContentRights\":\"\",\"multilingual\":\"N\",\"siteLanguage\":\"en-GB\",\"administratorLanguage\":\"en-GB\",\"timezone\":\"Asia\\/Ho_Chi_Minh\",\"allowUserRegistration\":\"N\",\"allowUserApiRegistration\":\"N\",\"userEmailAsUsername\":\"Y\",\"newUserActivation\":\"E\",\"newUserApiActivation\":\"E\",\"mailToAdminWhenNewUser\":\"Y\",\"adminEmail\":\"admin@email.com\",\"development\":\"Y\",\"adminPrefix\":\"admin\",\"apiSecretKey\":\"\",\"reCaptchaSiteKey\":\"\",\"reCaptchaSecretKey\":\"\",\"sysSendFromMail\":\"admin@email.com\",\"sysSendFromName\":\"Admin\",\"sysSmtpHost\":\"smtp.gmail.com\",\"sysSmtpPort\":0,\"sysSmtpSecurity\":\"ssl\",\"sysSmtpUsername\":\"\",\"sysSmtpPassword\":\"\"}',
        0),
       (3, 'cms.menu.item',
        '{\"id\":0,\"title\":\"My account\",\"icon\":\"user-o\",\"menu\":\"MainMenu\",\"type\":\"user-account\",\"target\":\"\",\"nofollow\":\"N\",\"templateId\":0,\"params\":[],\"parentId\":0}',
        0);

--
-- Table structure for table `#__logs`
--

CREATE TABLE `#__logs`
(
    `id`        bigint(20) unsigned                      NOT NULL AUTO_INCREMENT,
    `context`   varchar(50) COLLATE utf8mb4_unicode_ci   NOT NULL DEFAULT 'system',
    `stringKey` varchar(255) COLLATE utf8mb4_unicode_ci           DEFAULT NULL,
    `payload`   json                                              DEFAULT NULL,
    `createdAt` datetime                                 NOT NULL,
    `userId`    int(10) unsigned                         NOT NULL DEFAULT '0',
    `ip`        varchar(255) COLLATE utf8mb4_unicode_ci  NOT NULL,
    `userAgent` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `#__logs_context_index` (`context`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__media`
--

CREATE TABLE `#__media`
(
    `id`        int(10) unsigned                       NOT NULL AUTO_INCREMENT,
    `file`      varchar(1000) COLLATE utf8mb4_unicode_ci        DEFAULT NULL,
    `createdAt` datetime                               NOT NULL,
    `createdBy` int(10) unsigned                       NOT NULL DEFAULT '0',
    `type`      varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `mime`      varchar(255) COLLATE utf8mb4_unicode_ci         DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__plugins`
--

CREATE TABLE `#__plugins`
(
    `id`         int(10) unsigned                          NOT NULL AUTO_INCREMENT,
    `name`       varchar(191) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `group`      varchar(191) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `version`    char(6) COLLATE utf8mb4_unicode_ci        NOT NULL,
    `active`     enum ('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
    `protected`  enum ('Y','N') COLLATE utf8mb4_unicode_ci          DEFAULT 'N',
    `createdAt`  datetime                                  NOT NULL,
    `createdBy`  int(10) unsigned                          NOT NULL DEFAULT '0',
    `checkedAt`  datetime                                           DEFAULT NULL,
    `checkedBy`  int(10) unsigned                          NOT NULL DEFAULT '0',
    `modifiedAt` datetime                                           DEFAULT NULL,
    `modifiedBy` int(10) unsigned                          NOT NULL DEFAULT '0',
    `params`     json                                               DEFAULT NULL,
    `manifest`   json                                               DEFAULT NULL,
    `ordering`   int(10) unsigned                          NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_active` (`active`),
    KEY `idx_group_name` (`group`, `name`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `#__plugins`
--

INSERT INTO `#__plugins`
VALUES (1, 'System', 'Cms', '1.0', 'Y', 'Y', '2021-02-03 20:40:07', 3, null, 0, '2021-02-03 15:06:56', 3, '{}', '{
  "name": "System",
  "group": "Cms",
  "title": "cms-plugin-title",
  "author": "Mai Vu",
  "version": "1.0.0",
  "authorUrl": "https://github.com/mvanvu",
  "updateUrl": null,
  "authorEmail": "mvanvu@gmail.com",
  "description": "cms-plugin-desc"
}', 1);

--
-- Table structure for table `#__queue_jobs`
--

CREATE TABLE `#__queue_jobs`
(
    `queueJobId` varchar(32) COLLATE utf8mb4_unicode_ci    NOT NULL,
    `handler`    varchar(255) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `payload`    longtext COLLATE utf8mb4_unicode_ci,
    `priority`   tinyint(1) unsigned                       NOT NULL DEFAULT '2',
    `handling`   enum ('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
    `createdBy`  int(10) unsigned                          NOT NULL DEFAULT '0',
    `createdAt`  datetime                                  NOT NULL,
    `failedAt`   datetime                                           DEFAULT NULL,
    PRIMARY KEY (`queueJobId`),
    KEY `#__queue_jobs_handling_index` (`handling`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__roles`
--

CREATE TABLE `#__roles`
(
    `id`          int(10) unsigned                              NOT NULL AUTO_INCREMENT,
    `name`        varchar(255) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `type`        enum ('R','M','S') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'R',
    `description` varchar(500) COLLATE utf8mb4_unicode_ci       NOT NULL DEFAULT '',
    `permissions` json                                                   DEFAULT NULL,
    `protected`   enum ('Y','N') COLLATE utf8mb4_unicode_ci     NOT NULL DEFAULT 'N',
    `createdAt`   datetime                                      NOT NULL,
    `createdBy`   int(10) unsigned                              NOT NULL,
    `modifiedAt`  datetime                                               DEFAULT NULL,
    `modifiedBy`  int(10) unsigned                              NOT NULL DEFAULT '0',
    `checkedAt`   datetime                                               DEFAULT NULL,
    `checkedBy`   int(10) unsigned                              NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 4
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `#__roles`
--

INSERT INTO `#__roles`
VALUES (1, 'Super user', 'S', 'Master super user', '{}', 'Y', '2020-12-23 02:33:28', 3, '2021-01-18 09:52:59', 3, NULL,
        0),
       (2, 'Manager', 'M', 'Can manage the site but can&#39;t config', '{
         "tag": {
           "edit": "Y",
           "admin": "N",
           "create": "Y",
           "delete": "N",
           "manage": "Y",
           "editState": "N",
           "manageOwn": "Y"
         },
         "post": {
           "edit": "Y",
           "admin": "N",
           "create": "N",
           "delete": "Y",
           "manage": "Y",
           "editState": "N",
           "manageOwn": "N",
           "manageField": "Y",
           "manageComment": "Y",
           "manageCategory": "Y"
         },
         "user": {
           "edit": "Y",
           "admin": "N",
           "create": "Y",
           "delete": "N",
           "manage": "Y",
           "activate": "N",
           "manageOwn": "Y"
         },
         "media": {
           "admin": "N",
           "delete": "N",
           "manage": "Y",
           "upload": "Y",
           "manageOwn": "Y"
         },
         "cms-post": {
           "edit": "N",
           "admin": "N",
           "create": "N",
           "delete": "N",
           "manage": "N",
           "editState": "N",
           "manageOwn": "N",
           "manageField": "N",
           "manageComment": "N",
           "manageCategory": "N"
         },
         "post-category": {
           "edit": "N",
           "admin": "N",
           "create": "N",
           "delete": "N",
           "manage": "Y",
           "editState": "Y",
           "manageOwn": "Y"
         }
       }', 'Y', '2021-02-07 11:14:45', 1, '2021-02-07 11:16:22', 1, null, 0),
       (3, 'Registered', 'R', 'Default user register', '{}', 'Y', '2020-12-23 02:40:19', 3, '2021-02-04 03:19:26', 3,
        NULL, 0);

--
-- Table structure for table `#__sessions`
--

CREATE TABLE `#__sessions`
(
    `id`   varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `data` longtext COLLATE utf8mb4_unicode_ci,
    `time` int(10) unsigned                        NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `#__socket_data`
(
    `id`         bigint(20) unsigned                     NOT NULL AUTO_INCREMENT,
    `context`    varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `message`    json                                             DEFAULT NULL,
    `createdAt`  datetime                                NOT NULL,
    `createdBy`  int(10) unsigned                        NOT NULL DEFAULT '0',
    `modifiedAt` datetime                                         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `#__socket_data_context_index` (`context`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `#__tags`
(
    `id`         int(10) unsigned                        NOT NULL AUTO_INCREMENT,
    `title`      varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `slug`       varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `createdAt`  datetime                                NOT NULL,
    `createdBy`  int(10) unsigned                        NOT NULL DEFAULT '0',
    `modifiedAt` datetime                                         DEFAULT NULL,
    `modifiedBy` int(10) unsigned                        NOT NULL DEFAULT '0',
    `checkedAt`  datetime                                         DEFAULT NULL,
    `checkedBy`  int(10) unsigned                        NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `#__tags_slug_index` (`slug`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `#__templates`
(
    `id`         int(10) unsigned                          NOT NULL AUTO_INCREMENT,
    `name`       varchar(255) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `createdAt`  datetime                                  NOT NULL,
    `createdBy`  int(10) unsigned                          NOT NULL DEFAULT '0',
    `checkedAt`  datetime                                           DEFAULT NULL,
    `checkedBy`  int(10) unsigned                          NOT NULL DEFAULT '0',
    `modifiedAt` datetime                                           DEFAULT NULL,
    `modifiedBy` int(10) unsigned                          NOT NULL DEFAULT '0',
    `isDefault`  enum ('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
    `params`     mediumtext COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `#__templates`
--

INSERT INTO `#__templates`
VALUES (1, 'Sparrow - default template', '2021-01-09 09:44:23', 3, NULL, 0, '2021-02-06 10:12:49', 3, 'Y',
        '{\"positions\":\"Aside\\r\\nFooter\"}');

CREATE TABLE `#__translations`
(
    `translationId`   varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `translatedValue` json DEFAULT NULL,
    PRIMARY KEY (`translationId`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `#__ucm_comments`
(
    `id`               bigint(20) unsigned                           NOT NULL AUTO_INCREMENT,
    `state`            enum ('P','U','T') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'U',
    `parentId`         int(10) unsigned                              NOT NULL DEFAULT '0',
    `referenceContext` varchar(191) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `referenceId`      bigint(20) unsigned                           NOT NULL,
    `userId`           int(10) unsigned                              NOT NULL DEFAULT '0',
    `userIp`           varchar(191) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `userAgent`        varchar(1000) COLLATE utf8mb4_unicode_ci               DEFAULT NULL,
    `userName`         varchar(191) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `userEmail`        varchar(191) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `userComment`      text COLLATE utf8mb4_unicode_ci               NOT NULL,
    `userVote`         int(1) unsigned                               NOT NULL DEFAULT '0',
    `createdAt`        datetime                                      NOT NULL,
    `createdBy`        int(10) unsigned                              NOT NULL DEFAULT '0',
    `checkedAt`        datetime                                               DEFAULT NULL,
    `checkedBy`        int(10) unsigned                              NOT NULL DEFAULT '0',
    `modifiedAt`       datetime                                               DEFAULT NULL,
    `modifiedBy`       int(10) unsigned                              NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `#__ucm_comments_parentId_index` (`parentId`),
    KEY `#__ucm_comments_referenceContext_index` (`referenceContext`),
    KEY `#__ucm_comments_referenceId_index` (`referenceId`),
    KEY `#__ucm_comments_state_index` (`state`),
    KEY `#__ucm_comments_userId_index` (`userId`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
--
-- Table structure for table `#__ucm_field_groups`
--

CREATE TABLE `#__ucm_field_groups`
(
    `id`         int(10) unsigned                        NOT NULL AUTO_INCREMENT,
    `context`    varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `title`      varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `createdAt`  datetime                                NOT NULL,
    `createdBy`  int(10) unsigned                        NOT NULL DEFAULT '0',
    `modifiedAt` datetime                                         DEFAULT NULL,
    `modifiedBy` int(10) unsigned                        NOT NULL DEFAULT '0',
    `checkedAt`  datetime                                         DEFAULT NULL,
    `checkedBy`  int(10) unsigned                        NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `#__ucm_field_groups_context_index` (`context`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;


CREATE TABLE `#__ucm_field_values`
(
    `fieldId` int(10) unsigned                      NOT NULL,
    `itemId`  int(10) unsigned                      NOT NULL,
    `value`   mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`fieldId`, `itemId`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__ucm_fields`
--

CREATE TABLE `#__ucm_fields`
(
    `id`         bigint(20) unsigned                           NOT NULL AUTO_INCREMENT,
    `groupId`    int(10) unsigned                              NOT NULL DEFAULT '0',
    `context`    varchar(191) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `state`      enum ('P','U','T') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'P',
    `label`      varchar(255) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `type`       char(20) COLLATE utf8mb4_unicode_ci                    DEFAULT NULL,
    `name`       varchar(255) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `createdAt`  datetime                                      NOT NULL,
    `createdBy`  int(10) unsigned                              NOT NULL DEFAULT '0',
    `modifiedAt` datetime                                               DEFAULT NULL,
    `modifiedBy` int(10) unsigned                              NOT NULL DEFAULT '0',
    `checkedAt`  datetime                                               DEFAULT NULL,
    `checkedBy`  int(10) unsigned                              NOT NULL DEFAULT '0',
    `params`     mediumtext COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id`),
    KEY `#__ucm_fields_context_index` (`context`),
    KEY `#__ucm_fields_groupId_index` (`groupId`),
    KEY `#__ucm_fields_state_index` (`state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__ucm_item_map`
--

CREATE TABLE `#__ucm_item_map`
(
    `context` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `itemId1` bigint(20) unsigned                     NOT NULL,
    `itemId2` bigint(20) unsigned                     NOT NULL,
    PRIMARY KEY (`itemId1`, `itemId2`, `context`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__ucm_items`
--

CREATE TABLE `#__ucm_items`
(
    `id`          bigint(20) unsigned                           NOT NULL AUTO_INCREMENT,
    `context`     varchar(191) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `state`       enum ('P','U','T') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'P',
    `parentId`    bigint(20) unsigned                           NOT NULL DEFAULT '0',
    `title`       varchar(255) COLLATE utf8mb4_unicode_ci       NOT NULL,
    `route`       varchar(191) COLLATE utf8mb4_unicode_ci       NOT NULL DEFAULT '',
    `image`       text COLLATE utf8mb4_unicode_ci,
    `summary`     varchar(1000) COLLATE utf8mb4_unicode_ci               DEFAULT '',
    `description` mediumtext COLLATE utf8mb4_unicode_ci,
    `level`       int(3) unsigned                               NOT NULL DEFAULT '1',
    `lft`         int(6)                                        NOT NULL,
    `rgt`         int(6)                                                 DEFAULT NULL,
    `createdAt`   datetime                                      NOT NULL,
    `createdBy`   int(10) unsigned                              NOT NULL DEFAULT '0',
    `modifiedAt`  datetime                                               DEFAULT NULL,
    `modifiedBy`  int(10) unsigned                              NOT NULL DEFAULT '0',
    `checkedAt`   datetime                                               DEFAULT NULL,
    `checkedBy`   int(10) unsigned                              NOT NULL DEFAULT '0',
    `metaTitle`   varchar(255) COLLATE utf8mb4_unicode_ci       NOT NULL DEFAULT '',
    `metaDesc`    varchar(255) COLLATE utf8mb4_unicode_ci       NOT NULL DEFAULT '',
    `metaKeys`    varchar(255) COLLATE utf8mb4_unicode_ci       NOT NULL DEFAULT '',
    `metaRobots`  varchar(20) COLLATE utf8mb4_unicode_ci        NOT NULL DEFAULT '',
    `hits`        int(10) unsigned                              NOT NULL DEFAULT '0',
    `ordering`    int(10) unsigned                              NOT NULL DEFAULT '0',
    `params`      mediumtext COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id`),
    KEY `#__ucm_items_context_index` (`context`),
    KEY `#__ucm_items_lft_rgt_index` (`lft`, `rgt`),
    KEY `#__ucm_items_parentId_index` (`parentId`),
    KEY `#__ucm_items_route_index` (`route`),
    KEY `#__ucm_items_state_index` (`state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Table structure for table `#__users`
--

CREATE TABLE `#__users`
(
    `id`              int(10) unsigned                          NOT NULL AUTO_INCREMENT,
    `name`            varchar(70) COLLATE utf8mb4_unicode_ci    NOT NULL,
    `email`           varchar(255) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `username`        varchar(150) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `password`        varchar(225) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `roleId`          int(10) unsigned                          NOT NULL DEFAULT '3',
    `active`          enum ('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
    `lastVisitedDate` datetime                                           DEFAULT NULL,
    `createdAt`       datetime                                  NOT NULL,
    `createdBy`       int(10) unsigned                          NOT NULL DEFAULT '0',
    `modifiedAt`      datetime                                           DEFAULT NULL,
    `modifiedBy`      int(10) unsigned                          NOT NULL DEFAULT '0',
    `checkedAt`       datetime                                           DEFAULT NULL,
    `checkedBy`       int(10) unsigned                          NOT NULL DEFAULT '0',
    `secret`          varchar(255) COLLATE utf8mb4_unicode_ci   NOT NULL,
    `params`          mediumtext COLLATE utf8mb4_unicode_ci,
    `token`           varchar(40) COLLATE utf8mb4_unicode_ci             DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_roleId_idx` (`roleId`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `#__users`
--

INSERT INTO `#__users`
VALUES (1, 'Super user', 'admin@email.com', 'admin', '$2y$10$YVlSQUJybE9pdEJsZFVpb.VOQNhXLbZiVYUprV6JlsuIoMdpjxwle', 1,
        'Y', '2019-09-21 07:18:35', '2019-11-15 10:35:33', 0, '2021-02-04 07:09:49', 3, null, 0,
        '738f9424-f296-407a-87b7-780c1200b02a', '{"avatar":null,"timezone":"UTC"}', '');
SET FOREIGN_KEY_CHECKS = 1;
