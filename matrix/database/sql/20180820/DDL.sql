CREATE TABLE `cms_teachers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `icon_url` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `primary` tinyint(4) NOT NULL DEFAULT '0',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_sub_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `category_code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `active` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `sub_category_code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `title` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `summary` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `audio_url` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `teacher_id` int(11) NOT NULL DEFAULT '0',
  `modify_user_id` int(11) NOT NULL DEFAULT '0',
  `show` tinyint(4) NOT NULL DEFAULT '0',
  `cover_url` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `read` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `published_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_text_audio_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=CREATE, 1=PROCESS, 2=COMPLETE, 3=FAIL',
  `process_time` datetime NOT NULL,
  `process_duration` bigint(20) NOT NULL DEFAULT '0',
  `path` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `code` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `mobile` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `icon_url` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_article_reads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `ip` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `type` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `article_id` bigint(20) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_article_likes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `type` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `article_id` bigint(20) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cms_categories` ALTER COLUMN `name` SET DEFAULT '';
ALTER TABLE `cms_categories` ALTER COLUMN `active` SET DEFAULT 1;
ALTER TABLE `cms_categories` ADD `code` VARCHAR(191) DEFAULT '';
ALTER TABLE `cms_categories` ADD `summary` VARCHAR(191) DEFAULT '';
ALTER TABLE `cms_categories` ADD `description` text;
