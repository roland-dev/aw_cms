INSERT INTO `cms_users`(`name`, `email`, `password`, `type`, `icon_url`, `active`, `created_at`) values
  ('张仲钦', 'zhangzhongqin@hzhfzx.com', '', 'manager', '', 1, now()),
  ('郝平睿', 'haopingrui@hzhfzx.com', '', 'manager', '', 1, now()),
  ('李宗恒', 'lizongheng@hzhfzx.com', '', 'teacher', '', 1, now()),
  ('王健', 'wangjian@hzhfzx.com', '', 'teacher', '', 1, now()),
  ('韩珂', 'hanke@hzhfzx.com', '', 'teacher', '', 1, now()),
  ('莫成军', 'mochengjun@hzhfzx.com', '', 'teacher', '', 1, now()),
  ('李鹏', 'lipeng@hzhfzx.com', '', 'teacher', '', 1, now()),
  ('罗洪伟', 'luohongwei@hzhfzx.com', '', 'teacher', '', 1, now()),
  ('周昭', 'zhouzhao@hzhfzx.com', '', 'teacher', '', 1, now()),
  ('陈露露', 'chenlulu@hzhfzx.com', '', 'video_manager', '', 1, now()),
  ('郑倩', 'zhengqian@hzhfzx.com', '', 'video_manager', '', 1, now()),
  ('韩梦泽', 'hanmengze@hzhfzx.com', '', 'teacher', '', 1, now());
  
 INSERT INTO `cms_ucenters` (`user_id`, `enterprise_userid`, `created_at`) values
  (1, 'ZhangZhongQin', now()),
  (2, 'haopingtui', now()),
  (3, 'lizongheng', now()),
  (4, 'wangjian1', now()),
  (5, 'hanke', now()),
  (6, 'mochengjun', now()),
  (7, 'lipeng', now()),
  (8, 'luohongwei', now()),
  (9, '10017', now()),
  (10, 'ChenLuLu', now()),
  (11, 'zhengqian', now());
  
 INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`) VALUES
  ('admin', '系统管理', 'root', 1, now()),
  ('user', '用户管理', 'admin', 1, now()),
  ('permission', '权限管理', 'admin', 1, now()),
  ('resource', '资源管理', 'root', 1, now()),
  ('video', '视频管理', 'resource', 1, now());
  
 INSERT INTO `cms_grants`(`user_id`, `permission_code`, `active`, `created_at`) VALUES
  (1, 'user', 1, now()),
  (1, 'permission', 1, now()),
  (1, 'video', 1, now()),
  (2, 'user', 1, now()),
  (2, 'permission', 1, now()),
  (2, 'video', 1, now()),
  (10, 'video', 1, now()),
  (11, 'video', 1, now());
  
INSERT INTO `cms_categories`(`name`, `active`, `created_at`) values
  ('早间读报', 1, now()),
  ('早盘论势', 1, now()),
  ('黄金十点半', 1, now()),
  ('老莫看盘', 1, now()),
  ('顺势狙击', 1, now()),
  ('热点为王', 1, now()),
  ('老莫看主力', 1, now()),
  ('价值先锋', 1, now()),
  ('产业风云', 1, now()),
  ('晚间聊股', 1, now()),
  ('财富人生', 0, now());
  
INSERT INTO `cms_cat_to_tches`(`user_id`, `category_id`, `active`, `created_at`) VALUES
  (3, 1, 1, now()),
  (4, 2, 1, now()),
  (5, 3, 1, now()),
  (6, 4, 1, now()),
  (12, 5, 1, now()),
  (5, 6, 1, now()),
  (6, 7, 1, now()),
  (4, 8, 1, now()),
  (7, 9, 1, now()),
  (8, 10, 1, now()),
  (9, 10, 1, now());