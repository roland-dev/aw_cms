INSERT INTO `cms_stock_report_categories`(`category_id`, `category_name`, `short_title_active`, `created_at`, `updated_at`) VALUES
(2, '跟踪报告', '1', NOW(), NOW()),
(1, '众赢解股', '0', NOW(), NOW()),
(3, '财务分析', '0', NOW(), NOW());

INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('stock_report', '个股报告管理', 'content', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('stock_report_published_update', '个股报告（已发布）修改权限', 'content', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('feed', '推送记录管理', 'content', 1, now());