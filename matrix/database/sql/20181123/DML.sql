INSERT INTO `cms_user_groups`(`code`, `name`, `user_id`, `sort`, `created_at`, `updated_at`) VALUES
  ('teacher_stock_a', 'A股牛人', 34, 10, NOW(), NOW());

INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`) SELECT 'all', '全部', `user_id`, 50, NOW(), NOW() FROM `cms_ucenters` WHERE `enterprise_userid` = 'youweitouyan';
INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`) SELECT 'talkshow', '节目', `user_id`, 30, NOW(), NOW() FROM `cms_ucenters` WHERE `enterprise_userid` = 'youweitouyan';

UPDATE `cms_users` SET `cert_no` = 'A0870615050002、A0870118070005、A0870616070001' WHERE `name` = '新哥投研';
UPDATE `cms_users` SET `cert_no` = 'A0870614050001' WHERE `name` = '吕学栋';
UPDATE `cms_users` SET `cert_no` = 'A0870614060001' WHERE `name` = '有为投研';
UPDATE `cms_users` SET `cert_no` = 'A0870616100003' WHERE `name` = '猎豹投研';
UPDATE `cms_users` SET `description` = '家庭资产配置，就在我们身边~' WHERE `name` = 'Eda';
