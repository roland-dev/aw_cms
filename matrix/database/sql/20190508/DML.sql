-- 直播节目 权限
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('live', '直播管理', 'root', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('liveroom', '直播室管理', 'live', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('static_talkshow', '固定节目管理', 'live', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('talkshow', '每日节目管理', 'live', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('discuss', '直播互动管理', 'live', 1, now());

-- 视频供应商
INSERT INTO `cms_video_vendors`(`code`, `name`, `domain`, `remark`, `last_modify_user_id`, `created_at`, `updated_at`) VALUES
	('video_gensee', '展示互动', 'fhcj.gensee.com', '我们的密切供应商', 2, NOW(), NOW()),
	('video_tencent', '腾讯视频', 'v.qq.com', '腾讯视频，图文视频供应商', 2, NOW(), NOW());
