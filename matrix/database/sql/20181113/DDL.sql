CREATE TABLE `cms_category_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `category_code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_twitters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `category_code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `teacher_id` int(11) NOT NULL DEFAULT '0',
  `room_id` VARCHAR(191) DEFAULT '',
  `image_url` VARCHAR(191) DEFAULT '',
  `feed` TINYINT(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `operator_user_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_twitter_guards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `open_id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `operator_user_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_private_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `direction` tinyint(4) NOT NULL DEFAULT '0',
  `teacher_id` int(11) NOT NULL DEFAULT '0',
  `open_id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `read` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_private_message_guards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL DEFAULT '0',
  `open_id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `operator_user_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_system_notices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `content` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `target` tinyint(4) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `open_id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `read` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_user_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `cms_teacher_follows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `open_id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `cms_teacher_tabs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `teacher_user_id` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_custom_apps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `secret` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `remark` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_apps_code_unique` (`code`),
  UNIQUE KEY `custom_apps_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cms_users` ADD COLUMN `cert_no` VARCHAR(191) DEFAULT '' AFTER `icon_url`;
ALTER TABLE `cms_users` ADD COLUMN `description` TEXT AFTER `cert_no`;
ALTER TABLE `cms_users` ADD COLUMN `select` tinyint(4) DEFAULT 0 AFTER `description`;

ALTER TABLE `cms_articles` ADD COLUMN `feed` TINYINT(4) DEFAULT 0 AFTER `teacher_id`;

ALTER TABLE `cms_teachers` ADD COLUMN `visitor_video_url` VARCHAR(191) DEFAULT '' AFTER `icon_url`;
ALTER TABLE `cms_teachers` ADD COLUMN `customer_video_url` VARCHAR(191) DEFAULT '' AFTER `visitor_video_url`;
ALTER TABLE `cms_teachers` ADD COLUMN `cover_url` VARCHAR(191) DEFAULT '' AFTER `customer_video_url`;
ALTER TABLE `cms_categories` ADD COLUMN `service_key` VARCHAR(191) DEFAULT 'basic';
