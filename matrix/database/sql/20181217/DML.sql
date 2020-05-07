INSERT INTO `cms_category_groups` (`code`, `name`, `category_code`, `description`, `created_at`, `updated_at`, `sort`, `hide_for_deny`) VALUES ('shipindengji_group', '视频登记分组', 'shikanshipin', '', now(), now(), 0, 0);


INSERT INTO `cms_categories` (`name`, `code`, `active`, `created_at`, `updated_at`, `description`, `summary`, `service_key`) VALUES ('试看视频', 'shikanshipin', 1, now(), now(), '', '', 'basic');

INSERT INTO `cms_teachers` (`category_code`, `user_id`, `icon_url`, `visitor_video_url`, `customer_video_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`) SELECT DISTINCT 'shikanshipin', user_id, '', '', '', '', '', 0, 1, now(), now() FROM `cms_teachers` WHERE `active` = 1;

UPDATE `cms_teachers` SET `primary` = 1 WHERE `category_code` = 'shikanshipin' AND `user_id` = 6;

ALTER TABLE `tj_wx_send_log` DROP COLUMN `demo_url`;

ALTER TABLE `tj_wx_send_log` DROP COLUMN `ad_guide`;
