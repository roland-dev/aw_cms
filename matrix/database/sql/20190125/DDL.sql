ALTER TABLE `cms_twitter_guards` MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录id';
ALTER TABLE `cms_twitter_guards` MODIFY COLUMN `category_code` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '栏目分类Code';
ALTER TABLE `cms_twitter_guards` MODIFY COLUMN `open_id` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '客户openId';
ALTER TABLE `cms_twitter_guards` MODIFY COLUMN `operator_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '操作人user_id';
ALTER TABLE `cms_twitter_guards` MODIFY COLUMN `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '审批状态 0: 审批中 1:审批成功 2:审批失败';
ALTER TABLE `cms_twitter_guards` ADD `source_type` VARCHAR(191) NULL DEFAULT 'customer' COMMENT '审批来源 customer: 客户 manage_system:管理端 auto_program: 自动执行脚本' AFTER `status`;
ALTER TABLE `cms_twitter_guards` ADD `review_status` TINYINT(4) NULL COMMENT '上次审批结果 0:审批通过（特定用户） 1:审批被拒' AFTER `source_type`;
ALTER TABLE `cms_twitter_guards` ADD `is_qualified` TINYINT(4) NULL COMMENT '审批时是否达标 0: 不达标 1: 达标' AFTER `review_status` ;

ALTER TABLE `cms_private_message_guards` ADD `source_type` VARCHAR(191) NULL DEFAULT 'customer' COMMENT '审批来源 customer: 客户 re_review: 复审' AFTER `status`;
ALTER TABLE `cms_private_message_guards` ADD `review_status` TINYINT(4) NULL COMMENT '上次审批结果 0:审批通过（特定用户） 1:审批被拒' AFTER `source_type`;
