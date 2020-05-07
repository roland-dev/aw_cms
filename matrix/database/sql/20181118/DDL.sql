## 添加数据库表
CREATE TABLE `cms_ad_terminals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `ad_id` int(11) NOT NULL COMMENT '广告ID',
  `terminal_code` varchar(32) COLLATE utf8_general_ci NOT NULL COMMENT '展示终端Code',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

## 删除字段
ALTER TABLE `cms_ads` DROP `relatively_file_path`;
ALTER TABLE `cms_ads` DROP `relatively_popup_file_path`;

ALTER TABLE `cms_forums` DROP `relatively_file_path`;

ALTER TABLE `cms_ad_locations` DROP `terminal_code`;