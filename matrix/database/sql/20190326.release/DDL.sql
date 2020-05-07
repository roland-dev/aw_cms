--
-- Table structure for table `cms_ad_locations`
--

CREATE TABLE `cms_ad_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告类型ID',
  `code` varchar(32) NOT NULL COMMENT '广告类型code',
  `name` varchar(16) NOT NULL COMMENT '广告类型name',
  `num` int(11) NOT NULL COMMENT '广告位数量',
  `size` varchar(191) NOT NULL COMMENT '推荐尺寸',
  `file_size` int(11) NOT NULL COMMENT '图片文件大小',
  `popup_img_size` varchar(191) DEFAULT NULL COMMENT '弹出广告推荐尺寸',
  `popup_img_file_size` int(11) DEFAULT NULL COMMENT '弹出图片文件大小',
  `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否禁用 0：否 1：是',
  `default_ad_id` int(10) DEFAULT NULL COMMENT '默认广告ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_locations_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_ad_operation_types`
--

CREATE TABLE `cms_ad_operation_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告业务类型ID',
  `code` varchar(32) NOT NULL COMMENT '广告业务类型code',
  `name` varchar(16) NOT NULL COMMENT '广告业务类型name',
  `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否禁用 0：否 1：是',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_operation_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_ad_terminals`
--

CREATE TABLE `cms_ad_terminals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `ad_id` int(11) NOT NULL COMMENT '广告ID',
  `terminal_code` varchar(32) NOT NULL COMMENT '展示终端Code',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_ads`
--

CREATE TABLE `cms_ads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告ID',
  `location_code` varchar(32) NOT NULL COMMENT '广告位类型Code',
  `media_code` varchar(32) NOT NULL COMMENT '广告媒体类型Code',
  `operation_code` varchar(32) NOT NULL COMMENT '广告业务类型Code',
  `operation_id` int(11) DEFAULT '0' COMMENT '广告来源ID 默认：0',
  `title` varchar(255) NOT NULL COMMENT '广告名称',
  `img_src` varchar(300) NOT NULL COMMENT '图片地址',
  `url_link` varchar(300) NOT NULL COMMENT '链接地址',
  `start_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '展示开始时间',
  `end_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '展示结束时间',
  `sort_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序序号',
  `disabled` tinyint(4) NOT NULL COMMENT '是否禁用 0：否 1：是',
  `creator_id` int(11) NOT NULL COMMENT '创建人',
  `updated_user_id` int(11) NOT NULL COMMENT '最后修改人',
  `need_popup` tinyint(4) DEFAULT NULL COMMENT '是否需要弹出框',
  `popup_poster_url` varchar(300) DEFAULT NULL COMMENT '弹出海报地址（为空则与post_url相同处理)',
  `jump_type` varchar(32) NOT NULL DEFAULT 'common_web' COMMENT 'web: h5 webview; pdf: pdf viewer; video: 视频播放器; stream: 视频流; battle: 炒股大赛原生模块; broker: 券商开户原生模块...',
  `jump_params` varchar(100) DEFAULT NULL COMMENT '提供给原生跳转使用的参数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_article_likes`
--

CREATE TABLE `cms_article_likes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '点赞人的uc openid',
  `type` varchar(191) NOT NULL DEFAULT '' COMMENT '被点赞内容类型',
  `article_id` varchar(191) NOT NULL DEFAULT '' COMMENT '被点赞内容id',
  `udid` varchar(191) NOT NULL DEFAULT '' COMMENT '内容点赞识别安装的App字段',
  `user_type` varchar(64) DEFAULT '' COMMENT '点赞人类型',
  `session_id` varchar(191) DEFAULT '' COMMENT '会话id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_article_reads`
--

CREATE TABLE `cms_article_reads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '阅读人uc openid',
  `ip` varchar(191) NOT NULL DEFAULT '' COMMENT '阅读人IP地址',
  `type` varchar(191) NOT NULL DEFAULT '' COMMENT '阅读内容类型',
  `article_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '阅读内容id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_article_replies`
--

CREATE TABLE `cms_article_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '用户openid',
  `session_id` varchar(191) NOT NULL DEFAULT '' COMMENT '用户sessionid',
  `type` varchar(191) NOT NULL DEFAULT '' COMMENT '原文内容类型 article|talkshow|course',
  `article_id` varchar(191) NOT NULL DEFAULT '' COMMENT '原文内容id',
  `article_title` varchar(191) NOT NULL DEFAULT '' COMMENT '原文内容标题',
  `article_author_user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '作者user_id',
  `content` varchar(512) NOT NULL DEFAULT '' COMMENT '评论内容',
  `ref_id` int(10) NOT NULL DEFAULT '0' COMMENT '回复目标评论id',
  `ref_content` varchar(512) NOT NULL DEFAULT '' COMMENT '回复目标评论内容',
  `ref_open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '回复目标评论作者OpenId',
  `status` tinyint(4) NOT NULL DEFAULT '10' COMMENT '评论状态 10 = 待审核 | 20 = 审核通过 | 30 = 审核拒绝',
  `examine_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审批操作人cms_users.id',
  `examine_at` timestamp NULL DEFAULT NULL COMMENT '审批操作时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_articles`
--

CREATE TABLE `cms_articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(191) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '分类代码',
  `sub_category_code` varchar(191) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '子分类代码',
  `title` varchar(191) NOT NULL DEFAULT '' COMMENT '文章标题',
  `summary` varchar(191) NOT NULL DEFAULT '' COMMENT '文章摘要',
  `description` varchar(191) NOT NULL DEFAULT '' COMMENT '文章描述',
  `content` text NOT NULL COMMENT '文章内容',
  `audio_url` varchar(191) NOT NULL DEFAULT '' COMMENT '合成语音地址',
  `teacher_id` int(11) NOT NULL DEFAULT '0' COMMENT '作者teacher_id',
  `feed` tinyint(4) DEFAULT '0' COMMENT '是否已经同步到feed，0=false, 1=true',
  `modify_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '最后修改人的users.id',
  `show` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否对外展示',
  `cover_url` varchar(191) NOT NULL DEFAULT '' COMMENT '封面URL',
  `read` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '已读数',
  `ad_guide` varchar(500) DEFAULT NULL COMMENT '广告引导语',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_categories`
--

CREATE TABLE `cms_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录id',
  `name` varchar(191) NOT NULL DEFAULT '' COMMENT '栏目分类名称',
  `code` varchar(191) NOT NULL COMMENT '栏目分类Code',
  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否激活： 1、是 0、否',
  `description` text COMMENT '详细描述',
  `summary` varchar(191) NOT NULL DEFAULT '' COMMENT '摘要',
  `service_key` varchar(191) DEFAULT 'basic' COMMENT '服务Key',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_categories_code_unique` (`code`),
  KEY `index_category_code` (`code`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_category_groups`
--

CREATE TABLE `cms_category_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录id',
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '栏目分类分组Code',
  `name` varchar(191) NOT NULL DEFAULT '' COMMENT '栏目分类分组名称',
  `category_code` varchar(191) NOT NULL DEFAULT '' COMMENT '对应栏目分类',
  `description` varchar(191) NOT NULL DEFAULT '' COMMENT '详细描述',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_category_groups_code_category_code_unique` (`code`,`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_content_guards`
--

CREATE TABLE `cms_content_guards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_code` varchar(191) NOT NULL COMMENT '服务代码',
  `uri` varchar(191) NOT NULL COMMENT '访问资源的URI',
  `param1` varchar(191) DEFAULT NULL COMMENT '第一参数',
  `param2` varchar(191) DEFAULT NULL COMMENT '第二参数',
  `param3` varchar(191) DEFAULT NULL COMMENT '第三参数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_params` (`param1`,`param2`,`param3`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_course_systems`
--

CREATE TABLE `cms_course_systems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL COMMENT '课程体系名称',
  `code` varchar(191) NOT NULL COMMENT '课程体系代码',
  `creator_user_id` int(11) NOT NULL COMMENT '创建者users.id',
  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否生效, 0=false, 1=true',
  `sort_no` varchar(64) DEFAULT NULL COMMENT '排序序号',
  `primary_category` varchar(64) DEFAULT NULL COMMENT '课程类型代码',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_systems_name_index` (`name`),
  KEY `course_systems_creator_user_id_index` (`creator_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_course_videos`
--

CREATE TABLE `cms_course_videos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `picture_path` varchar(500) NOT NULL,
  `is_display` tinyint(4) NOT NULL COMMENT '视频是否在app显示',
  `access` varchar(128) NOT NULL COMMENT '点击数',
  `watch` varchar(128) NOT NULL COMMENT '观看数',
  `end` varchar(128) NOT NULL COMMENT '看完数',
  `course_code` varchar(191) DEFAULT NULL COMMENT '课程代码',
  `video_signin_id` int(11) NOT NULL COMMENT '视频登记表id',
  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否生效',
  `tag` varchar(64) DEFAULT NULL COMMENT '专属标签',
  `sort_no` varchar(64) DEFAULT NULL COMMENT '排序序号',
  `demo_url` varchar(1000) DEFAULT NULL COMMENT '试看url',
  `ad_guide` varchar(500) DEFAULT NULL COMMENT '广告语引导',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_videos_updated_at_index` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_courses`
--

CREATE TABLE `cms_courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL COMMENT '课程名称',
  `code` varchar(191) NOT NULL COMMENT '课程代码',
  `description` text COMMENT '课程描述',
  `background_picture` varchar(255) DEFAULT NULL COMMENT '课程背景图',
  `course_system_code` varchar(191) NOT NULL COMMENT '课程体系代码',
  `creator_user_id` int(11) NOT NULL COMMENT '创建者users.id',
  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否生效, 0=false, 1=true',
  `sort_no` varchar(64) DEFAULT NULL COMMENT '排序序号',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `code_2` (`code`),
  KEY `courses_name_index` (`name`),
  KEY `courses_course_system_code_index` (`course_system_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_custom_apps`
--

CREATE TABLE `cms_custom_apps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL COMMENT '第三方系统代码',
  `name` varchar(191) NOT NULL DEFAULT '' COMMENT '第三方系统名称',
  `secret` varchar(191) NOT NULL DEFAULT '' COMMENT '第三方系统密钥',
  `remark` varchar(191) NOT NULL DEFAULT '' COMMENT '第三方系统备注',
  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '第三方系统账号是否启用, 0=false, 1=true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_apps_code_unique` (`code`),
  UNIQUE KEY `custom_apps_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_customers`
--

CREATE TABLE `cms_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT 'uc open_id',
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT 'uc customer_code',
  `qy_userid` varchar(191) DEFAULT '' COMMENT 'uc qy_userid',
  `name` varchar(191) NOT NULL DEFAULT '' COMMENT '姓名',
  `mobile` varchar(191) NOT NULL DEFAULT '' COMMENT '手机号码',
  `nickname` varchar(191) DEFAULT '' COMMENT '客户昵称',
  `icon_url` varchar(191) NOT NULL DEFAULT '' COMMENT '头像URL',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_forums`
--

CREATE TABLE `cms_forums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '论坛ID',
  `theme` varchar(255) NOT NULL COMMENT '论坛主题',
  `img_src` varchar(255) NOT NULL COMMENT '图片地址',
  `relatively_file_path` varchar(255) DEFAULT NULL COMMENT '图片相对路径',
  `url_key` varchar(50) DEFAULT NULL COMMENT '展示互动id',
  `url_link` varchar(500) NOT NULL COMMENT '展示互动播放url',
  `forum_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '论坛直播日期',
  `visible_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '论坛展示日期',
  `duration` int(11) NOT NULL COMMENT '论坛时长(min)',
  `teacher` varchar(100) NOT NULL COMMENT '主讲嘉宾',
  `abstract` varchar(500) NOT NULL COMMENT '论坛简介',
  `creator_id` int(11) NOT NULL COMMENT '创建人',
  `updated_user_id` int(11) NOT NULL COMMENT '最后修改人',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_grants`
--

CREATE TABLE `cms_grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `permission_code` varchar(191) NOT NULL COMMENT '权限码',
  `active` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_like_statistics`
--

CREATE TABLE `cms_like_statistics` (
  `article_id` varchar(191) NOT NULL DEFAULT '' COMMENT '记录id',
  `type` varchar(191) NOT NULL COMMENT '记录类型',
  `like_sum` int(11) NOT NULL DEFAULT '0' COMMENT '点赞总数',
  `customer_like_sum` int(11) NOT NULL DEFAULT '0' COMMENT '客户点赞总数',
  `staff_like_sum` int(11) NOT NULL DEFAULT '0' COMMENT '员工点赞总数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`article_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_logs`
--

CREATE TABLE `cms_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_key` varchar(191) NOT NULL COMMENT '操作模块',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `original_data` text NOT NULL COMMENT '历史数据',
  `operate` varchar(191) NOT NULL COMMENT '执行操作',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_move_qr_groups`
--

CREATE TABLE `cms_move_qr_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '唯一标识随机码',
  `title` varchar(191) NOT NULL DEFAULT '' COMMENT '固定二维码组标题',
  `max_fans` int(11) NOT NULL DEFAULT '0' COMMENT '活码最大访问次数',
  `remark` varchar(191) NOT NULL DEFAULT '' COMMENT '固定二维码备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_move_qrs`
--

CREATE TABLE `cms_move_qrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '活码唯一标识随机码',
  `move_qr_group_code` varchar(191) NOT NULL DEFAULT '' COMMENT '静态码唯一标识随机码',
  `title` varchar(191) NOT NULL DEFAULT '' COMMENT '活码二维码组标题',
  `filename` varchar(191) NOT NULL DEFAULT '' COMMENT '活码二维码文件名',
  `remark` varchar(191) NOT NULL DEFAULT '' COMMENT '活码二维码备注',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '活码二维码顺序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_permissions`
--

CREATE TABLE `cms_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) NOT NULL COMMENT '权限码',
  `name` varchar(191) NOT NULL COMMENT '权限名',
  `pre_code` varchar(191) NOT NULL COMMENT '父级权限码',
  `active` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_private_message_guards`
--

CREATE TABLE `cms_private_message_guards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(11) NOT NULL DEFAULT '0' COMMENT '私信的teacher_id',
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '私信的用户open_id',
  `operator_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '审批操作人users.id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审批状态',
  `source_type` varchar(191) DEFAULT 'customer' COMMENT '审批来源 customer: 客户 re_review: 复审',
  `review_status` tinyint(4) DEFAULT NULL COMMENT '上次复审结果 0:复审通过（特定用户） 1:复审被拒',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_private_messages`
--

CREATE TABLE `cms_private_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `direction` tinyint(4) NOT NULL DEFAULT '0' COMMENT '私信方向',
  `teacher_id` int(11) NOT NULL DEFAULT '0' COMMENT '老师的teacher_id',
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '用户的open_id',
  `content` text NOT NULL COMMENT '私信内容',
  `read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已读',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_reply_cnts`
--

CREATE TABLE `cms_reply_cnts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_type` varchar(191) NOT NULL DEFAULT '' COMMENT '内容类型 twitter|article|talkshow|news|course',
  `content_id` varchar(191) NOT NULL DEFAULT '' COMMENT '内容id',
  `cnt` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '这个状态的评论总数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_sub_categories`
--

CREATE TABLE `cms_sub_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录id',
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '子栏目分类Code',
  `name` varchar(191) NOT NULL DEFAULT '' COMMENT '子栏目分类名称',
  `category_code` varchar(191) NOT NULL DEFAULT '' COMMENT '所属父级栏目Code',
  `active` int(11) NOT NULL DEFAULT '0' COMMENT '是否激活： 1、是 0、否',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_sub_categories_code_category_code_unique` (`code`,`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_system_notices`
--

CREATE TABLE `cms_system_notices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL DEFAULT '' COMMENT '系统通知标题',
  `content` varchar(191) NOT NULL DEFAULT '' COMMENT '系统通知内容',
  `target` tinyint(4) NOT NULL DEFAULT '0' COMMENT '系统通知目标',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '系统通知后台用户users.id',
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '系统通知C端用户open_id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `read` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_teacher_follows`
--

CREATE TABLE `cms_teacher_follows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '老师的users.id',
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '用户的open_id',
  `business` varchar(191) NOT NULL DEFAULT 'default' COMMENT '业务线 default | hk',
  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否有效',
  `sync_to_uc` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已经同步到UC, 0=false, 1=true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_business_open_id_user_id` (`business`,`open_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_teacher_tabs`
--

CREATE TABLE `cms_teacher_tabs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录id',
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '内容标签Code',
  `name` varchar(191) NOT NULL DEFAULT '' COMMENT '内容标签name',
  `teacher_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '老师的userid',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '内容标签的排序',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_teacher_tabs_code_name_teacher_user_id_unique` (`code`,`name`,`teacher_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_teachers`
--

CREATE TABLE `cms_teachers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录id',
  `category_code` varchar(191) NOT NULL DEFAULT '' COMMENT '栏目分类code',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户Id',
  `icon_url` varchar(191) NOT NULL DEFAULT '' COMMENT '头像url',
  `visitor_video_url` varchar(191) DEFAULT '' COMMENT '视频地址（访客）',
  `customer_video_url` varchar(191) DEFAULT '' COMMENT '视频地址（客户）',
  `cover_url` varchar(191) DEFAULT '' COMMENT '封面地址',
  `description` varchar(191) NOT NULL DEFAULT '' COMMENT '详细描述',
  `primary` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是主笔老师： 1、主笔老师 0、非主笔老师',
  `active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否激活： 1、是 0、否',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_teachers_category_code_user_id_unique` (`category_code`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_terminals`
--

CREATE TABLE `cms_terminals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '展示终端ID',
  `code` varchar(32) NOT NULL COMMENT '展示终端code',
  `name` varchar(16) NOT NULL COMMENT '展示终端name',
  `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否禁用 0：否 1：是',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `terminals_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_text_audio_tasks`
--

CREATE TABLE `cms_text_audio_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `process_time` datetime NOT NULL,
  `process_duration` bigint(20) NOT NULL DEFAULT '0',
  `path` varchar(191) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_twitter_guards`
--

CREATE TABLE `cms_twitter_guards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录id',
  `category_code` varchar(191) NOT NULL DEFAULT '' COMMENT '栏目分类Code',
  `open_id` varchar(191) NOT NULL DEFAULT '' COMMENT '客户openId',
  `operator_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人user_id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审批状态 0: 审批中 1:审批成功 2:审批失败',
  `source_type` varchar(191) DEFAULT 'customer' COMMENT '审批来源 customer: 客户 manage_system:管理端 auto_program: 自动执行脚本',
  `review_status` tinyint(4) DEFAULT NULL COMMENT '上次复审结果 0:复审通过（特定用户） 1:复审被拒',
  `is_qualified` tinyint(4) DEFAULT NULL COMMENT '审批时是否达标 0: 不达标 1: 达标',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_twitters`
--

CREATE TABLE `cms_twitters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL COMMENT '解盘内容',
  `category_code` varchar(191) NOT NULL DEFAULT '' COMMENT '栏目代码',
  `teacher_id` int(11) NOT NULL DEFAULT '0' COMMENT '老师的teacher_id',
  `feed` tinyint(4) DEFAULT '0' COMMENT '是否已经同步到feed',
  `operator_user_id` int(11) DEFAULT '0' COMMENT '发布人users.id',
  `room_id` varchar(191) DEFAULT '' COMMENT '看高手房间objectid',
  `image_url` varchar(191) DEFAULT '' COMMENT '发送的图片',
  `source_id` varchar(191) NOT NULL DEFAULT '' COMMENT '看高手源objectid',
  `ref_type` varchar(191) DEFAULT '' COMMENT '转发内容类型 article|talkshow|news|course',
  `ref_category_code` varchar(191) DEFAULT '' COMMENT '转发内容分类',
  `ref_id` varchar(191) DEFAULT '' COMMENT '转发内容源id',
  `ref_thumb` varchar(191) DEFAULT '' COMMENT '转发内容缩略图',
  `ref_title` varchar(191) DEFAULT '' COMMENT '转发内容标题',
  `ref_summary` varchar(191) DEFAULT '' COMMENT '转发内容摘要',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_ucenters`
--

CREATE TABLE `cms_ucenters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `enterprise_userid` varchar(191) NOT NULL COMMENT '企业用户user_id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_user_groups`
--

CREATE TABLE `cms_user_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录id',
  `code` varchar(191) NOT NULL DEFAULT '' COMMENT '用户组Code',
  `name` varchar(191) NOT NULL DEFAULT '' COMMENT '用户组name',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '用户在 当前用户组 当中的序号',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_user_groups_code_name_user_id_unique` (`code`,`name`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `cms_users`
--

CREATE TABLE `cms_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL COMMENT '用户名',
  `email` varchar(191) NOT NULL COMMENT '用户邮件',
  `password` varchar(191) NOT NULL COMMENT '用户密码',
  `type` varchar(64) NOT NULL COMMENT '用户类型',
  `icon_url` varchar(500) NOT NULL COMMENT '用户头像',
  `cert_no` varchar(191) DEFAULT '' COMMENT '证书编号',
  `description` text COMMENT '个人描述',
  `selected` int(4) DEFAULT NULL COMMENT '是否可选',
  `active` tinyint(4) NOT NULL COMMENT '是否有效, 0=false, 1=true',
  `remember_token` varchar(100) DEFAULT NULL COMMENT '记住登录状态',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `cms_video_signins`
--

CREATE TABLE `cms_video_signins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `video_key` varchar(191) CHARACTER SET utf8mb4 NOT NULL COMMENT '视频加密key值',
  `creator_user_id` int(11) NOT NULL COMMENT '记录添加人id',
  `url` varchar(191) NOT NULL COMMENT '视频url',
  `category` varchar(64) DEFAULT NULL COMMENT '已废弃',
  `category_code` varchar(64) DEFAULT '' COMMENT '栏目代码',
  `author` int(16) NOT NULL COMMENT '作者users.id',
  `title` varchar(191) NOT NULL COMMENT '标题',
  `description` text COMMENT '内容',
  `published_at` varchar(191) DEFAULT NULL COMMENT '视频发布时间',
  `active` tinyint(4) NOT NULL COMMENT '是否有效, 0=false, 1=true',
  `is_public_player` int(11) DEFAULT '0' COMMENT '是否使用通用播放器, 0=使用, 1=不使用',
  `is_public` int(11) NOT NULL DEFAULT '1' COMMENT '是否需要鉴权, 0=需要, 1=不需要',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_video_key` (`video_key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `feed`
--

CREATE TABLE `feed` (
  `feed_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `feed_owner` varchar(100) NOT NULL COMMENT '消息发起方/一级栏目名称',
  `feed_type` tinyint(4) NOT NULL COMMENT '一级分类编号：0-调仓; 1-研报; 2-大盘分析; 3-学战法; 4-周战报；5-量化金股池；6-产业金股池；7-智能仓位；8-热点轮动；9-模拟交易；10-实盘交易；99-客服盈盈',
  `category_key` varchar(30) NOT NULL DEFAULT '' COMMENT '二级分类key',
  `msg_type` varchar(20) NOT NULL DEFAULT 'news' COMMENT '内容媒体类型',
  `owner_id` varchar(50) CHARACTER SET ascii DEFAULT NULL COMMENT '消息发起方id',
  `source_id` varchar(50) DEFAULT NULL COMMENT '消息源记录key',
  `title` varchar(200) NOT NULL COMMENT '标题',
  `summary` varchar(500) DEFAULT NULL COMMENT '摘要',
  `source_url` varchar(500) DEFAULT NULL COMMENT '详情链接',
  `thumb_cdn_url` varchar(500) DEFAULT NULL COMMENT '缩略图cdn url',
  `thumb_local_url` varchar(300) DEFAULT NULL COMMENT '缩略图本地路径',
  `origin_image_url` varchar(300) DEFAULT NULL COMMENT '原图url',
  `access_level` varchar(50) NOT NULL DEFAULT '' COMMENT '访问所需授权等级',
  `access_backup` varchar(191) DEFAULT '',
  `is_elite` tinyint(4) DEFAULT NULL COMMENT '是否精选内容',
  `push_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '推送状态：0-未推送；1-wx推送失败；2-wx推送成功; 3-push失败; 4-成功',
  `push_time` datetime DEFAULT NULL COMMENT '推送时间',
  `push_error` varchar(500) DEFAULT NULL COMMENT '推送返回错误信息',
  `push_list` mediumtext COMMENT '接受人员列表',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creator` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`feed_id`),
  KEY `search_key` (`feed_type`,`source_id`) USING HASH,
  KEY `index_category` (`category_key`) USING HASH,
  KEY `index_add_time` (`add_time`),
  KEY `index_feed_title` (`title`(191)),
  KEY `unique_source_id` (`source_id`),
  KEY `index_status` (`push_status`),
  KEY `index_teachers` (`feed_type`,`owner_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

