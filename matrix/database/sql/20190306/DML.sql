UPDATE `cms_teachers`, `cms_users` SET `cms_teachers`.icon_url = `cms_users`.icon_url WHERE `cms_teachers`.user_id = `cms_users`.id AND `cms_users`.icon_url LIKE '%http%' AND `cms_teachers`.icon_url NOT LIKE '%http%'; 
UPDATE `cms_teachers` SET `icon_url` = 'http://res.zhongyingtougu.com/cms/head_icon/default.png' WHERE `icon_url` = '' ;