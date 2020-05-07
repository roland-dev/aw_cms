ALTER TABLE `cms_user_groups` MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录id';
ALTER TABLE `cms_user_groups` MODIFY COLUMN `code` VARCHAR(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户组Code';
ALTER TABLE `cms_user_groups` MODIFY COLUMN `name` VARCHAR(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户组name';
ALTER TABLE `cms_user_groups` MODIFY COLUMN `user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '用户id';
ALTER TABLE `cms_user_groups` MODIFY COLUMN `sort` INT(11) NOT NULL DEFAULT '0' COMMENT '用户在 当前用户组 当中的序号';

CREATE UNIQUE INDEX cms_user_groups_code_name_user_id_unique ON `cms_user_groups`(`code`,`name`,`user_id`);
