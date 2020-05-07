-- 栏目头像 优化 数据处理

-- cms_users.icon_url 同步到 cms_teachers.icon_url 设置 cms_teachers.icon_url 为空
UPDATE `cms_teachers`, `cms_users` SET `cms_teachers`.icon_url = '' WHERE `cms_teachers`.user_id = `cms_users`.id AND `cms_teachers`.icon_url = `cms_users`.icon_url AND `cms_teachers`.icon_url != '';

-- 清空 cms_teachers 当中默认头像
UPDATE `cms_teachers` SET icon_url = '' WHERE `icon_url` = 'http://res.zhongyingtougu.com/cms/head_icon/default.png';