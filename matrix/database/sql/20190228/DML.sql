INSERT INTO `cms_categories`(`name`, `active`, `created_at`, `updated_at`, `code`, `summary`, `description`, `service_key`) VALUES('皖豫粑粑带你玩转保险', 1, NOW(), NOW(), 'wanyubaba', '', '', 'basic');

INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `visitor_video_url`, `customer_video_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`) VALUES('wanyubaba', 42, 'http://res.zhongyingtougu.com/cms/head_icon/xiaozuochuan_v1.png', '', '', '', '', 1, 1, NOW(), NOW());

INSERT INTO `cms_category_groups`(`code`, `name`, `category_code`, `description`, `created_at`, `updated_at`, `sort`, `hide_for_deny`) VALUES('shipindengji_group', '视频登记分组', 'wanyubaba', '', NOW(), NOW(), 0, 0);


### 更新栏目管理左侧菜单
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('column', '栏目管理', 'root', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('category', '栏目分类管理', 'column', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('category_group', '栏目分组管理', 'column', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('teacher', '栏目老师管理', 'column', 1, now());