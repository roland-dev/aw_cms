CREATE TABLE `cms_article_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '用户openid',
  `session_id` varchar(191) NOT NULL DEFAULT '' COMMENT'用户sessionid',
  `type` varchar(191) NOT NULL DEFAULT '' COMMENT '原文内容类型 article|talkshow|course',
  `article_id` varchar(191) NOT NULL DEFAULT '' COMMENT '原文内容id',
  `article_title` varchar(191) NOT NULL DEFAULT '' COMMENT '原文内容标题',
  `article_author_user_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '作者user_id',
  `content` varchar(512) NOT NULL DEFAULT '' COMMENT '评论内容',
  `ref_id` int(10) NOT NULL DEFAULT 0 COMMENT '回复目标评论id',
  `ref_content` varchar(512) NOT NULL DEFAULT '' COMMENT '回复目标评论内容',
  `ref_open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '回复目标评论作者OpenId',
  `status` tinyint(4) NOT NULL DEFAULT 10 COMMENT '评论状态 10 = 待审核 | 20 = 审核通过 | 30 = 审核拒绝',
  `examine_user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '审批操作人cms_users.id',
  `examine_at` timestamp NULL DEFAULT NULL COMMENT '审批操作时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_reply_cnts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_type` varchar(191) NOT NULL DEFAULT '' COMMENT '内容类型 article|talkshow|course',
  `content_id` varchar(191) NOT NULL DEFAULT 0 COMMENT '内容id',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '评论状态 10 = 待审核 | 20 = 审核通过 | 30 = 审核拒绝',
  `cnt` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '这个状态的评论总数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cms_twitters` ADD COLUMN `ref_type` VARCHAR(191) DEFAULT '' COMMENT '转发内容类型 article|talkshow|news|course';
ALTER TABLE `cms_twitters` ADD COLUMN `ref_category_code` VARCHAR(191) DEFAULT '' COMMENT '转发内容分类';
ALTER TABLE `cms_twitters` ADD COLUMN `ref_id` VARCHAR(191) DEFAULT '' COMMENT '转发内容源id';
ALTER TABLE `cms_twitters` ADD COLUMN `ref_thumb` VARCHAR(191) DEFAULT '' COMMENT '转发内容缩略图';
ALTER TABLE `cms_twitters` ADD COLUMN `ref_title` VARCHAR(191) DEFAULT '' COMMENT '转发内容标题';
ALTER TABLE `cms_twitters` ADD COLUMN `ref_summary` VARCHAR(191) DEFAULT '' COMMENT '转发内容摘要';

ALTER TABLE `cms_customers` ADD COLUMN `nickname` VARCHAR(191) DEFAULT '' COMMENT '客户昵称' AFTER `mobile`;

ALTER TABLE `cms_article_likes` MODIFY COLUMN `article_id` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '被点赞内容id'; -- 已上线内容, 无影响修改字段类型兼容更多内容
ALTER TABLE `cms_like_statistics` MODIFY COLUMN `article_id` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '被点赞内容id'; -- 已上线内容, 无影响修改字段类型兼容更多内容
