INSERT INTO `cms_categories`(`code`, `name`, `summary`, `description`, `active`, `created_at`, `updated_at`, `service_key`) VALUES('edazhuanlan', 'Eda专栏', '', '', 1, NOW(), NOW(), 'basic');
INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `visitor_video_url`, `customer_video_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`)
  VALUES ('edazhuanlan', 16, 'http://res.zhongyingtougu.com/cms/head_icon/qiyashen_v2.png', '', '', '', '', 1, 1, NOW(), NOW());
INSERT INTO `cms_category_groups`(`code`, `name`, `category_code`, `description`, `created_at`, `updated_at`, `sort`) VALUES ('article_group_a', 'A股文章', 'edazhuanlan', '', NOW(), NOW(), 0);

INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `visitor_video_url`, `customer_video_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`)
  VALUES('xingezhuanlan', 10, '', '', '', '', '', 0, 1, NOW(), NOW());

INSERT INTO `cms_sub_categories`(`code`, `name`, `category_code`, `active`, `created_at`, `updated_at`) VALUES('default_xingezhuanlan', '新哥专栏默认', 'xingezhuanlan', 1, NOW(), NOW());
