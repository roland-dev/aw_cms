ALTER TABLE `cms_ad_locations` ADD `file_size` int(11) NOT NULL COMMENT '图片文件大小' after `size`;
ALTER TABLE `cms_ad_locations` ADD `popup_img_file_size` int(11) DEFAULT NULL COMMENT '弹出图片文件大小' after `popup_img_size`;


ALTER TABLE `cms_twitters` ADD COLUMN `source_id` VARCHAR(191) NOT NULL DEFAULT '';
