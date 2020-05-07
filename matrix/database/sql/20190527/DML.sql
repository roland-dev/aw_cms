INSERT INTO `cms_ad_locations`(code, name, num, size, file_size, disabled, created_at, updated_at) VALUES ('fast_entrance', '首页功能快速入口', 1000, '132*132', 10, 0, now(), now());
INSERT INTO `cms_ad_operation_types`(code, name, disabled, created_at, updated_at) VALUES ('no_jump', '无跳转', 0, NOW(), NOW());
INSERT INTO `cms_ad_operation_types`(code, name, disabled, created_at, updated_at) VALUES ('small_program', '小程序', 0, NOW(), NOW());
INSERT INTO `cms_ad_operation_types`(code, name, disabled, created_at, updated_at) VALUES ('pdf', 'PDF', 0, NOW(), NOW());
INSERT INTO `cms_ad_operation_types`(code, name, disabled, created_at, updated_at) VALUES ('message', '解盘', 0, NOW(), NOW());
INSERT INTO `cms_ad_operation_types`(code, name, disabled, created_at, updated_at) VALUES ('open_account', '证券开户', 0, NOW(), NOW());
INSERT INTO `cms_ad_operation_types`(code, name, disabled, created_at, updated_at) VALUES ('coupon', '优惠券领取', 0, NOW(), NOW());
INSERT INTO `cms_ad_operation_types`(code, name, disabled, created_at, updated_at) VALUES ('audio_video', '音视频', 0, NOW(), NOW());
INSERT INTO `cms_ad_operation_types`(code, name, disabled, created_at, updated_at) VALUES ('other', '其他', 0, NOW(), NOW());

UPDATE `cms_ad_operation_types` SET `disabled` = 1 WHERE `code` = 'ad'; 
UPDATE `cms_ads` SET `operation_code` = 'other' WHERE `operation_code` = 'ad';