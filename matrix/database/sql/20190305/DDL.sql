ALTER TABLE `cms_teacher_follows` ADD COLUMN `business` VARCHAR(191) NOT NULL DEFAULT 'default';
ALTER TABLE `cms_teacher_follows` ADD COLUMN `active` TINYINT(4) NOT NULL DEFAULT 1;
ALTER TABLE `cms_teacher_follows` ADD COLUMN `sync_to_uc` TINYINT(4) NOT NULL DEFAULT 0;
ALTER TABLE `cms_teacher_follows` ADD UNIQUE KEY `uk_business_open_id_user_id`(`business`, `open_id`, `user_id`);
