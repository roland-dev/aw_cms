TRUNCATE TABLE `cms_ad_locations`;
TRUNCATE TABLE `cms_terminals`;

INSERT INTO `cms_ad_locations`(`id`, `code`, `name`, `num`, `size`, `popup_img_size`, `disabled`, `created_at`, `updated_at`, `default_ad_id`) 
VALUES (1, 'banner', '首页banner', 1000, '1035*369', '1062*1327', 0, NOW(), NOW(), 7),
(2, 'splash_screen', '闪屏', 1, '1242*2208', NULL, 0, NOW(), NOW(), NULL),
(3, 'live_banner', '直播banner', 1, '1065*280', NULL, 0, NOW(), NOW(), NULL),
(4, 'xj_banner', '香江banner', 1000, '1035*369', NULL, 0, NOW(), NOW(), NULL);

INSERT INTO `cms_terminals` VALUES (1, 'pc', 'pc端', 0, NULL, NOW(), NOW());
INSERT INTO `cms_terminals` VALUES (2, 'android', '安卓', 0, NULL, NOW(), NOW());
INSERT INTO `cms_terminals` VALUES (3, 'ios', 'ios', 0, NULL, NOW(), NOW());