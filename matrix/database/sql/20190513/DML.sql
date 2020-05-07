-- 本次变更为视频登记更新功能的关联查询漏洞导致的无法正确更新数据

-- 手动变更线上登记错误的数据

UPDATE `cms_video_signins` SET `category_code` = 'yizhaozhisheng', `author` = 9 WHERE `id` = 2295;
