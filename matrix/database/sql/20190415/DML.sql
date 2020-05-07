-- Fix a bug for teacher selected
UPDATE `cms_openapi_permissions` SET `request_method` = 'get' where id = 1;
UPDATE `cms_openapi_permissions` SET `request_method` = 'post' where id = 2;

