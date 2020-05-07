INSERT INTO `cms_openapi_permissions` (`code`, `name`, `request_method`, `uri`, `group_code`, `group_name`, `remark`, `active`, `created_at`, `updated_at`) VALUES ('kgs_create', '创建看高手信息', 'post', 'api/v2/openapi/kgs', 'kgs_group','看高手分组接口',  '看高手分组接口',1, now(), now());

INSERT INTO `cms_openapi_permissions` (`code`, `name`, `request_method`, `uri`, `group_code`, `group_name`, `remark`, `active`, `created_at`, `updated_at`) VALUES ('kgs_list', '看高手列表', 'post', 'api/v2/openapi/kgs/list', 'kgs_group','看高手分组接口',  '看高手分组接口',1, now(), now());

INSERT INTO `cms_openapi_permissions` (`code`, `name`, `request_method`, `uri`, `group_code`, `group_name`, `remark`, `active`, `created_at`, `updated_at`) VALUES ('kgs_record', '看高手详情', 'post', 'api/v2/openapi/kgs/record', 'kgs_group','看高手分组接口',  '看高手分组接口',1, now(), now());

INSERT INTO `cms_openapi_permissions` (`code`, `name`, `request_method`, `uri`, `group_code`, `group_name`, `remark`, `active`, `created_at`, `updated_at`) VALUES ('kgs_like', '看高手点赞', 'post', 'api/v2/openapi/kgs/like', 'kgs_group','看高手分组接口',  '看高手分组接口',1, now(), now());
