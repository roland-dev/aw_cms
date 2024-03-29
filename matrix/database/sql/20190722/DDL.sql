CREATE TABLE `cms_kits` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
 `code` varchar(32) NOT NULL COMMENT '锦囊Code',
 `name` varchar(255) NOT NULL COMMENT '锦囊名称',
 `cover_url` varchar(500) NOT NULL COMMENT '封面url',
 `descript` mediumtext NOT NULL COMMENT '锦囊介绍',
 `belong_user_id` int(11) NOT NULL COMMENT '归属牛人userid',
 `buy_type` tinyint(4) NOT NULL COMMENT '购买类型 1：APP内购 2: APP外购',
 `buy_state` tinyint(4) NOT NULL COMMENT '购买状态 0：不可购买 1： 可购买',
 `service_key` varchar(255) NOT NULL COMMENT '服务key',
 `sort_num` int(11) NOT NULL COMMENT '排列序号',
 `creator_user_id` int(10) NULL COMMENT '创建人',
 `last_modify_user_id` int(10) NOT NULL COMMENT '最后一个修改人cms_users.id',
 `deleted_at` timestamp NULL DEFAULT NULL,
 `created_at` timestamp NULL DEFAULT NULL,
 `updated_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY(`id`),
 UNIQUE KEY `cms_kits_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_kit_reports` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
 `report_id` varchar(50) NOT NULL COMMENT '记录ID',
 `title` varchar(500) NOT NULL COMMENT '报告名称',
 `kit_code` varchar(32) NOT NULL COMMENT '锦囊Code',
 `start_at` timestamp NOT NULL COMMENT '有效开始时间',
 `end_at` timestamp NOT NULL COMMENT '有效结束时间',
 `cover_url` varchar(500) NOT NULL COMMENT '封面url',
 `summary` varchar(255) NOT NULL COMMENT '摘要',
 `format` tinyint(4) NOT NULL COMMENT '内容类型 0-图文; 1-PDF',
 `content` mediumtext NULL COMMENT '内容正文',
 `url` varchar(500) NULL COMMENT '文件url -- PDF',
 `creator_user_id` int(10) NULL COMMENT '创建人',
 `last_modify_user_id` int(10) NOT NULL COMMENT '最后一个修改人cms_users.id',
 `publish` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已发布， 0=false, 1=true',
 `deleted_at` timestamp NULL DEFAULT NULL,
 `created_at` timestamp NULL DEFAULT NULL,
 `updated_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY(`id`),
 KEY `cms_kit_reports_kit_code_start_at_end_at` (`kit_code`,`start_at`, `end_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;