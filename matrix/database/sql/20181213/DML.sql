INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `visitor_video_url`, `customer_video_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`)
  VALUES('edazhuanlan', 37, '', '', '', '', '', 0, 1, NOW(), NOW());

INSERT INTO `cms_sub_categories`(`code`, `name`, `category_code`, `active`, `created_at`, `updated_at`) VALUES('default_edazhuanlan', 'Eda专栏默认', 'edazhuanlan', 1, NOW(), NOW());

INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`) VALUES
  ('article', '文章', 16, 20, NOW(), NOW()),
  ('course', '课程', 5, 10, NOW(), NOW());
