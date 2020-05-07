-- 本次更新仅针对慢查询优化增加索引
ALTER TABLE `cms_articles` ADD INDEX `index_category_code`(`category_code`);
