<?php

namespace Matrix\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

use Matrix\Contracts\PermissionManager;

use Matrix\Exceptions\PermissionException;

class PermissionProtect
{
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;

    private $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            abort(self::HTTP_UNAUTHORIZED, 'Permission Denied.');
        }
        switch ($request->route()->uri) {
            case 'user':
                $permissionCode = 'GET' === $request->method() ? '' : 'user';
                break;
            case 'user/sign-type/list':
                $permissionCode = ['user', 'permission'];
                break;
            case 'article':
                $permissionCode = 'article';
                break;
            case 'article/list':
                $permissionCode = 'article';
                break;
            case 'article/{articleId}':
                $permissionCode = 'article';
                break;
            case 'article/{articleId}/show':
                $permissionCode = 'article';
                break;
            case 'article/{articleId}/push':
                $permissionCode = 'article';
                break;
            case 'propaganda/forum':
                $permissionCode = 'forum';
                break;
            case 'propaganda/forum/{forumId}':
                $permissionCode = 'forum';
                break;
            case 'propaganda/forum/ads/{forumId}':
                $permissionCode = 'forum';
                break;
            case 'propaganda/ad':
                $permissionCode = 'ad';
                break;
            case 'propaganda/ad/{adId}':
                $permissionCode = 'ad';
                break;
            case 'propaganda/locations':
                $permissionCode = ['ad', 'forum'];
                break;
            case 'propaganda/terminal/list':
                $permissionCode = ['ad', 'forum'];
                break;
            case 'propaganda/media/list':
                $permissionCode = ['ad', 'forum'];
                break;
            case 'propaganda/operation/list':
                $permissionCode = ['ad', 'forum'];
                break;
            case 'propaganda/ad/create':
                $permissionCode = ['ad', 'forum'];
                break;
            case 'propaganda/packages':
                $permissionCode = ['ad', 'forum', 'dynamic_ad'];
                break;
            case 'propaganda/img/upload':
                $permissionCode = ['ad', 'forum'];
                break;
            case 'twitter/request/list':
                $permissionCode = 'twitter_follow';
                break;
            case 'twitter/request/{twitterGuardId}':
                $permissionCode = 'twitter_follow';
                break;
            case 'twitter/request/add':
                $permissionCode = 'twitter_follow';
                break;
            case 'twitter/request/paging-list':
                $permissionCode = 'twitter_follow';
                break;
            case 'private-message/request/list':
                $permissionCode = 'private_message_follow';
                break;
            case 'private-message/{privateMessageId}/read':
                $permissionCode = 'private_message_follow';
                break;
            case 'private-message/request/paging-list':
                $permissionCode = 'private_message_follow';
                break;
            case 'feed/elite/{feedId}':
                $permissionCode = 'feed_elite';
                break;
            case 'feed/bypass/{feedId}':
                $permissionCode = 'feed_elite';
                break;
            case 'feed/list':
                $permissionCode = 'feed_elite';
                break;
            case 'column/category/list':
                $permissionCode = ['category', 'discuss', 'static_talkshow', 'talkshow'];
                break;
            case '/column/category/paging-list':
                $permissionCode = 'category';
                break;
            case 'column/category/{categoryId}':
                $permissionCode = 'category';
                break;
            case 'column/category/service/list':
                $permissionCode = 'category';
                break;
            case 'column/category':
                $permissionCode = 'category';
                break;
            case 'column/category/checkcode/{categoryCode}':
                $permissionCode = 'category';
                break;
            case 'column/category/create':
                $permissionCode = 'category';
                break;
            case 'column/category/update/{categoryId}':
                $permissionCode = 'category';
                break;
            case 'column/category/upload/cover':
                $permissionCode = 'category';
                break;
            case 'column/category/teacher/list/{categoryCode}':
                $permissionCode = ['category', 'static_talkshow', 'talkshow'];
                break;
            case 'column/subcategory/list/{categoryCode}':
                $permissionCode = 'category';
                break;
            case 'column/subcategory/checkcode/{categoryCode}/{subCategoryCode}':
                $permissionCode = 'category';
                break;
            case 'column/subcategory/create':
                $permissionCode = 'category';
                break;
            case 'column/subcategory/{subCategoryId}':
                $permissionCode = 'category';
                break;
            case 'column/subcategory/update/{subCategoryId}':
                $permissionCode = 'category';
                break;
            case 'column/subcategory/delete/{subCategoryId}':
                $permissionCode = 'category';
                break;
            case 'column/subcategory/{subCategoryId}/active/{active}':
                $permissionCode = 'category';
                break;
            case 'column/category-group/list':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/checkcode/{categoryGroupCode}':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/create':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/{categoryGroupCode}':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/update':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/delete/{categoryGroupCode}':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/category/list':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/category/member':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/category/member/list':
                $permissionCode = 'category_group';
                break;
            case 'column/category-group/category/member/{categoryGroupId}':
                $permissionCode = 'category_group';
                break;
            case 'column/teacher/list':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher/paging-list':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher/user/list':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher/{teacherId}':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher/create':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher/update/{teacherId}':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher/{teacherId}/active/{active}':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher/upload/icon':
                $permissionCode = 'teacher';
                break;
            case 'column/teacher/upload/cover':
                $permissionCode = 'teacher';
                break;
            case 'stock/report/category/list':
                $permissionCode = 'stock_report';
                break;
            case 'stock/report/push-status/list':
                $permissionCode = 'stock_report';
                break;
            case 'stock/report/list':
                $permissionCode = 'stock_report';
                break;
            case 'stock/report':
                $permissionCode = 'stock_report';
                break;
            case 'stock/report/create':
                $permissionCode = 'stock_report';
                break;
            case 'stock/report/{id}':
                $permissionCode = 'stock_report';
                break;
            case 'stock/report/push/{id}':
                $permissionCode = 'stock_report';
                break;
            case 'stock/report/upload':
                $permissionCode = 'stock_report';
                break;
            case 'kit/buy-type/list':
                $permissionCode = 'kit';
                break;
            case 'kiy/buy-states/list':
                $permissionCode = 'kit';
                break;
            case 'kit/teacher/list':
                $permissionCode = 'kit';
                break;
            case 'kit/list':
                $permissionCode = 'kit';
                break;
            case 'kit':
                $permissionCode = 'kit';
                break;
            case 'kit/create':
                $permissionCode = 'kit';
                break;
            case 'kit/{id}':
                $permissionCode = 'kit';
                break;
            case 'kit/upload/cover':
                $permissionCode = 'kit';
                break;
            case 'kit/report/kit/list':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report/publish-status/list':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report/valid-status/list':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report/list':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report/create':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report/{id}':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report/upload/cover':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report/upload/file':
                $permissionCode = 'kit_report';
                break;
            case 'kit/report/push/{id}':
                $permissionCode = 'kit_report';
                break;
            case 'content/feed':
                $permissionCode = 'feed';
                break;
            case 'content/feed/type/list':
                $permissionCode = 'feed';
                break;
            default:
                $permissionCode = '';
                break;
        }

        // 合并相关权限项
        // 用户管理
        if (in_array($request->route()->uri, [
            'user/{userId}',
            'user/{userId}/active/{active}',
            'user/{userId}/selected/{selected}',
            'user/teacher-tab/list',
            'user/list'
        ])) {
            $permissionCode = 'user';
        }

        // 权限管理
        if (in_array($request->route()->uri, [
            'user/permission/{userId}',
            'user/grant',
            'user/grant/list'
        ])) {
            $permissionCode = 'permission';
        }

        // 用户组管理
        if (in_array($request->route()->uri, [
            'user-group/list',
            'user-group/{userGroupCode}',
            'user-group',
            'user-group/member',
            'user-group/member/{userGroupId}',
            'user/all/list',
            'user-group/member/{userGroup}/user_ids'
        ])) {
            $permissionCode = 'user_group';
        }

        // 视频管理
        if (in_array($request->route()->uri(), [
            'user/teacher/list',
            'resource/img',
            'resource/video',
            'resource/video/category',
            'resource/video/{videoId}'
        ])) {
            $permissionCode = 'video';
        }

        // 课程体系管理
        if (in_array($request->route()->uri(), [
            'resource/coursesystem',
            'resource/coursesystem/checkcode/{courseSystemCode}',
            'resource/coursesystem/{courseSystemId}',
            'resource/coursesystem/{courseSystemId}/{courseSystemCode}',
            'resource/coursesystem/all/list'
        ])) {
            $permissionCode = 'coursesystem';
        }

        // 课程管理
        if (in_array($request->route()->uri, [
            'resource/coursesystem/course',
            'resource/coursesystem/course/list',
            'resource/coursesystem/course/servicelist',
            'resource/coursesystem/course/{courseCode}',
            'resource/coursesystem/course/{courseId}/{courseSystemId}/{courseCode}',
            'resource/coursesystem/course/{courseId}/{courseCode}'
        ])) {
            $permissionCode = 'course';
        }


        if (in_array($request->route()->uri, [
            'operate/moveqr',
            'operate/moveqrgroup',
            'operate/moveqr/image',
            'operate/moveqr/{qrCode}',
            'operate/moveqrgroup/cache/{groupCode}',
            'operate/moveqrgroup/{groupCode}',
        ])) {
            $permissionCode = 'moveqr';
        }

        if (in_array($request->route()->uri, [
            'interaction/content-type/list',
            'interaction/reply/examine',
            'interaction/reply/batch-examine',
            'interaction/reply/list',
            'interaction/teacher/list',
        ])) {
            $permissionCode = 'reply';
        }

        if (in_array($request->route()->uri, [
            'openapi/customapp',
            'openapi/customapp/basic',
            'openapi/customapp/secret',
            'openapi/customapp/lock',
            'openapi/customapp/unlock',
            'openapi/customapp/list',
            'openapi/customapp/search',
            'openapi/customapp/detail/{code}',
            'openapi/customapp/paging-list',
        ])) {
            $permissionCode = 'openapicode';
        }

        if (in_array($request->route()->uri, [
            'openapi/permission',
            'openapi/permission/{code}',
            'openapi/customapp/paging-list',
        ])) {
            $permissionCode = 'openapipermission';
        }

        if (in_array($request->route()->uri, [
            'propaganda/dynamic/ad/source-type/list',
            'propaganda/dynamic/ad/terminal/list',
            'propaganda/dynamic/ad/create',
            'propaganda/dynamic/ad',
            'propaganda/dynamic/ad/list',
            'propaganda/dynamic/ad/{dynamicAdId}/active/{active}',
            'propaganda/dynamic/ad/{dynamicAdId}/sign/{sign}',
            'propaganda/dynamic/ad/{dynamicAdId}',
        ])) {
            $permissionCode = 'dynamic_ad';
        }

        if (!empty($permissionCode)) {
            try {
                $this->permissionManager->checkPermission(Auth::id(), $permissionCode);
            } catch (PermissionException $e) {
                abort(self::HTTP_FORBIDDEN, $e->getMessage());
            }
        }
        return $next($request);
    }
}
