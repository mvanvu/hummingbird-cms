SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
--
-- Table structure for table `#__config_data`
--

DROP TABLE IF EXISTS `#__config_data`;
CREATE TABLE `#__config_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `context` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` mediumtext COLLATE utf8mb4_unicode_ci,
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__config_data`
--

INSERT INTO `#__config_data` (`id`,`context`,`data`,`ordering`) VALUES ('2','cms.menu.type','MainMenu','0'),('4','cms.config','{\"siteName\":\"Hummingbird CMS site name\",\"siteOffline\":\"N\",\"siteOfflineMsg\":\"This site is down for maintenance.<br \\/>Please check back again soon.\",\"listLimit\":20,\"siteMetaDesc\":\"\",\"siteMetaKeys\":\"\",\"siteRobots\":\"\",\"siteContentRights\":\"\",\"siteLanguage\":\"en-GB\",\"administratorLanguage\":\"en-GB\",\"multilingual\":\"Y\",\"timezone\":\"Asia\\/Ho_Chi_Minh\",\"allowUserRegistration\":\"Y\",\"newUserActivation\":\"A\",\"mailToAdminWhenNewUser\":\"N\",\"adminEmail\":\"\",\"development\":\"Y\",\"adminPrefix\":\"admin\",\"sysSendFromMail\":\"maivubc@gmail.com\",\"sysSendFromName\":\"Phalcon CMS\",\"sysSmtpHost\":\"smtp.gmail.com\",\"sysSmtpPort\":465,\"sysSmtpSecurity\":\"ssl\",\"sysSmtpUsername\":\"vncvubc@gmail.com\",\"sysSmtpPassword\":\"vncvubcvncvubc\"}','0'),('19','cms.menu.type','TopA','0'),('20','cms.menu.item','{\"id\":20,\"title\":\"About us\",\"icon\":\"home\",\"menu\":\"TopA\",\"type\":\"link\",\"target\":\"\",\"nofollow\":\"N\",\"params\":{\"link\":\"#\"},\"parentId\":0}','0'),('22','cms.menu.item','{\"id\":22,\"title\":\"My account\",\"icon\":\"user\",\"menu\":\"TopA\",\"type\":\"user-account\",\"target\":\"\",\"nofollow\":\"N\",\"params\":[],\"parentId\":0}','2'),('26','cms.config.widget.item.code','{\"isCmsCore\":true,\"manifest\":{\"name\":\"Code\",\"version\":\"1.0.0\",\"author\":\"Mai vu (Rainy)\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"content\",\"type\":\"CmsEditorCode\",\"label\":\"widget-code-title\",\"translate\":true,\"filters\":[\"html\"]}]},\"title\":\"\",\"position\":\"Footer\",\"params\":{\"content\":\"<p class=\\\"uk-text-small uk-text-center\\\">\\r\\n            Copyright 2019 - <a href=\\\"https:\\/\\/www.joomtech.net\\/\\\">Created\\r\\n                by Mai Vu<\\/a> | Built with <a href=\\\"https:\\/\\/docs.phalcon.io\\/3.4\\/en\\/introduction\\\" title=\\\"Visit Phalcon site\\\" target=\\\"_blank\\\">Phalcon PHP<\\/a> and <a href=\\\"http:\\/\\/getuikit.com\\\" title=\\\"Visit UIkit 3 site\\\" target=\\\"_blank\\\">UIkit<\\/a><\\/p>\"}}','0'),('37','cms.menu.item','{\"id\":37,\"title\":\"Blog\",\"icon\":\"\",\"menu\":\"MainMenu\",\"type\":\"post-category\",\"target\":\"\",\"nofollow\":\"N\",\"params\":{\"categoryId\":110},\"parentId\":0}','0'),('38','cms.config.widget.item.flashnews','{\"isCmsCore\":true,\"manifest\":{\"name\":\"FlashNews\",\"version\":\"1.0.0\",\"author\":\"Mai Vu\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"categoryIds\",\"type\":\"CmsModalUcmItem\",\"context\":\"post-category\",\"multiple\":true,\"filters\":[\"uint\"]},{\"name\":\"postsNum\",\"type\":\"Number\",\"label\":\"limit-posts-number\",\"multiple\":true,\"filters\":[\"uint\"],\"min\":1,\"value\":5},{\"name\":\"displayLayout\",\"type\":\"Select\",\"label\":\"display-layout\",\"value\":\"FlashNews\",\"options\":{\"FlashNews\":\"slider-thumb-nav\",\"SliderNews\":\"sub-slider\",\"BlogList\":\"blog-list\",\"BlogStack\":\"blog-stack\"},\"rules\":[\"Options\"]},{\"name\":\"orderBy\",\"type\":\"Select\",\"label\":\"order-by\",\"value\":\"latest\",\"options\":{\"latest\":\"order-latest\",\"random\":\"order-random\",\"titleAsc\":\"order-title-asc\",\"titleDesc\":\"order-title-desc\"},\"rules\":[\"Options\"]}]},\"title\":\"\",\"position\":\"FlashNews\",\"params\":{\"categoryIds\":[110],\"postsNum\":5,\"displayLayout\":\"FlashNews\",\"orderBy\":\"latest\"}}','0'),('39','cms.config.widget.item.flashnews','{\"isCmsCore\":false,\"manifest\":{\"name\":\"FlashNews\",\"title\":\"widget-flash-news-title\",\"description\":\"widget-flash-news-desc\",\"version\":\"1.0.0\",\"author\":\"Mai Vu\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"categoryIds\",\"type\":\"CmsModalUcmItem\",\"context\":\"post-category\",\"multiple\":true,\"filters\":[\"uint\"]},{\"name\":\"postsNum\",\"type\":\"Number\",\"label\":\"limit-posts-number\",\"multiple\":true,\"filters\":[\"uint\"],\"min\":1,\"value\":5},{\"name\":\"displayLayout\",\"type\":\"Select\",\"label\":\"display-layout\",\"value\":\"FlashNews\",\"options\":{\"FlashNews\":\"slider-thumb-nav\",\"SliderNews\":\"sub-slider\",\"BlogList\":\"blog-list\",\"BlogStack\":\"blog-stack\"},\"rules\":[\"Options\"]},{\"name\":\"orderBy\",\"type\":\"Select\",\"label\":\"order-by\",\"value\":\"latest\",\"options\":{\"latest\":\"order-latest\",\"random\":\"order-random\",\"titleAsc\":\"order-title-asc\",\"titleDesc\":\"order-title-desc\"},\"rules\":[\"Options\"]}]},\"title\":\"Latest news\",\"position\":\"LatestNews\",\"params\":{\"categoryIds\":[110],\"postsNum\":2,\"displayLayout\":\"BlogList\",\"orderBy\":\"latest\"}}','0'),('40','cms.config.widget.item.flashnews','{\"isCmsCore\":true,\"manifest\":{\"name\":\"FlashNews\",\"version\":\"1.0.0\",\"author\":\"Mai Vu\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"categoryIds\",\"type\":\"CmsModalUcmItem\",\"context\":\"post-category\",\"multiple\":true,\"filters\":[\"uint\"]},{\"name\":\"postsNum\",\"type\":\"Number\",\"label\":\"limit-posts-number\",\"multiple\":true,\"filters\":[\"uint\"],\"min\":1,\"value\":5},{\"name\":\"displayLayout\",\"type\":\"Select\",\"label\":\"display-layout\",\"value\":\"FlashNews\",\"options\":{\"FlashNews\":\"slider-thumb-nav\",\"SliderNews\":\"sub-slider\",\"BlogList\":\"blog-list\",\"BlogStack\":\"blog-stack\"},\"rules\":[\"Options\"]},{\"name\":\"orderBy\",\"type\":\"Select\",\"label\":\"order-by\",\"value\":\"latest\",\"options\":{\"latest\":\"order-latest\",\"random\":\"order-random\",\"titleAsc\":\"order-title-asc\",\"titleDesc\":\"order-title-desc\"},\"rules\":[\"Options\"]}]},\"title\":\"Trending\",\"position\":\"Trending\",\"params\":{\"categoryIds\":[110],\"postsNum\":5,\"displayLayout\":\"SliderNews\",\"orderBy\":\"latest\"}}','0'),('41','cms.config.widget.item.flashnews','{\"isCmsCore\":true,\"manifest\":{\"name\":\"FlashNews\",\"version\":\"1.0.0\",\"author\":\"Mai Vu\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"categoryIds\",\"type\":\"CmsModalUcmItem\",\"context\":\"post-category\",\"multiple\":true,\"filters\":[\"uint\"]},{\"name\":\"postsNum\",\"type\":\"Number\",\"label\":\"limit-posts-number\",\"multiple\":true,\"filters\":[\"uint\"],\"min\":1,\"value\":5},{\"name\":\"displayLayout\",\"type\":\"Select\",\"label\":\"display-layout\",\"value\":\"FlashNews\",\"options\":{\"FlashNews\":\"slider-thumb-nav\",\"SliderNews\":\"sub-slider\",\"BlogList\":\"blog-list\",\"BlogStack\":\"blog-stack\"},\"rules\":[\"Options\"]},{\"name\":\"orderBy\",\"type\":\"Select\",\"label\":\"order-by\",\"value\":\"latest\",\"options\":{\"latest\":\"order-latest\",\"random\":\"order-random\",\"titleAsc\":\"order-title-asc\",\"titleDesc\":\"order-title-desc\"},\"rules\":[\"Options\"]}]},\"title\":\"Random articles\",\"position\":\"Aside\",\"params\":{\"categoryIds\":[110],\"postsNum\":5,\"displayLayout\":\"BlogStack\",\"orderBy\":\"random\"}}','0'),('42','cms.config.widget.item.languageswitcher','{\"isCmsCore\":false,\"manifest\":{\"name\":\"LanguageSwitcher\",\"version\":\"1.0.0\",\"author\":\"Mai Vu\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"displayLayout\",\"type\":\"Select\",\"value\":\"Dropdown\",\"label\":\"display-layout\",\"options\":{\"SubNav\":\"sub-nav\",\"Flag\":\"flags\",\"Dropdown\":\"dropdown\"},\"rules\":[\"Options\"]}]},\"title\":\"\",\"position\":\"TopB\",\"params\":{\"displayLayout\":\"SubNav\"}}','0'),('43','cms.config.widget.item.login','{\"isCmsCore\":true,\"manifest\":{\"name\":\"Login\",\"version\":\"1.0.0\",\"author\":\"Mai Vu\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null},\"title\":\"Login\",\"position\":\"Aside\",\"params\":[]}','2'),('44','cms.config.widget.item.code','{\"isCmsCore\":true,\"manifest\":{\"name\":\"Code\",\"version\":\"1.0.0\",\"author\":\"Mai vu (Rainy)\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"content\",\"type\":\"CmsEditorCode\",\"label\":\"widget-code-title\",\"filters\":[\"html\"]}]},\"title\":\"Social icons\",\"position\":\"TopB\",\"params\":{\"content\":\"<div class=\\\"uk-navbar-item\\\">\\r\\n\\t<a class=\\\"uk-visible@s\\\" style=\\\"margin-right: 4px\\\" href=\\\"#\\\" uk-icon=\\\"facebook\\\"><\\/a>\\r\\n\\t<a class=\\\"uk-visible@s\\\" style=\\\"margin-right: 4px\\\" href=\\\"#\\\" uk-icon=\\\"twitter\\\"><\\/a>\\r\\n\\t<a class=\\\"uk-visible@s\\\" style=\\\"margin-right: 4px\\\" href=\\\"#\\\" uk-icon=\\\"instagram\\\"><\\/a>                    \\r\\n<\\/div>\"}}','1'),('56','cms.config.widget.item.code','{\"isCmsCore\":true,\"manifest\":{\"name\":\"Code\",\"title\":\"widget-code-title\",\"description\":\"widget-code-desc\",\"version\":\"1.0.0\",\"author\":\"Mai vu (Rainy)\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"content\",\"type\":\"CmsEditorCode\",\"label\":\"widget-code-title\",\"translate\":true,\"filters\":[\"html\"]}]},\"title\":\"Hummingbird CMS\",\"position\":\"Aside\",\"params\":{\"content\":\"<div class=\\\"uk-card uk-card-body uk-background-muted\\\">Hummingbird CMS is a open source software which created by Mai Vu and built with PHP Phalcon v4 and UIkit v3.<\\/div>\"}}','1'),('57','cms.config.plugin.system.backup','{\"manifest\":{\"name\":\"Backup\",\"group\":\"System\",\"title\":\"backup-plugin-title\",\"description\":\"backup-plugin-desc\",\"version\":\"1.0.0\",\"author\":\"Mai vu\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"backup\",\"type\":\"CmsBackup\",\"label\":\"backup-manage\"}]},\"active\":true,\"isCmsCore\":false,\"params\":[]}','0'),('58','cms.config.plugin.system.cms','{\"manifest\":{\"name\":\"Cms\",\"group\":\"System\",\"title\":\"cms-plugin-title\",\"description\":\"cms-plugin-desc\",\"version\":\"1.0.0\",\"author\":\"Mai vu\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null},\"active\":true,\"isCmsCore\":true,\"params\":[]}','0');

--
-- Table structure for table `#__media`
--

DROP TABLE IF EXISTS `#__media`;
CREATE TABLE `#__media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__media`
--

INSERT INTO `#__media` (`id`,`file`,`createdAt`,`createdBy`,`type`,`mime`) VALUES ('2','blog1.jpg','2019-10-14 02:55:00','3','image','image/jpeg'),('3','blog2.jpg','2019-10-14 02:55:00','3','image','image/jpeg'),('4','blog3.jpg','2019-10-14 02:55:00','3','image','image/jpeg'),('5','blog4.jpg','2019-10-14 02:55:00','3','image','image/jpeg'),('6','blog5.jpg','2019-10-14 02:55:00','3','image','image/jpeg'),('7','blog6.jpg','2019-10-14 02:55:00','3','image','image/jpeg'),('8','Ba-na-hills.jpeg','2019-10-18 08:36:40','3','image','image/jpeg');

--
-- Table structure for table `#__tags`
--

DROP TABLE IF EXISTS `#__tags`;
CREATE TABLE `#__tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__tags`
--

INSERT INTO `#__tags` (`id`,`title`,`slug`,`createdAt`,`createdBy`,`modifiedAt`,`modifiedBy`,`checkedAt`,`checkedBy`) VALUES ('1','Blog','tag-blog','2019-11-15 04:46:50','3','2019-11-18 10:33:55','3','0000-00-00 00:00:00','0');

--
-- Table structure for table `#__translations`
--

DROP TABLE IF EXISTS `#__translations`;
CREATE TABLE `#__translations` (
  `translationId` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `originalValue` text COLLATE utf8mb4_unicode_ci,
  `translatedValue` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`translationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__translations`
--

INSERT INTO `#__translations` (`translationId`,`originalValue`,`translatedValue`) VALUES ('vi-VN.config_data.id=26.data','{\"isCmsCore\":true,\"manifest\":{\"name\":\"Code\",\"version\":\"1.0.0\",\"author\":\"Mai vu (Rainy)\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"content\",\"type\":\"CmsEditorCode\",\"label\":\"widget-code-title\",\"translate\":true,\"filters\":[\"html\"]}]},\"title\":\"\",\"position\":\"Footer\",\"params\":{\"content\":\"<p class=\\\"uk-text-small uk-text-center\\\">\\r\\n            Copyright 2019 - <a href=\\\"https:\\/\\/www.joomtech.net\\/\\\">Created\\r\\n                by Mai Vu<\\/a> | Built with <a href=\\\"https:\\/\\/docs.phalcon.io\\/3.4\\/en\\/introduction\\\" title=\\\"Visit Phalcon site\\\" target=\\\"_blank\\\">Phalcon PHP<\\/a> and <a href=\\\"http:\\/\\/getuikit.com\\\" title=\\\"Visit UIkit 3 site\\\" target=\\\"_blank\\\">UIkit<\\/a><\\/p>\"}}','{\"isCmsCore\":true,\"manifest\":{\"name\":\"Code\",\"version\":\"1.0.0\",\"author\":\"Mai vu (Rainy)\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"content\",\"type\":\"CmsEditorCode\",\"label\":\"widget-code-title\",\"translate\":true,\"filters\":[\"html\"]}]},\"title\":\"\",\"position\":\"Footer\",\"params\":{\"content\":\"<p class=\\\"uk-text-small uk-text-center\\\">\\r\\n            Copyright 2019 - <a href=\\\"https:\\/\\/www.joomtech.net\\/\\\">Ph\\u00e1t tri\\u1ec3n  Mai Vu<\\/a> | D\\u1ef1a tr\\u00ean c\\u00e1c n\\u1ec1n t\\u1ea3n m\\u00e3 ngu\\u1ed3n m\\u1edf <a href=\\\"https:\\/\\/docs.phalcon.io\\/3.4\\/en\\/introduction\\\" title=\\\"Visit Phalcon site\\\" target=\\\"_blank\\\">Phalcon PHP<\\/a> v\\u00e0 <a href=\\\"http:\\/\\/getuikit.com\\\" title=\\\"Visit UIkit 3 site\\\" target=\\\"_blank\\\">UIkit<\\/a><\\/p>\"}}'),('vi-VN.config_data.id=56.data','{\"isCmsCore\":true,\"manifest\":{\"name\":\"Code\",\"title\":\"widget-code-title\",\"description\":\"widget-code-desc\",\"version\":\"1.0.0\",\"author\":\"Mai vu (Rainy)\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"content\",\"type\":\"CmsEditorCode\",\"label\":\"widget-code-title\",\"translate\":true,\"filters\":[\"html\"]}]},\"title\":\"Hummingbird CMS\",\"position\":\"Aside\",\"params\":{\"content\":\"<div class=\\\"uk-card uk-card-body uk-background-muted\\\">Hummingbird CMS is a open source software which created by Mai Vu and built with PHP Phalcon v4 and UIkit v3.<\\/div>\"}}','{\"isCmsCore\":true,\"manifest\":{\"name\":\"Code\",\"title\":\"widget-code-title\",\"description\":\"widget-code-desc\",\"version\":\"1.0.0\",\"author\":\"Mai vu (Rainy)\",\"authorEmail\":\"rainy@joomtech.net\",\"authorUrl\":\"https:\\/\\/www.joomtech.net\",\"updateUrl\":null,\"params\":[{\"name\":\"content\",\"type\":\"CmsEditorCode\",\"label\":\"widget-code-title\",\"translate\":true,\"filters\":[\"html\"]}]},\"title\":\"Chim ru\\u1ed3i bay l\\u1ea1c\",\"position\":\"Aside\",\"params\":{\"content\":\"<div class=\\\"uk-card uk-card-body uk-background-muted\\\">Hummingbird CMS - chim ru\\u1ed3i bay l\\u1ea1c l\\u00e0 ph\\u1ea7n m\\u1ec1m m\\u00e3 ngu\\u1ed3n m\\u1edf \\u0111\\u01b0\\u1ee3c ph\\u00e1t tri\\u1ec3n b\\u1edfi t\\u00e1c gi\\u1ea3 Mai V\\u0169 d\\u1ef1a tr\\u00ean PHP Phalcon v4 v\\u00e0 UIkit v3.<\\/div>\"}}'),('vi-VN.tags.id=1.slug','tag-blog','the-blog'),('vi-VN.ucm_items.id=111.route','blog/the-list-of-the-best-coffee-in-the-world-the-different-types-of-coffee','danh-sach-cac-loai-ca-phe-tot-nhat-tren-the-gioi-cac-loai-ca-phe-khac-nhau'),('vi-VN.ucm_items.id=111.title','The List of the Best Coffee in the World (The Different Types of Coffee)','Danh sách các loại cà phê tốt nhất trên thế giới (Các loại cà phê khác nhau)'),('vi-VN.ucm_items.id=114.route','blog/saigon-beer-is-leading-brand-name-in-vietnam-beer-industry-with-about-140-years-of-developing','bia-sai-gon-la-thuong-hieu-hang-dau-trong-nganh-bia-viet-nam-voi-khoang-140-nam-phat-trien'),('vi-VN.ucm_items.id=114.summary','Saigon Beer is leading brand name in Vietnam beer industry with about 140 years of developing. Saigon Beer is recognized as National Brand and also honored to be the 351st member in Berlin Beer Academy – one of the cradle of global beer culture.','Bia Sài Gòn là thương hiệu hàng đầu trong ngành bia Việt Nam với khoảng 140 năm phát triển. Bia Sài Gòn được công nhận là Thương hiệu Quốc gia và cũng vinh dự là thành viên thứ 351 trong Học viện Bia Berlin - một trong những cái nôi của văn hóa bia toàn cầu.'),('vi-VN.ucm_items.id=114.title','Saigon Beer is leading brand name in Vietnam beer industry with about 140 years of developing','Bia Sài Gòn là thương hiệu hàng đầu trong ngành bia Việt Nam với khoảng 140 năm phát triển');

--
-- Table structure for table `#__ucm_comments`
--

DROP TABLE IF EXISTS `#__ucm_comments`;
CREATE TABLE `#__ucm_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `state` enum('P','U','T') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'U',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `referenceContext` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referenceId` bigint(20) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `userIp` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userAgent` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userName` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userEmail` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userComment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `userVote` int(1) unsigned NOT NULL DEFAULT '0',
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_parentId` (`parentId`),
  KEY `idx_referenceContext` (`referenceContext`),
  KEY `idx_referenceId` (`referenceId`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Table structure for table `#__ucm_field_groups`
--

DROP TABLE IF EXISTS `#__ucm_field_groups`;
CREATE TABLE `#__ucm_field_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `context` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Table structure for table `#__ucm_field_values`
--

DROP TABLE IF EXISTS `#__ucm_field_values`;
CREATE TABLE `#__ucm_field_values` (
  `fieldId` int(10) unsigned NOT NULL,
  `itemId` int(10) unsigned NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`fieldId`,`itemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__ucm_field_values`
--

INSERT INTO `#__ucm_field_values` (`fieldId`,`itemId`,`value`) VALUES ('3','111','2019-11-17 00:00:00');

--
-- Table structure for table `#__ucm_fields`
--

DROP TABLE IF EXISTS `#__ucm_fields`;
CREATE TABLE `#__ucm_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` int(10) unsigned NOT NULL DEFAULT '0',
  `context` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` enum('P','U','T') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'P',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` char(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `params` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_groupId` (`groupId`),
  KEY `idx_context` (`context`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
-- Table structure for table `#__ucm_item_map`
--

DROP TABLE IF EXISTS `#__ucm_item_map`;
CREATE TABLE `#__ucm_item_map` (
  `context` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `itemId1` bigint(20) unsigned NOT NULL,
  `itemId2` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`itemId1`,`itemId2`,`context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__ucm_item_map`
--

INSERT INTO `#__ucm_item_map` (`context`,`itemId1`,`itemId2`) VALUES ('tag','111','1');

--
-- Table structure for table `#__ucm_items`
--

DROP TABLE IF EXISTS `#__ucm_items`;
CREATE TABLE `#__ucm_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `context` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` enum('P','U','T') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'P',
  `parentId` bigint(20) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image` text COLLATE utf8mb4_unicode_ci,
  `summary` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `level` int(3) unsigned NOT NULL DEFAULT '1',
  `lft` int(6) NOT NULL,
  `rgt` int(6) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `metaDesc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `metaKeys` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `params` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_context` (`context`),
  KEY `idx_state` (`state`),
  KEY `idx_parentId` (`parentId`),
  KEY `idx_route` (`route`(768)),
  KEY `idx_lft_rgt` (`lft`,`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__ucm_items`
--

INSERT INTO `#__ucm_items` (`id`,`context`,`state`,`parentId`,`title`,`route`,`image`,`summary`,`description`,`level`,`lft`,`rgt`,`createdAt`,`createdBy`,`modifiedAt`,`modifiedBy`,`checkedAt`,`checkedBy`,`metaTitle`,`metaDesc`,`metaKeys`,`hits`,`ordering`,`params`) VALUES ('106','post-category','P','0','system-node-root','','','','','0','0','3','2019-11-15 09:47:03','0','0000-00-00 00:00:00','0','0000-00-00 00:00:00','0','','','','0','0',''),('107','product-category','P','0','system-node-root','','','','','0','0','1','2019-11-15 09:47:03','0','0000-00-00 00:00:00','0','0000-00-00 00:00:00','0','','','','0','0',''),('109','product-brand','P','0','system-node-root','','','','','1','0','1','2019-11-15 09:47:03','0','0000-00-00 00:00:00','0','0000-00-00 00:00:00','0','','','','0','0',''),('110','post-category','P','106','Blog','blog','[]','','','1','1','2','2019-11-15 09:47:03','0','2019-12-20 09:22:02','3','','0','','','','13','1','{\"allowUserComment\":\"Y\",\"commentAsGuest\":\"N\",\"autoPublishComment\":\"N\",\"commentWithEmoji\":\"N\"}'),('111','post','P','110','The List of the Best Coffee in the World (The Different Types of Coffee)','blog/the-list-of-the-best-coffee-in-the-world-the-different-types-of-coffee','[\"blog1.jpg\"]','Coffee is one of the most loved drinks in the world and the second biggest product in the world after petroleum. Hopefully, this article will give you an idea how massive and deeply rooted our love affair with coffee is.','<p><strong>The History &amp; Benefits:&nbsp;</strong></p>\r\n<p>Coffee is believed to have originated from Ethiopia as the coffee plant grows there naturally. Around 500 &ndash; &nbsp;800 A.D, the coffee plant was then taken to Yemen by one of the great masters, who was based in the port city of Mocha.</p>\r\n<p>Hence, the name of the popular coffee flavour came from this city.</p>\r\n<p>From then, coffee has been one of the most loved beverages in Asia, due to its stimulants and its sleep reducing effects. Around 1615, the buzz about coffee had reached Europe through travellers and merchants...</p>\r\n<p><em>Source: <a href=\"https://everythingzany.com/\">https://everythingzany.com</a></em></p>','1','0','1','2019-11-15 09:47:03','0','2019-12-23 11:10:56','3','','0','','','','22','1','{\"allowUserComment\":\"Y\",\"commentAsGuest\":\"Y\",\"autoPublishComment\":\"Y\",\"commentWithEmoji\":\"Y\"}'),('112','post','P','110','Best Coffee In the World (A Gourmet’s Guide to 30 Types of Coffee)','blog/best-coffee-in-the-world-a-gourmets-guide-to-30-types-of-coffee','[\"blog2.jpg\"]','One of our favorite things about traveling around the world has been the opportunity it affords us to sample the best food, wine, and spirits the cultures we explore have to offer.','<p>I&rsquo;ve been a hardcore coffee fanatic since I was 15 years old, making my dad a steaming hot cup every morning. And we&rsquo;ve been fortunate to try some of the best coffee in the world on our adventures.</p>\r\n<p>From staying on a Kona Coffee plantation on the island of Hawaii to picking, shelling, and fire-roasting our own at a farm on Mount Kilimanjaro, we&rsquo;ve been fortunate to try countless different types of coffee fresh from the source.</p>\r\n<p>The aromas are more intense, the flavors are bolder, and the experience of planting your feet on the field in which it was grown somehow seems to make every cup more enjoyable.</p>\r\n<h6><em>Source: <a href=\"https://greenglobaltravel.com/\">https://greenglobaltravel.com</a></em></h6>','1','0','1','2019-11-15 09:47:03','0','2019-12-23 11:11:03','3','','0','','','','15','1','{\"allowUserComment\":\"Y\",\"commentAsGuest\":\"Y\",\"autoPublishComment\":\"Y\",\"commentWithEmoji\":\"Y\"}'),('114','post','P','110','Saigon Beer is leading brand name in Vietnam beer industry with about 140 years of developing','blog/saigon-beer-is-leading-brand-name-in-vietnam-beer-industry-with-about-140-years-of-developing','[\"blog4.jpg\"]','Saigon Beer is leading brand name in Vietnam beer industry with about 140 years of developing. Saigon Beer is recognized as National Brand and also honored to be the 351st member in Berlin Beer Academy – one of the cradle of global beer culture.','<p><strong>SABECO</strong>&nbsp;beer producer has factories in every part of Vietnam; however, it is more preferred in the South and Centre Vietnam market than the Northern market. The major products of this producer is bottled beer, however canned beer brand &ldquo;<strong>333</strong>&rdquo; is also popular with a reasonable price. The bottled beer product line of SABECO includes four brands:</p>\r\n<p><strong>333 Premium</strong>: This is a high standard product of SABECO that is not only popular in Vietnam but also in some other countries. The beer is packaged in 330ml glass bottle with the alcoholic level of 5.3 %. &nbsp;</p>\r\n<p>Vietnamese consumers are familiar with the brand: Saigon Beer (or Bia Saigon) of Saigon Beer-Alcohol-Beverage Corporation (Sabeco). In 2017, Saigon Beer has been over 142-year original history, 40-year brand building and development. Since that 142 milestone, the yellow beer bubbles flow has continuously kept up the future way, always made Vietnamese proud of their products.</p>\r\n<p>The unique taste of Saigon Beer is the inspiring taste combined with the spirit of Saigonese&rsquo;s generosity and the rich of the Southern land, making it an in dispensable part of everyday life. With 2 bottle of 610 ml Larue bottle and 330 ml bottle of beer in the first takeover period. Until now, Saigon beer has developed 8 type of products such as Saigon Lager 450 bottle, Saigon Export Beer bottle, Saigon Special bottle, Saigon Lager 355 bottle, 333 Premium, 333 beer can, Saigon Special can, Saigon Lager can.</p>\r\n<p>From a small production of 21.5 million liters in 1977, after 39 years of development, by 2016, Saigon Beer has achieved 1.59 billion liters of output, striving to achieve 1.66 billion liters til 2017. Although the market has appeared many famous beer brands in the world, Saigon Beer is still Vietnam\'s leading beer market in Vietnam, on the way to conquer the markets such as Germany, the US, Japan, the Netherlands and so on.</p>','1','0','1','2019-11-15 09:47:03','0','2019-12-23 11:11:06','3','','0','Saigon Beer is leading brand name in Vietnam beer industry with about 140 years of developing','','','35','1','{\"allowUserComment\":\"Y\",\"commentAsGuest\":\"Y\",\"autoPublishComment\":\"Y\",\"commentWithEmoji\":\"Y\"}'),('115','post','P','110','Things to do at Ba Na Hills, Danang','blog/things-to-do-at-ba-na-hills-danang','[\"blog5.jpg\"]','Lorem ipsum, or lipsum as it is sometimes known, is dummy text used in laying out print, graphic or web designs. The passage is attributed to an unknown typesetter in the 15th century who is thought to have scrambled parts of Cicero&#39;s De Finibus Bonorum et Malorum for use in a type specimen book.','<p>If you were still doubting it, 2018 just proved that Ba Na Hills, if not the must-see place in<strong>&nbsp;Da Nang</strong>, then it&rsquo;s certainly the most attractive right now. The opening of Golden Bridge has given Ba Na hills the popularity like never before. But if you don&rsquo;t know much about Ba Na Hills other than the Golden Bridge, then our article will help you to spend a wonderful day here. Are you ready?</p>\r\n<h4>What You&rsquo;ll Experience</h4>\r\n<p>Whether it\'s your first time in Da Nang or you\'ve been here many times before, Ba Na Hills should always be on your itinerary. To get to Ba Na Hills, you will hitch a ride on the park\'s cable car network and glide over the stunning mountains and forests. Once you get to the park, the fun begins.</p>\r\n<p><img src=\"/upload/Ba-na-hills.jpeg\" alt=\"\" /></p>\r\n<p>There\'s Fantasy Park, an entertainment hub with more than 105 arcade machines and attractions. There\'s French Village, a village built with distinct French Medieval architectural style, which provides the perfect background for any photoshoot. And then there\'s the park\'s cr&egrave;me de la cr&egrave;me &mdash; the Golden Bridge. Voted by TIME magazine in 2018 as one of the world\'s 100 greatest places, the bridge is where you play the audience to breathtaking panoramic views of Ba Na Hills. So what are you waiting for?</p>\r\n<p>&nbsp;</p>','1','0','1','2019-11-15 09:47:03','0','2019-12-23 11:11:12','3','','0','','','','14','1','{\"allowUserComment\":\"Y\",\"commentAsGuest\":\"Y\",\"autoPublishComment\":\"Y\",\"commentWithEmoji\":\"Y\"}'),('116','post','P','110','Welcome to Halong Bay','welcome-to-halong-bay','[\"blog6.jpg\"]','Towering limestone pillars and tiny islets topped by forest rise from the emerald waters of the Gulf of Tonkin. Designated a World Heritage Site in 1994, Halong Bay&#39;s scatter of islands, dotted with wind- and wave-eroded grottoes, is a vision of ethereal beauty and, unsurprisingly, northern Vietnam&#39;s number one-tourism hub.','<p>Sprawling Halong City (also known as Bai Chay) is the bay\'s main gateway, but its high-rises are a disappointing doorstep to this site. Most visitors opt for cruise tours that include sleeping on board within the bay, while a growing number are deciding to eschew the main bay completely, heading straight for Cat Ba Island from where trips to less-visited but equally alluring Lan Ha Bay are easily set up.</p>\r\n<p>All visitors must purchase entry tickets for the national park (40,000d) and there are also separate admission tickets for attractions in the bay, such as caves and fishing villages (30,000d to 50,000d).</p>\r\n<p><iframe src=\"//players.brightcove.net/5104226627001/default_default/index.html?videoId=5799942340001\" width=\"100%\" height=\"480\" allowfullscreen=\"allowfullscreen\"></iframe></p>\r\n<p>Source: <a class=\"uk-link-muted\" href=\"https://www.lonelyplanet.com/vietnam/northeast-vietnam/halong-bay\" target=\"_blank\" rel=\"noopener\">https://www.lonelyplanet.com/vietnam/northeast-vietnam/halong-bay</a></p>','1','0','1','2019-11-15 09:47:03','0','2019-12-23 11:11:18','3','','0','','','','14','1','{\"allowUserComment\":\"Y\",\"commentAsGuest\":\"Y\",\"autoPublishComment\":\"Y\",\"commentWithEmoji\":\"Y\"}');

--
-- Table structure for table `#__users`
--

DROP TABLE IF EXISTS `#__users`;
CREATE TABLE `#__users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(225) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('R','A','M','S') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'R',
  `active` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `lastVisitedDate` datetime DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(10) unsigned NOT NULL DEFAULT '0',
  `modifiedAt` datetime DEFAULT NULL,
  `modifiedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedAt` datetime DEFAULT NULL,
  `checkedBy` int(10) unsigned NOT NULL DEFAULT '0',
  `secret` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `params` mediumtext COLLATE utf8mb4_unicode_ci,
  `token` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `#__users_email_uindex` (`email`),
  UNIQUE KEY `#__users_username_uindex` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__users`
--

INSERT INTO `#__users` (`id`,`name`,`email`,`username`,`password`,`role`,`active`,`lastVisitedDate`,`createdAt`,`createdBy`,`modifiedAt`,`modifiedBy`,`checkedAt`,`checkedBy`,`secret`,`params`,`token`) VALUES ('3','Super user','admin@email.com','admin','$2y$08$SWxqUmxhdDZQbDl6RTJnO.dVT591F3CEQhM7ye3V8NqkVW1H4efnC','S','Y','2019-09-21 07:18:35','2019-11-15 10:35:33','0','2019-12-23 10:54:34','3','','0','2d066c03-fbf4-4f59-9c61-c0d8e5b9f1ab','{\"timezone\":\"Asia\\/Ho_Chi_Minh\",\"avatar\":null}','');

SET FOREIGN_KEY_CHECKS = 1;