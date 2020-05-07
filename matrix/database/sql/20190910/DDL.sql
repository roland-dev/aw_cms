CREATE TABLE `cms_tabs` (
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
	`code` varchar(191) NOT NULL COMMENT '内容标签Code',
	`name` varchar(191) NOT NULL COMMENT '内容标签name',
	`sort` int(11) NOT NULL COMMENT '内容标签排序',
	`active` tinyint(4) NOT NULL COMMENT '激活状态 1: 激活 0: 未激活',
	`deleted_at` timestamp NULL DEFAULT NULL,
	`created_at` timestamp NULL DEFAULT NULL,
	`updated_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY(`id`),
  UNIQUE KEY `cms_tabs_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;