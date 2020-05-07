-- ----------------------------
-- Table structure for cms_ad_locations
-- ----------------------------
CREATE TABLE `cms_ad_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告类型ID',
  `code` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '广告类型code',
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT '广告类型name',
  `num` int(11) NOT NULL COMMENT '广告位数量',
  `size` varchar(191) COLLATE utf8_unicode_ci NOT NULL COMMENT '推荐尺寸',
  `popup_img_size` varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '弹出广告推荐尺寸',
  `terminal_code` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '展示终端Code',
  `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否禁用 0：否 1：是',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_locations_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for cms_ad_operation_types
-- ----------------------------
CREATE TABLE `cms_ad_operation_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告业务类型ID',
  `code` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '广告业务类型code',
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT '广告业务类型name',
  `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否禁用 0：否 1：是',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_operation_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for cms_ads
-- ----------------------------
CREATE TABLE `cms_ads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告ID',
  `location_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '广告位类型Code',
  `media_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '广告媒体类型Code',
  `operation_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '广告业务类型Code',
  `operation_id` int(11) DEFAULT '0' COMMENT '广告来源ID 默认：0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '广告名称',
  `img_src` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片地址',
  `relatively_file_path` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片相对路径',
  `url_link` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '链接地址',
  `start_at` timestamp NOT NULL COMMENT '展示开始时间',
  `end_at` timestamp NOT NULL COMMENT '展示结束时间',
  `sort_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序序号',
  `disabled` tinyint(4) NOT NULL COMMENT '是否禁用 0：否 1：是',
  `creator_id` int(11) NOT NULL COMMENT '创建人',
  `updated_user_id` int(11) NOT NULL COMMENT '最后修改人',
  `need_popup` tinyint(4) DEFAULT NULL COMMENT '是否需要弹出框',
  `popup_poster_url` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '弹出海报地址（为空则与post_url相同处理)',
  `relatively_popup_file_path` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '弹出图片相对路径',
  `jump_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'common_web' COMMENT 'web: h5 webview; pdf: pdf viewer; video: 视频播放器; stream: 视频流; battle: 炒股大赛原生模块; broker: 券商开户原生模块...',
  `jump_params` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '提供给原生跳转使用的参数',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_forums
-- ----------------------------
CREATE TABLE `cms_forums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '论坛ID',
  `theme` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '论坛主题',
  `img_src` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片地址',
  `relatively_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片相对路径',
  `url_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '展示互动id',
  `url_link` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '展示互动播放url',
  `forum_at` timestamp NOT NULL COMMENT '论坛直播日期',
  `visible_at` timestamp NOT NULL COMMENT '论坛展示日期',
  `duration` int(11) NOT NULL COMMENT '论坛时长(min)',
  `teacher` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主讲嘉宾',
  `abstract` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '论坛简介',
  `creator_id` int(11) NOT NULL COMMENT '创建人',
  `updated_user_id` int(11) NOT NULL COMMENT '最后修改人',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cms_terminals
-- ----------------------------
CREATE TABLE `cms_terminals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '展示终端ID',
  `code` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '展示终端code',
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT '展示终端name',
  `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否禁用 0：否 1：是',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `terminals_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
