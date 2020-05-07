CREATE TABLE `cms_dynamic_ads` (
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
	`title` varchar(255) NOT NULL COMMENT '跑马灯 广告标题',
	`content_url` varchar(500) NOT NULL COMMENT '链接',
	`jump_type` varchar(100) NULL COMMENT '跳转类型',
	`jump_params` varchar(500) NULL COMMENT '跳转参数',
	`start_at` timestamp NOT NULL COMMENT '展示开始时间',
	`end_at` timestamp NOT NULL COMMENT '展示结束时间',
	`terminal_code` varchar(32) NOT NULL COMMENT '展示终端Code',
	`active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否发布 1: 发布 0: 未发布',
	`sign` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否标红 1: 不标红 0: 标红',
	`source_type` varchar(32) NOT NULL DEFAULT 'added' COMMENT 'added: 手动添加 feed: 内容精选 talkshow: 节目预告',
	`source_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '来源ID',
	`last_modify_user_id` int(11) NOT NULL COMMENT '最后一个修改人cms_users.id 0: 系统同步',
	`created_at` timestamp NULL DEFAULT NULL,
	`updated_at` timestamp NULL DEFAULT NULL,
	`deleted_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY(`id`),
	KEY `cms_dynamic_ads_start_at_end_at_active` (`start_at`,`end_at`, `active`),
	KEY `cms_dynamic_ads_source_type_source_id` (`source_type`, `source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_dynamic_ad_terminals` (
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
	`dynamic_ad_id` int(11) NOT NULL COMMENT '跑马灯记录 ID',
	`terminal_code` varchar(32) NOT NULL COMMENT '展示终端Code',
	`created_at` timestamp NULL DEFAULT NULL,
	`updated_at` timestamp NULL DEFAULT NULL,
	`deleted_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cms_terminals` ADD `is_dynamic_ad` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是跑马灯展示终端类型 0: 否 1: 是' AFTER `disabled`;