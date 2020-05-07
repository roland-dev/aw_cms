/**
 * 增加视频维护人员 舒馨
 */
INSERT INTO cms_users(`name`, `email`, `password`, `type`, `icon_url`, `active`, `created_at`, `updated_at`)
    VALUES ('舒馨', 'shuxin@hzhfzx.com', '', 'video_manager', '', 1, now(), now());
INSERT INTO cms_ucenters(`user_id`, `enterprise_userid`, `created_at`, `updated_at`)
    VALUES ('14', 'shuxin', now(), now());
INSERT INTO cms_grants(`user_id`, `permission_code`, `active`, `created_at`, `updated_at`)
    VALUES (14, 'video', 1, now(), now());
