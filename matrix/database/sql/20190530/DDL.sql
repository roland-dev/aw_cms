ALTER IGNORE TABLE `cms_article_likes` ADD UNIQUE INDEX (`article_id`, `type`, `udid`, `open_id`);
