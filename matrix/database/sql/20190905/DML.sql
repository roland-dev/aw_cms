UPDATE `cms_stock_reports`, `cms_article_likes` SET `cms_article_likes`.article_id = `cms_stock_reports`.report_id WHERE `cms_article_likes`.article_id = `cms_stock_reports`.id and `cms_article_likes`.type = 'stock_report';