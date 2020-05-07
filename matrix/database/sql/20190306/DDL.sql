CREATE TABLE `cms_move_qr_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '唯一标识随机码',
  `title` varchar(191)  NOT NULL DEFAULT '' COMMENT '固定二维码组标题',
  `max_fans` integer(11) NOT NULL DEFAULT 0 COMMENT '活码最大访问次数',
  `remark` varchar(191) NOT NULL DEFAULT '' COMMENT '固定二维码备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `cms_move_qrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '活码唯一标识随机码',
  `move_qr_group_code` varchar(191) NOT NULL DEFAULT '' COMMENT '静态码唯一标识随机码',
  `title` varchar(191) NOT NULL DEFAULT '' COMMENT '活码二维码组标题',
  `filename` varchar(191) NOT NULL DEFAULT '' COMMENT '活码二维码文件名',
  `remark` varchar(191) NOT NULL DEFAULT '' COMMENT '活码二维码备注',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '活码二维码顺序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
