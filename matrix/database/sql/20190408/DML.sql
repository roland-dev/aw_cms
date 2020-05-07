INSERT INTO  `cms_openapi_permissions` (`code`, `name`, `request_method`, `uri`, `group_code`, `group_name`, `remark`, `active`, `created_at`, `updated_at`) VALUES ('order_teacher_list', '获取可选择老师列表', 'get',  'api/v2/openapi/teacher/select-list/{userGroupCode?}', 'order_teacher_group','订单选择老师','订单选择老师',1, now(), now());

INSERT INTO  `cms_openapi_permissions` (`code`, `name`, `request_method`, `uri`, `group_code`, `group_name`, `remark`, `active`, `created_at`, `updated_at`) VALUES ('order_teacher', '选择老师', 'post', 'api/v2/openapi/teacher/select/{enterpriseUserId}', 'order_teacher_group','订单选择老师','订单选择老师',1, now(), now());

 INSERT INTO `cms_openapi_guards` (`openapi_code`, `permission_code`, `active`, `created_at`, `updated_at`) VALUES ('9zHZ7R9w', 'order_teacher_list', 1, now(), now());

 INSERT INTO `cms_openapi_guards` (`openapi_code`, `permission_code`, `active`, `created_at`, `updated_at`) VALUES ('9zHZ7R9w', 'order_teacher', 1, now(), now());


