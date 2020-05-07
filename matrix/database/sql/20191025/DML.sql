INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('dynamic_ad', '跑马灯管理', 'propaganda', 1, now());

UPDATE `cms_terminals` SET `is_dynamic_ad` = 1 WHERE `code` = 'pc';