CREATE TABLE `cms_video_vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '供应商代码, gensee=展示互动',
  `name` varchar(191) NOT NULL DEFAULT '' COMMENT '供应商名称',
  `domain` varchar(191) NOT NULL DEFAULT '' COMMENT 'SDK参数，供应商域名',
  `remark` varchar(191) NOT NULL DEFAULT '' COMMENT '供应商备注',
  `last_modify_user_id` int(10) unsigned NOT NULL COMMENT '最后一个修改人cms_users.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_live_rooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL COMMENT '直播室代码，外部提供填入',
  `name` varchar(191) NOT NULL COMMENT '直播室名称',
  `password` varchar(191) NOT NULL DEFAULT '' COMMENT '直播室密码，外部设置后填入',
  `last_modify_user_id` int(10) unsigned NOT NULL COMMENT '最后一个修改人cms_users.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_static_talkshows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `video_vendor_code` varchar(191) NOT NULL DEFAULT '' COMMENT '视频供应商代码, gensee=展示互动',
  `title` varchar(191) NOT NULL COMMENT '节目标题',
  `teacher_id` int(10) unsigned NOT NULL COMMENT 'cms_teachers.id，包含cms_categories.code和cms_users.id两个信息',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `banner_url` varchar(500) NOT NULL DEFAULT '' COMMENT 'banner图片地址',
  `type` varchar(191) NOT NULL default 'live' COMMENT '节目类型，live = 直播，play = 点播',
  `live_room_code` varchar(191) NOT NULL COMMENT '直播室代码',
  `boardcast_content` varchar(500) NOT NULL DEFAULT '' COMMENT '播报内容',
  `last_modify_user_id` int(10) unsigned NOT NULL COMMENT '最后一个修改人cms_users.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_talkshows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL COMMENT '节目代码, 直播状态下自动生成，填入录播地址后更新为视频代码',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '预留状态，10=没有预告，20=蓝字预告，30=即将开始，40=正在播出，50=播放结束，60=看回放',
  `video_vendor_code` varchar(191) NOT NULL DEFAULT '' COMMENT '视频供应商代码, gensee=展示互动',
  `title` varchar(191) NOT NULL COMMENT '节目标题',
  `teacher_id` int(10) unsigned NOT NULL COMMENT 'cms_teachers.id，包含cms_categories.code和cms_users.id两个信息',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `banner_url` varchar(500) NOT NULL DEFAULT '' COMMENT 'banner图片地址',
  `type` varchar(191) NOT NULL default 'live' COMMENT '节目类型，live = 直播，play = 点播',
  `live_room_code` varchar(191) NOT NULL COMMENT '直播室代码',
  `boardcast_content` varchar(500) NOT NULL DEFAULT '' COMMENT '播报内容',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '海报简介',
  `play_url` varchar(500) NOT NULL DEFAULT '' COMMENT '录播地址',
  `last_modify_user_id` int(10) unsigned NOT NULL COMMENT '最后一个修改人cms_users.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_discusses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `live_room_code` varchar(191) NOT NULL COMMENT '直播室代码, cms_live_rooms.code',
  `talkshow_code` varchar(191) NOT NULL COMMENT '节目代码, cms_talkshows.code',
  `open_id` varchar(191) NOT NULL COMMENT '发言用户openId, UC的用户openId',
  `customer_name` varchar(191) NOT NULL COMMENT '发言用户姓名, UC的用户姓名',
  `icon_url` varchar(500) NOT NULL COMMENT '用户头像地址, UC的用户头像地址',
  `content` varchar(500) NOT NULL COMMENT '用户发言内容',
  `reply_to_open_id` varchar(191) NOT NULL COMMENT '回复目标用户openId, UC的用户openId',
  `reply_to_name` varchar(191) NOT NULL COMMENT '回复目标用户姓名, UC的用户姓名',
  `status` tinyint(4) NOT NULL COMMENT '发言审批状态，10=待审批, 20=审批通过（同已回复）, 30=审批拒绝',
  `examine_user_id` int(10) unsigned DEFAULT NULL COMMENT '审批人cms_users.id',
  `examine_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cms_operate_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `operator_user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '操作人员cms_users.id',
  `operate_code` varchar(191) NOT NULL DEFAULT '' COMMENT '操作代码 create|update|delete|approve|reject|publish',
  `operate_title` varchar(191) NOT NULL DEFAULT '' COMMENT '操作名称',
  `content_type` varchar(191) NOT NULL DEFAULT '' COMMENT '操作目标类型',
  `content_id` varchar(191) NOT NULL DEFAULT '' COMMENT '操作目标唯一标识id或code',
  `message` varchar(500) NOT NULL DEFAULT '' COMMENT '操作记录详情信息',
  `ip` varchar(191) NOT NULL DEFAULT '' COMMENT '操作人IP地址，点分十进制格式',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cms_categories` ADD COLUMN `ad_image_url` VARCHAR(500);
