-- 本次更新仅为修复错误的课程连接

UPDATE `cms_course_videos` SET `video_signin_id` = 1272 WHERE `video_signin_id` = 1348;
UPDATE `cms_course_videos` SET `video_signin_id` = 1281 WHERE `video_signin_id` = 1349;
UPDATE `cms_course_videos` SET `video_signin_id` = 1291 WHERE `video_signin_id` = 1351;
UPDATE `cms_course_videos` SET `video_signin_id` = 1299 WHERE `video_signin_id` = 1352;
UPDATE `cms_course_videos` SET `video_signin_id` = 1308 WHERE `video_signin_id` = 1353;
UPDATE `cms_course_videos` SET `video_signin_id` = 1317 WHERE `video_signin_id` = 1354;

-- 本更新修正课程没有准备好富文本简介的问题
UPDATE `cms_courses` SET `full_text_description` = `description`;

-- 本次更新仅为修复错误的课程连接
UPDATE `cms_course_videos` SET `video_signin_id` = 1779 WHERE `video_signin_id` = 1782;
UPDATE `cms_course_videos` SET `video_signin_id` = 1780 WHERE `video_signin_id` = 1783;
UPDATE `cms_course_videos` SET `video_signin_id` = 716 WHERE `video_signin_id` = 1784;
UPDATE `cms_course_videos` SET `video_signin_id` = 1776 WHERE `video_signin_id` = 1785;
