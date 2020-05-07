CREATE TABLE `cms_ad_location_terminals` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
	`location_code` varchar(32) NOT NULL COMMENT '广告位类型Code',
	`terminal_code` varchar(32) NOT NULL COMMENT '展示终端Code',
	`created_at` timestamp NULL DEFAULT NULL,
	`updated_at` timestamp NULL DEFAULT NULL,
	`deleted_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `cms_ad_location_terminals_location_code_terminal_code_unique` (`location_code`, `terminal_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;