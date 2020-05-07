CREATE TABLE `cms_course_systems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creator_user_id` int(11) NOT NULL,
  `sort_no` int(11) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `primary_category` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_systems_name_index` (`name`),
  KEY `course_systems_creator_user_id_index` (`creator_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `cms_courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `background_picture` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_system_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creator_user_id` int(11) NOT NULL,
  `sort_no` int(11) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `courses_name_index` (`name`),
  KEY `courses_course_system_code_index` (`course_system_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cms_course_videos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `picture_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_display` tinyint(4) NOT NULL COMMENT '视频是否在app显示',
  `access` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `watch` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_signin_id` int(11) NOT NULL,
  `sort_no` int(11) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_videos_updated_at_index` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cms_content_guards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `param1` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `param2` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `param3` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cms_category_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `category_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

alter table cms_video_signins add column is_public tinyint(4) NOT NULL DEFAULT '1'  COMMENT  '0:课程视频，1:普通登记视频';
alter table cms_course_videos add column tag varchar(64) default '' after `sort_no`;
