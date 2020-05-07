ALTER TABLE `cms_teacher_tabs` MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录id';
ALTER TABLE `cms_teacher_tabs` MODIFY COLUMN `code` VARCHAR(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '内容标签Code';
ALTER TABLE `cms_teacher_tabs` MODIFY COLUMN `name` VARCHAR(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '内容标签name';
ALTER TABLE `cms_teacher_tabs` MODIFY COLUMN `teacher_user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '老师的userid';
ALTER TABLE `cms_teacher_tabs` MODIFY COLUMN `sort` INT(11) NOT NULL DEFAULT '0' COMMENT '内容标签的排序';
ALTER TABLE `cms_teacher_tabs` ADD `deleted_at` timestamp NULL DEFAULT NULL AFTER `sort`;

CREATE UNIQUE INDEX cms_teacher_tabs_code_name_teacher_user_id_unique ON `cms_teacher_tabs`(`code`,`name`,`teacher_user_id`);