CREATE TABLE `cms_column_groups` (
	`id` int(10)  UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
	`code` varchar(191) NOT NULL COMMENT '栏目分类分组Code',
	`name` varchar(191) NOT NULL COMMENT '栏目分类分组名称',
	`descript` varchar(191) NOT NULL COMMENT '栏目分类分组描述',
	`deleted_at` timestamp NULL DEFAULT NULL,
	`created_at` timestamp NULL DEFAULT NULL,
	`updated_at` timestamp NULL DEFAULT NULL,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;