INSERT INTO `cms_categories`(`code`, `name`, `summary`, `description`, `created_at`, `updated_at`, `active`) VALUES
  ('hk_lipeng', '李鹏', '', '', NOW(), NOW(), 1),
  ('hk_laoding', '老丁', '', '', NOW(), NOW(), 1),
  ('hk_fangzhi', '方志', '', '', NOW(), NOW(), 1),
  ('hk_heshu', '和叔', '', '', NOW(), NOW(), 1),
  ('kgs_q', '量化看高手', '', '', NOW(), NOW(), 1),
  ('kgs_c', '产业看高手', '', '', NOW(), NOW(), 1);

INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`) VALUES
  ('hk_lipeng', 7, 'http://res.zhongyingtougu.com/cms/head_icon/hk_lipeng.png', 'http://res.zhongyingtougu.com/cms/banner/hk_video_cover.jpg', '', 1, 1, NOW(), NOW()),
  ('hk_laoding', 29, 'http://res.zhongyingtougu.com/cms/head_icon/hk_laoding.png', 'http://res.zhongyingtougu.com/cms/banner/hk_video_cover.jpg', '', 1, 1, NOW(), NOW()),
  ('hk_fangzhi', 32, 'http://res.zhongyingtougu.com/cms/head_icon/hk_fangzhi.png', 'http://res.zhongyingtougu.com/cms/banner/hk_video_cover.jpg', '', 1, 1, NOW(), NOW()),
  ('hk_heshu', 20, 'http://res.zhongyingtougu.com/cms/head_icon/hk_heshu.png', 'http://res.zhongyingtougu.com/cms/banner/hk_video_cover.jpg', '', 1, 1, NOW(), NOW());

INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`) SELECT 'kgs_q', `cms_users`.`id`, '', '', '', 0, 1, NOW(), NOW() FROM `cms_users`, `cms_ucenters` WHERE `cms_users`.`id` = `cms_ucenters`.`user_id` AND `cms_ucenters`.`enterprise_userid` IN ('hanke', 'hanmengze', 'lizongheng', 'mochengjun', 'xiongyuan', 'zhanghan', 'xialiyuan', 'zhuguang', 'yangjian_tg', 'lipeng', 'Gdiongchong', 'wangjian1', '10017', '001018', 'liebaotouyan', 'lvxuedong');
INSERT INTO `cms_teachers`(`category_code`, `user_id`, `icon_url`, `cover_url`, `description`, `primary`, `active`, `created_at`, `updated_at`) SELECT 'kgs_c', `cms_users`.`id`, '', '', '', 0, 1, NOW(), NOW() FROM `cms_users`, `cms_ucenters` WHERE `cms_users`.`id` = `cms_ucenters`.`user_id` AND `cms_ucenters`.`enterprise_userid` IN ('hanke', 'hanmengze', 'lizongheng', 'mochengjun', 'xiongyuan', 'zhanghan', 'xialiyuan', 'zhuguang', 'yangjian_tg', 'lipeng', 'Gdiongchong', 'wangjian1', '10017', '001018', 'liebaotouyan', 'lvxuedong');

INSERT INTO `cms_category_groups`(`code`, `name`, `category_code`, `description`, `created_at`, `updated_at`, `sort`) VALUES
  ('xiangjianglunjian', '香江论剑', 'hk_lipeng', '', NOW(), NOW(), 0),
  ('xiangjianglunjian', '香江论剑', 'hk_laoding', '', NOW(), NOW(), 0),
  ('xiangjianglunjian', '香江论剑', 'hk_fangzhi', '', NOW(), NOW(), 0),
  ('xiangjianglunjian', '香江论剑', 'hk_heshu', '', NOW(), NOW(), 0),
  ('twitter_group_a', 'A股解盘', 'kgs_q', '', NOW(), NOW(), 0),
  ('twitter_group_a', 'A股解盘', 'kgs_c', '', NOW(), NOW(), 0);

INSERT INTO `cms_user_groups`(`code`, `name`, `user_id`, `sort`, `created_at`, `updated_at`) SELECT 'teacher_stock_a', 'A股牛人', `user_id`, 10, NOW(), NOW() FROM `cms_ucenter` WHERE `enterprise_userid` IN ('mochengjun', 'hanke', '10017', 'wangjian1', 'hanmengze', 'zhuguang', 'xialiyuan', 'lipeng', '001018', 'liebaotouyan', 'youweitouyan', 'xiongyuan', 'Gdiongchong', 'lvxuedong', 'qiyashen');
INSERT INTO `cms_user_groups`(`code`, `name`, `user_id`, `sort`, `created_at`, `updated_at`) SELECT 'teacher_select_stock_a', 'A股可评价牛人', `user_id`, 10, NOW(), NOW() FROM `cms_ucenter` WHERE `enterprise_userid` IN ('mochengjun', 'hanke', '10017', 'wangjian1', 'hanmengze', 'zhuguang', 'xialiyuan', 'lipeng', 'Gdiongchong', '001018', 'liebaotouyan', 'youweitouyan');

INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`) SELECT 'all', '全部', `user_id`, 50, NOW(), NOW() FROM `cms_ucenters` WHERE `enterprise_userid` in ('mochengjun', 'hanke', '10017', 'wangjian1', 'hanmengze', 'zhuguang', 'xialiyuan', 'lipeng', '001018', 'liebaotouyan', 'youweitouyan', 'xiongyuan', 'Gdiongchong', 'lvxuedong', 'qiyashen');

INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`) SELECT 'twitter', '解盘', `user_id`, 40, NOW(), NOW() FROM `cms_ucenters` WHERE `enterprise_userid` in ('mochengjun', 'hanke', '10017', 'wangjian1', 'hanmengze', 'zhuguang', 'xialiyuan', 'lipeng', '001018', 'liebaotouyan', 'xiongyuan', 'Gdiongchong', 'lvxuedong');

INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`) SELECT 'talkshow', '节目', `user_id`, 30, NOW(), NOW() FROM `cms_ucenters` WHERE `enterprise_userid` in ('mochengjun', 'hanke', '10017', 'wangjian1', 'hanmengze', 'zhuguang', 'xialiyuan', 'lipeng', 'liebaotouyan', 'youweitouyan', 'Gdiongchong', 'qiyashen');

INSERT INTO `cms_teacher_tabs`(`code`, `name`, `teacher_user_id`, `sort`, `created_at`, `updated_at`) SELECT 'course', '课程', `user_id`, 10, NOW(), NOW() FROM `cms_ucenters` WHERE `enterprise_userid` in ('mochengjun', 'wangjian1', 'hanmengze', 'xialiyuan', 'lipeng', '001018', 'qiyashen');

INSERT INTO `cms_permissions`(`code`, `name`, `pre_code`, `active`, `created_at`, `updated_at`) VALUES
  ('twitter', '动态管理', 'content', 1, NOW(), NOW()),
  ('examine', '审批管理', 'root', 1, NOW(), NOW()),
  ('twitter_follow', '动态关注申请', 'examine', 1, NOW(), NOW()),
  ('private_message_follow', '私信聊天申请', 'examine', 1, NOW(), NOW()),
  ('private_message', '私信管理', 'content', 1, NOW(), NOW()),
  ('operate', '运营管理', 'root', 1, NOW(), NOW()),
  ('feed_elite', '内容精选', 'operate', 1, NOW(), NOW());
