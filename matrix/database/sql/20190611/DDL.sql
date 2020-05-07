CREATE TABLE `cms_stock_reports` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
	`report_id` varchar(50) NOT NULL COMMENT '记录ID',
	`category_id` smallint NOT NULL COMMENT '个股报告类型id',
	`stock_code` varchar(10) NOT NULL COMMENT '股票代码',
	`stock_name` varchar(50) NOT NULL COMMENT '股票名称',
	`report_format` tinyint(4) NOT NULL COMMENT '内容类型 0-图文; 1-PDF',
	`report_short_title` varchar(64) NULL COMMENT '短标题',
	`report_title` varchar(500) NOT NULL COMMENT '报告标题',
	`report_content` mediumtext NULL COMMENT '内容正文',
	`report_url` varchar(500) NULL COMMENT '链接地址',
	`report_date` date NOT NULL COMMENT '报告日期',
	`author_teacher_id` int(11) NULL COMMENT '作者id',
	`creator` int(11) NULL COMMENT '创建人',
	`last_modify_user_id` int(10) NOT NULL COMMENT '最后一个修改人cms_users.id',
	`report_summary` varchar(255) NULL COMMENT '摘要',
	`external_id` varchar(100) NULL COMMENT '外部ID',
	`publish` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已发布，0=false, 1=true',
	`deleted_at` timestamp NULL DEFAULT NULL,
	`created_at` timestamp NULL DEFAULT NULL,
	`updated_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY(`id`),
	KEY `cms_stock_report_stock_code_category_id` (`stock_code`, `category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_stock_report_categories` (
	`category_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '类型ID',
	`category_name` varchar(32) NOT NULL COMMENT '类型Name',
	`short_title_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否包含短标题 0: 否 1: 是',
	`sort_no` int(11) NULL COMMENT '排列编号',
	`visible` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可见 0：否 1：是',
	`deleted_at` timestamp NULL DEFAULT NULL,
	`created_at` timestamp NULL DEFAULT NULL,
	`updated_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `feed` ADD `qywx_status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '企业微信推送状态：0-不推送；1-未推送；2-推送失败；3-推送成功' AFTER `push_error`;
ALTER TABLE `feed` ADD `qywx_time` DATETIME NULL COMMENT '企业微信推送时间' AFTER `qywx_status`;
ALTER TABLE `feed` ADD `qywx_error` VARCHAR(500) NULL COMMENT '企业微信推送返回错误信息' AFTER `qywx_time`;