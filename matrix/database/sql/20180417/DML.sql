/**
 * 增加视频维护人员 李龙轩
 * 将晚间聊股两位老师头像更改为统一的众赢logo
 */
INSERT INTO cms_users(`name`, `email`, `password`, `type`, `icon_url`, `active`, `created_at`, `updated_at`)
    VALUES ('李龙轩', 'lilongxuan@hzhfzx.com', '', 'video_manager', '', 1, now(), now());
INSERT INTO cms_ucenters(`user_id`, `enterprise_userid`, `created_at`, `updated_at`)
    VALUES ('13', 'lilongxuan', now(), now());
INSERT INTO cms_grants(`user_id`, `permission_code`, `active`, `created_at`, `updated_at`)
    VALUES (13, 'video', 1, now(), now());
UPDATE `cms_users` SET `icon_url` = 'assets/image/headicon/zyicon.png' WHERE `name` IN ('周昭', '罗洪伟');
