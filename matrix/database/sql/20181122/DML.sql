/*cms_cat_to_tches表
【早间读报】去掉齐玉波，保留有为投研为primary*/

UPDATE `cms_cat_to_tches` SET active = 0 WHERE id = 1;

/*【晚间聊股】去掉罗宏伟，周昭，保留猎豹投研为primary*/

UPDATE `cms_cat_to_tches` SET active = 0 WHERE id = 10;

UPDATE `cms_cat_to_tches` SET active = 0 WHERE id = 11;

/*财富人生 cat_to_tches 没有对应作者

新哥投研

专家看盘

每日聊股  没有对应栏目*/

/*cms_teachers 表

【早间读报】由齐玉波改为有为投研*/

UPDATE `cms_teachers` SET active = 0 WHERE id = 1;

INSERT INTO `cms_teachers` (category_code, user_id, icon_url, visitor_video_url, customer_video_url, cover_url, description, `primary`, active, created_at, updated_at) VALUES ('shipinjiepan', 34, 'http://res.zhongyingtougu.com/cms/head_icon/zyicon.png', '', '', '', '', 1, 1, now(), now() );

/*【晚间聊股】去掉罗宏伟 周昭 改为猎豹投研*/

UPDATE `cms_teachers` SET active = 0 WHERE id = 10;

UPDATE `cms_teachers` SET active = 0 WHERE id = 11;

UPDATE `cms_teachers` SET primary = 0 WHERE id = 11;

INSERT INTO `cms_teachers` (category_code, user_id, icon_url, visitor_video_url, customer_video_url, cover_url, description, `primary`, active, created_at, updated_at) VALUES ('wanjianliaogu', 27, 'http://res.zhongyingtougu.com/cms/head_icon/zyicon.png', '', '', '', '', 1, 1, now(), now() );

/**/
UPDATE `cms_categories` SET `service_key` = 'kgs_q' WHERE `code` = 'kgs_q';
UPDATE `cms_categories` SET `service_key` = 'kgs_c' WHERE `code` = 'kgs_c';

UPDATE `feed` SET `access_level` = 'kgs_q' WHERE `feed_type` = 11 AND `category_key` = 'kgs_q';
UPDATE `feed` SET `access_level` = 'kgs_c' WHERE `feed_type` = 11 AND `category_key` = 'kgs_c';
