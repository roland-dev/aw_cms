INSERT INTO `cms_ad_locations` VALUES (1, 'pc_banner', '首页banner', 1000, '360*144', '500*626', 'pc', 0, NULL, now(), now());
INSERT INTO `cms_ad_locations` VALUES (2, 'app_banner', '首页banner', 1000, '1125*450', '1062*1327', 'app', 0, NULL, now(), now());
INSERT INTO `cms_ad_locations` VALUES (3, 'splash_screen', '闪屏', 1, '1242*2208', NULL, 'app', 0, NULL, now(), now());
INSERT INTO `cms_ad_locations` VALUES (4, 'pc_live_banner', '直播banner', 1, '360*95', NULL, 'pc', 0, NULL, now(), now());
INSERT INTO `cms_ad_locations` VALUES (5, 'app_live_banner', '直播banner', 1, '1065*280', NULL, 'app', 0, NULL, now(), now());
INSERT INTO `cms_ad_locations` VALUES (6, 'pc_new_banner', '首页banner（新）', 1000, '360*128', '500*626', 'pc', 0, NULL, now(), now());
INSERT INTO `cms_ad_locations` VALUES (7, 'app_new_banner', '首页banner（新）', 1000, '1035*369', '1062*1327', 'app', 0, NULL, now(), now());


INSERT INTO `cms_ad_operation_types` VALUES (1, 'ad', '纯广告', 0, NULL, now(), now());
INSERT INTO `cms_ad_operation_types` VALUES (2, 'forum', '论坛', 0, NULL, now(), now());

INSERT INTO `cms_terminals` VALUES (1, 'app', 'app', 0, NULL, now(), now());
INSERT INTO `cms_terminals` VALUES (2, 'pc', 'pc', 0, NULL, now(), now());

INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('propaganda', '宣传管理', 'root', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('ad', '广告管理', 'propaganda', 1, now());
INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES ('forum', '论坛管理', 'propaganda', 1, now());