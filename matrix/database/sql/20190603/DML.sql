INSERT INTO `cms_ad_locations`(code, name, num, size, file_size, disabled, created_at, updated_at) VALUES ('ad_splash_screen', '广告闪屏', 1, '1242*2208', 200, 0, now(), now());

INSERT INTO `cms_ad_location_terminals`(`location_code`, `terminal_code`, `created_at`, `updated_at`) VALUES
('banner', 'pc', NOW(), NOW()),
('banner', 'android', NOW(), NOW()),
('banner', 'ios', NOW(), NOW()),
('live_banner', 'pc', NOW(), NOW()),
('live_banner', 'android', NOW(), NOW()),
('live_banner', 'ios', NOW(), NOW()),
('xj_banner', 'pc', NOW(), NOW()),
('xj_banner', 'android', NOW(), NOW()),
('xj_banner', 'ios', NOW(), NOW()),
('fast_entrance', 'android', NOW(), NOW()),
('fast_entrance', 'ios', NOW(), NOW()),
('ad_splash_screen', 'android', NOW(), NOW()),
('ad_splash_screen', 'ios', NOW(), NOW());

UPDATE `cms_ad_locations` SET `disabled` = 1 WHERE `code` = 'splash_screen';