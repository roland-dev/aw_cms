UPDATE `cms_teacher_tabs` SET `code` = 'course', `name` = '课程', `sort` = 10, `updated_at` = NOW() WHERE `id` = 40;

INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `visitor_video_url`, `customer_video_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`) VALUES('edazhuanlan', 39, '', '', '', '', '', 0, 1, NOW(), NOW());
