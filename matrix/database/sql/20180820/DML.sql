UPDATE `cms_categories` SET `code` = 'shipinjiepan' WHERE `id` = 1;
UPDATE `cms_categories` SET `code` = 'zaopankanshi' WHERE `id` = 2;
UPDATE `cms_categories` SET `code` = 'huangjinshidianban' WHERE `id` = 3;
UPDATE `cms_categories` SET `code` = 'laomokanpan' WHERE `id` = 4;
UPDATE `cms_categories` SET `code` = 'shunshijuji' WHERE `id` = 5;
UPDATE `cms_categories` SET `code` = 'redianweiwang' WHERE `id` = 6;
UPDATE `cms_categories` SET `code` = 'laomokanzhuli' WHERE `id` = 7;
UPDATE `cms_categories` SET `code` = 'chanyeshipin' WHERE `id` = 8;
UPDATE `cms_categories` SET `code` = 'chanyefengyun' WHERE `id` = 9;
UPDATE `cms_categories` SET `code` = 'wanjianliaogu' WHERE `id` = 10;
UPDATE `cms_categories` SET `code` = 'caifurensheng' WHERE `id` = 11;
UPDATE `cms_categories` SET `code` = 'caifuguanli' WHERE `id` = 12;
UPDATE `cms_categories` SET `code` = 'guanggelunshi' WHERE `id` = 13;
UPDATE `cms_categories` SET `code` = 'jiazhijuejin' WHERE `id` = 14;
UPDATE `cms_categories` SET `code` = 'wanjianfupan' WHERE `id` = 15;

INSERT INTO `cms_categories`(`code`, `name`, `summary`, `description`, `active`, `created_at`, `updated_at`) VALUES
	('heshuganggu', '和叔港股', '笑傲港股风云', '1.港股入门 （基础知识，交易规则）<br />2.港股打新<br />3.港股大势分析 盘面解读 个股机会（中长线，短线量化，短线机会，套利机会）<br />', 1, NOW(), NOW()),
	('caifuguanli_qiyashen', '财富管理-祁雅申专栏', '', '', 1, NOW(), NOW()),
	('caifuguanli_lipeng', '财富管理-李鹏专栏', '', '', 1, NOW(), NOW()),
	('caifuguanli_luosheng', '财富管理-罗生专栏', '', '', 1, NOW(), NOW());

INSERT INTO `cms_teachers` (`category_code`, `user_id`, `icon_url`, `description`, `primary`, `active`, `created_at`, `updated_at`) VALUES
	('shipinjiepan', 3, '', '', 1, 1, NOW(), NOW()),
    ('zaopankanshi', 4, 'assets/image/headicon/wangjian.png', '', 1, 1, NOW(), NOW()),
    ('huangjinshidianban', 5, 'assets/image/headicon/hanke.png', '', 1, 1, NOW(), NOW()),
    ('laomokanpan', 6, 'assets/image/headicon/mochengjun.png', '', 1, 1, NOW(), NOW()),
    ('shunshijuji', 12, '', '', 1, 1, NOW(), NOW()),
    ('redianweiwang', 5, 'assets/image/headicon/hanke.png', '', 1, 1, NOW(), NOW()),
    ('laomokanzhuli', 6, 'assets/image/headicon/mochengjun.png', '', 1, 1, NOW(), NOW()),
    ('chanyeshipin', 4, 'assets/image/headicon/wangjian.png', '', 1, 1, NOW(), NOW()),
    ('chanyefengyun', 7, '', '', 1, 1, NOW(), NOW()),
    ('wanjianliaogu', 8, 'assets/image/headicon/zyicon.png', '', 0, 1, NOW(), NOW()),
    ('wanjianliaogu', 9, 'assets/image/headicon/zyicon.png', '', 1, 1, NOW(), NOW()),
    ('caifuguanli', 16, 'http://hzhfzx.com/static/images/others/qys.jpg', '', 1, 1, NOW(), NOW()),
    ('guanggelunshi', 17, '', '', 1, 1, NOW(), NOW()),
    ('jiazhijuejin', 18, '', '', 1, 1, NOW(), NOW()),
    ('caifuguanli', 7, '', '', 0, 1, NOW(), NOW()),
    ('heshuganggu', 20, '', '腾讯财经、新浪财经特邀证券分析师，由于操盘手法成熟稳重老练，业内尊称为“和叔”。<br />撰写《创星50年最具成长性新股》，被国内著名财经杂志《创业家》刊登为封面文章。曾受邀前往华尔街与多位明星交易员交流，并在纳斯纳克接受专访。沉潜股市数十年，深研量化交易系统，独创“强势调整战法”“夹板突破战法”，且经实战验证颇为有效。<br />交易系统成熟，风控能力极强，成功躲过四次股灾。<br />', 1, 1, NOW(), NOW()),
    ('caifuguanli_qiyashen', 16, '', '', 1, 1, NOW(), NOW()),
    ('caifuguanli_lipeng', 7, '', '', 1, 1, NOW(), NOW()),
    ('caifuguanli_luosheng', 21, '', '', 1, 1, NOW(), NOW()),
    ('wanjianfupan', 9, '', '', 1, 1, NOW(), NOW()),
    ('wanjianfupan', 17, '', '', 0, 1, NOW(), NOW()),
    ('wanjianfupan', 12, '', '', 0, 1, NOW(), NOW());

INSERT INTO `cms_sub_categories`(`code`, `name`, `category_code`, `active`, `created_at`, `updated_at`) VALUES
    ('ganggujihui', '港股机会', 'heshuganggu', 1, NOW(), NOW()),
    ('ganggujiaoxue', '港股教学', 'heshuganggu', 1, NOW(), NOW());
