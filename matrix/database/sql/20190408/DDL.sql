CREATE TABLE `cms_openapi_guards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `openapi_code` varchar(64)   NOT NULL COMMENT '第三方code',
  `permission_code` varchar(64)   NOT NULL COMMENT '权限code',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cms_openapi_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `code` varchar(64)   NOT NULL COMMENT '权限code值',
  `name` varchar(64)   NOT NULL COMMENT '权限名',
  `request_method` varchar(64)   NOT NULL COMMENT '请求方式',
  `uri` varchar(191)   NOT NULL COMMENT '客户点赞总数',
  `group_code` varchar(64)   NOT NULL COMMENT '分组code',
  `group_name` varchar(64)    NOT NULL COMMENT '分组名称',
  `remark` varchar(191)   DEFAULT NULL COMMENT '备注',
  `active` int(10) NOT NULL DEFAULT '1' COMMENT '是否删除：0 是 1 否',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
