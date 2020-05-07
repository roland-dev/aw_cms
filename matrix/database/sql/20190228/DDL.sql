ALTER TABLE `cms_teachers` MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录id';
ALTER TABLE `cms_teachers` MODIFY COLUMN `category_code` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '栏目分类code';
ALTER TABLE `cms_teachers` MODIFY COLUMN `user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '用户Id';
ALTER TABLE `cms_teachers` MODIFY COLUMN `icon_url` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '头像url';
ALTER TABLE `cms_teachers` MODIFY COLUMN `visitor_video_url` VARCHAR(191) DEFAULT '' COMMENT '视频地址（访客）';
ALTER TABLE `cms_teachers` MODIFY COLUMN `customer_video_url` VARCHAR(191) DEFAULT '' COMMENT '视频地址（客户）';
ALTER TABLE `cms_teachers` MODIFY COLUMN `cover_url` VARCHAR(191) DEFAULT '' COMMENT '封面地址';
ALTER TABLE `cms_teachers` MODIFY COLUMN `description` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '详细描述';
ALTER TABLE `cms_teachers` MODIFY COLUMN `primary` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否是主笔老师： 1、主笔老师 0、非主笔老师';
ALTER TABLE `cms_teachers` MODIFY COLUMN `active` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否激活： 1、是 0、否';

CREATE UNIQUE INDEX cms_teachers_category_code_user_id_unique ON `cms_teachers`(`category_code`,`user_id`);


ALTER TABLE `cms_categories` MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录id';
ALTER TABLE `cms_categories` MODIFY COLUMN `name` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '栏目分类名称';
ALTER TABLE `cms_categories` MODIFY COLUMN `code` VARCHAR(191) NOT NULL COMMENT '栏目分类Code';
ALTER TABLE `cms_categories` MODIFY COLUMN `description` TEXT COMMENT '详细描述';
ALTER TABLE `cms_categories` MODIFY COLUMN `summary` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '摘要';
ALTER TABLE `cms_categories` MODIFY COLUMN `service_key` VARCHAR(191) DEFAULT 'basic' COMMENT '服务Key';
ALTER TABLE `cms_categories` MODIFY COLUMN `active` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '是否激活： 1、是 0、否';

CREATE UNIQUE INDEX cms_categories_code_unique ON `cms_categories`(`code`);

ALTER TABLE `cms_sub_categories` MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录id';
ALTER TABLE `cms_sub_categories` MODIFY COLUMN `code` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '子栏目分类Code';
ALTER TABLE `cms_sub_categories` MODIFY COLUMN `name` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '子栏目分类名称';
ALTER TABLE `cms_sub_categories` MODIFY COLUMN `category_code` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '所属父级栏目Code';
ALTER TABLE `cms_sub_categories` MODIFY COLUMN `active` INT(11) NOT NULL DEFAULT '0' COMMENT '是否激活： 1、是 0、否';

CREATE UNIQUE INDEX cms_sub_categories_code_category_code_unique ON `cms_sub_categories`(`code`,`category_code`);


ALTER TABLE `cms_category_groups` MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录id';
ALTER TABLE `cms_category_groups` MODIFY COLUMN `code` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '栏目分类分组Code';
ALTER TABLE `cms_category_groups` MODIFY COLUMN `name` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '栏目分类分组名称';
ALTER TABLE `cms_category_groups` MODIFY COLUMN `category_code` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '对应栏目分类';
ALTER TABLE `cms_category_groups` MODIFY COLUMN `description` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '详细描述';
ALTER TABLE `cms_category_groups` MODIFY COLUMN `sort` INT(11) DEFAULT '0' COMMENT '排序';

CREATE UNIQUE INDEX cms_category_groups_code_category_code_unique ON `cms_category_groups`(`code`,`category_code`);