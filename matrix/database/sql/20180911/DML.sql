INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`, `updated_at`) VALUES
	('content', '内容管理', 'root', 1, NOW(), NOW()),
	('article', '文章管理', 'content', 1, NOW(), NOW());
