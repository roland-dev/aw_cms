-- 管理端广告管理调整广告、直播banner上传尺寸
UPDATE `cms_ad_locations` SET size = '1035*240', file_size = 100 WHERE code = 'banner';
