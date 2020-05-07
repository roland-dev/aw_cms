INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('kit', '锦囊管理', 'content', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('kit_report', '锦囊报告管理', 'content', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('kit_report_published_update', '锦囊报告（已发布）修改权限', 'content', 1, now());

ALTER TABLE `cms_categories`  ADD `is_system_generation` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否是栏目生成： 0 - 否, 1 - 是' AFTER `service_key`;