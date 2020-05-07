INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`)
	SELECT 'all', '全部', `id`, 50, NOW(), NOW() FROM `cms_users` WHERE `id` NOT IN (SELECT `teacher_user_id` FROM `cms_teacher_tabs` WHERE `code` = 'all');

UPDATE `cms_users` SET `description` = '独创“六脉神剑”战法，六大招式教你分析热点题材快速捕捉龙头股' WHERE `id` = 5;

UPDATE `cms_categories` SET `name` = '看高手' WHERE `code` = 'kgs_q';
UPDATE `cms_categories` SET `name` = '产业资本跟踪' WHERE `code` = 'kgs_c';

INSERT INTO `cms_categories`(`code`, `name`, `summary`, `description`, `active`, `created_at`, `updated_at`, `service_key`) VALUES('xingezhuanlan', '新哥专栏', '', '', 1, NOW(), NOW(), 'basic');
INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `visitor_video_url`, `customer_video_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`)
  VALUES ('xingezhuanlan', 28, 'http://res.zhongyingtougu.com/cms/head_icon/xingetouyan_v2.png', '', '', '', '', 1, 1, NOW(), NOW());
INSERT INTO `cms_category_groups`(`code`, `name`, `category_code`, `description`, `created_at`, `updated_at`, `sort`) VALUES ('article_group_a', 'A股文章', 'xingezhuanlan', '', NOW(), NOW(), 0);


INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`) VALUES('article', '文章', 28, 20, NOW(), NOW());
