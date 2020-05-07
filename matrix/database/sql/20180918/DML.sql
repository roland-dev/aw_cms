/*
*新增栏目表中service字段数据 如果是课程登记视频用到的栏目，用course表示
*/
insert into cms_categories (name, code, active, created_at, updated_at, description, summary) values ('学战法-课程视频', 'xuezhanfa_course', 1, now(), now(), '', '');

/*
*新增视频登记表中 is_public字段，
*/
/*alter table cms_video_signins add column is_public int(11) NOT NULL DEFAULT '1'  COMMENT  '0:课程视频，1:普通登记视频'; */

/*
*栏目分组数据表写入 视频登记相关栏目
*/
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('zaojiandubao', '视频登记分组', 'shipindengji_group',  now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('huangjinshidianban', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('laomokanpan', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('shunshijuji', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('redianweiwang', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('laomokanzhuli', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('jiazhixianfeng', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('chanyefengyun', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('wanjianliaogu', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('caifurensheng', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('caifuguanli', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('guanggelunshi', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('jiazhijuejin', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('wanjiafupan', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('heshuguanggu', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('caifuguanli_qiyashen', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('caifuguanli_lipeng', '视频登记分组', 'shipindengji_group', now(), now(), '');
insert into cms_category_groups (category_code, name, code, created_at, updated_at, description) values ('caifuguanli_luosheng', '视频登记分组', 'shipindengji_group', now(), now(), '');

insert into cms_users (name, email, password, type, icon_url, active, remember_token, created_at, updated_at) values ('众赢量化工作室', 'zhongyinglianghuagongzuoshi@hzhfzx.com', '', 'teacher', '', 1, '', now(), now() );

insert into cms_users (name, email, password, type, icon_url, active, remember_token, created_at, updated_at) values ('产业资本研究中心', 'chanyezibenyanjiuzhongxin@hzhfzx.com', '', 'teacher', '', 1, '', now(), now() );
