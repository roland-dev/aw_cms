ALTER TABLE `cms_like_statistics` ADD COLUMN `session_id` varchar(191) DEFAULT '' COMMENT '会话id';

ALTER TABLE `cms_article_likes` ADD COLUMN `user_type` varchar(64) DEFAULT '' COMMENT '点赞人类型';
ALTER TABLE `cms_article_likes` ADD COLUMN `session_id` varchar(191) DEFAULT '' COMMENT '会话id';

CREATE TABLE `cms_like_statistics` (
  `article_id` int(11) NOT NULL DEFAULT '0' COMMENT '记录id',
  `type` varchar(191) NOT NULL COMMENT '记录类型',
  `like_sum` int(11) NOT NULL DEFAULT '0' COMMENT '点赞总数',
  `customer_like_sum` int(11) NOT NULL DEFAULT '0' COMMENT '客户点赞总数',
  `staff_like_sum` int(11) NOT NULL DEFAULT '0' COMMENT '员工点赞总数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`article_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
