ALTER TABLE `cms_article_replies` ADD `placed_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '置顶状态： 0 - 取消置顶，1 - 置顶' AFTER `status`;
ALTER TABLE `cms_article_replies` ADD `placed_at` timestamp NULL DEFAULT NULL COMMENT '置顶时间' AFTER `placed_status`;
ALTER TABLE `cms_article_replies` ADD `is_all_visible` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否全员可见： 0 - 否, 1 - 是' AFTER `ref_open_id`;
ALTER TABLE `cms_article_replies` ADD `forward_to_twitter` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否转发到解盘： 0 - 否, 1 - 是' AFTER `is_all_visible`;