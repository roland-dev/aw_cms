/**
 * 注意！本次更新脚本使用必须保证没有重名人员，并且更新的是三位老师的头像，用于视频推广
 */
UPDATE `cms_users` SET `icon_url` = 'assets/image/headicon/wangjian.png' WHERE `name` = '王健';
UPDATE `cms_users` SET `icon_url` = 'assets/image/headicon/hanke.png' WHERE `name` = '韩珂';
UPDATE `cms_users` SET `icon_url` = 'assets/image/headicon/mochengjun.png' WHERE `name` = '莫成军';
