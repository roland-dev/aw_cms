
ALTER TABLE `cms_video_signins` MODIFY `category` VARCHAR(64) DEFAULT NULL;
ALTER TABLE `cms_video_signins` ADD COLUMN `category_code` VARCHAR(64) DEFAULT '' AFTER `category`;



UPDATE `cms_video_signins` AS v LEFT JOIN  `cms_categories` AS c ON v.`category` = c.`id` SET v.`category_code` = c.`code` WHERE v.`category` <> 'null';

