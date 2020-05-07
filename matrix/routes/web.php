<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('promotion')->group(function () {
    Route::get('moveqr/{qrGroupCode}/{report?}', 'PromotionController@moveQrNew');
    Route::get('moveqr/{report?}', 'PromotionController@moveQr');
	Route::get('{commissionCode}/moveqr', 'PromotionController@commissionMoveQr');
});

Route::prefix('operate')->group(function () {
    Route::prefix('moveqrgroup')->group(function () {
        Route::delete('cache/{groupCode}', 'OperateController@cacheClear');
        Route::get('/', 'OperateController@getMoveQrGroupList');
        Route::put('{groupCode}', 'OperateController@updateMoveQrGroup');
        Route::post('/', 'OperateController@createMoveQrGroup');
        Route::delete('{groupCode}', 'OperateController@removeMoveQrGroup');
    });
    Route::prefix('moveqr')->group(function () {
        Route::post('image', 'OperateController@uploadImage');
        Route::put('{qrCode}', 'OperateController@updateMoveQr');
        Route::post('/', 'OperateController@createMoveQr');
        Route::delete('{qrCode}', 'OperateController@removeMoveQr');
    });
});

Route::group(['middleware' => ['permission']], function () {
    Route::prefix('openapi')->group(function () {
        Route::prefix('customapp')->group(function () {
            Route::post('/', 'OpenApiController@create');
            Route::put('/basic', 'OpenApiController@updateBasicInfo');
            Route::put('/secret', 'OpenApiController@updateSecret');
            Route::put('/lock', 'OpenApiController@lock');
            Route::put('/unlock', 'OpenApiController@unLock');
            Route::get('/list', 'OpenApiController@getCustomAppList');

            // 管理端分页 20200107
            Route::get('/paging-list', 'OpenApiController@getCustomAppListOfPaging');

            Route::get('/search', 'OpenApiController@getCustomAppList');
            Route::get('/detail/{code}', 'OpenApiController@getCustomAppInfo');
        });
        Route::prefix('permission')->group(function () {
            Route::get('/', 'OpenApiController@getPermissionInfo');
            Route::get('/{code}', 'OpenApiController@getCodePermission');
            Route::post('/', 'OpenApiController@grant');
        });
    });
});

Route::group(['middleware' => 'permission'], function () {
    Route::prefix('video/vendor')->group(function () {
        Route::get('list', 'VideoVendorController@getVideoVendorList');
        Route::post('{vendorCode}', 'VideoVendorController@createVideoVendor');
        Route::put('{vendorCode}', 'VideoVendorController@updateVideoVendor');
        Route::delete('{vendorCode}', 'VideoVendorController@removeVideoVendor');
        Route::get('{vendorCode}', 'VideoVendorController@getVideoVendor');
    });

    Route::prefix('live/room')->group(function () {
        Route::get('list', 'LiveRoomController@getLiveRoomList');
        Route::post('/', 'LiveRoomController@createLiveRoom');
        Route::put('{roomCode}', 'LiveRoomController@updateLiveRoom');
        Route::delete('{roomCode}', 'LiveRoomController@removeLiveRoom');
        Route::get('{roomCode}', 'LiveRoomController@getLiveRoom');
    });

    Route::prefix('live/static-talkshow')->group(function () {
        Route::get('list', 'TalkshowController@getStaticTalkshowList');
        Route::post('/', 'TalkshowController@createStaticTalkshow');
        Route::put('{staticTalkshowId}', 'TalkshowController@updateStaticTalkshow');
        Route::delete('{staticTalkshowId}', 'TalkshowController@removeStaticTalkshow');
        Route::get('{staticTalkshowId}', 'TalkshowController@getStaticTalkshow');
        Route::prefix('upload')->group(function () {
            Route::post('/banner', 'TalkshowController@uploadBannerImage');
        });
    });

    Route::prefix('live/talkshow')->group(function () {
        Route::get('list', 'TalkshowController@getTalkshowList');
        Route::post('list', 'TalkshowController@importTalkshowList');
        Route::post('/', 'TalkshowController@createTalkshow');
        Route::put('{talkshowCode}', 'TalkshowController@updateTalkshow');
        Route::patch('{talkshowCode}', 'TalkshowController@operateTalkshow');
        Route::delete('{talkshowCode}', 'TalkshowController@removeTalkshow');
        Route::get('{talkshowCode}', 'TalkshowController@getTalkshow');
        Route::prefix('upload')->group(function () {
            Route::post('/banner', 'TalkshowController@uploadBannerImage');
        });
    });

    Route::prefix('live/discuss')->group(function () {
        Route::put('batch-examine', 'DiscussController@batchExamine');
        Route::get('list', 'DiscussController@getDiscussList');
        Route::post('/', 'DiscussController@createDiscuss');
        Route::put('{discussId}/examine', 'DiscussController@examine');
    });
});


# /user
Route::prefix('user')->group(function () {
#   GET /user/logout
    Route::get('logout', 'UserController@logout');

#   /user/auth
    Route::prefix('auth')->group(function () {
#       /user/auth/uc
        Route::prefix('uc')->group(function () {
#           GET /user/auth/uc
            Route::get('/', 'UserController@ucEnterpriseLoginCallback');
#           GET /user/auth/uc/enterprise
            Route::get('enterprise', 'UserController@getUcEnterpriseLoginUrl');
        });
    });

    Route::group(['middleware' => 'permission'], function () {
#       PUT /user/{userId}/active/{active}
        Route::put('/{userId}/active/{active}', 'UserController@activeUser');
#       PUT /user/{userId}/selected/{selected}
        Route::put('/{userId}/selected/{selected}', 'UserController@selectedUser');
#       GET /user/teacher-tab/list
        Route::get('/teacher-tab/list', 'UserController@getTeacherTabList');
#       GRT /user/sign-type/list
        Route::get('/sign-type/list', 'UserController@getSignTypeList');

#       GET /user/list
        Route::get('list', 'UserController@getUserList');
#       GET /user/all/list
        Route::get('all/list', 'UserController@getAllUserList');

#       /user/list/permission
        Route::prefix('permission')->group(function () {
#           GET /user/list/permission
            Route::get('/', 'PermissionController@show');
#           GET /user/list/permission/{userId}
            Route::get('/{userId}', 'PermissionController@show');
        });
#       /user/grant
        Route::prefix('grant')->group(function(){
#           GET /user/grant/list
            Route::get('list', 'GrantController@getMoreUserGrantedList');
#           POST /user/grant
            Route::post('/', 'GrantController@grant'); 
        });

#       GET /user/teacher/list
	    Route::get('/teacher/list', 'UserController@getTeacherList');

#       GET /user/videoauthor/list
	    Route::get('/videoauthor/list', 'UserController@getVideoAuthorList');

#       GET /user
        Route::get('/', 'UserController@getUserInfo');
#       GET /user/{userId}
        Route::get('/{userId}', 'UserController@getUserInfo');
#       POST /user
        Route::post('/', 'UserController@create');
#       PUT /user/{userId}
        Route::put('/{userId}', 'UserController@update');
#       POST /user/icon/upload
        Route::post('/icon/upload', 'UserController@uploadIcon');
    });
});

Route::prefix('user-group')->group(function () {
    Route::group(['middleware' => ['auth', 'permission']], function () {
#       GET /user-group/list
        Route::get('list', 'UserGroupController@getUserGroupList');
#       GET /user-group/{userGroupCode}
        Route::get('/{userGroupCode}', 'UserGroupController@getUserListByUserGroupCode');
#       POST /user
        Route::post('/', 'UserGroupController@create');
#       PUT /user
        Route::put('/', 'UserGroupController@update');
#       DELETE
        Route::delete('/{userGroupCode}', 'UserGroupController@remove');

        Route::prefix('member')->group(function () {
            Route::get('/{userGroup}/user_ids', 'UserGroupController@getUserIdListOfUserGroupCode');

            Route::post('/', 'UserGroupController@createUserGroupMember');

            Route::get('/{userGroupId}', 'UserGroupController@getUserGroupMember')->where('userGroupId', '[0-9]+');

            Route::put('/{userGroupId}', 'UserGroupController@updateUserGroupMember')->where('userGroupId', '[0-9]+');

            Route::delete('/{userGroupId}', 'UserGroupController@deleteUserGroupMember')->where('userGroupId', '[0-9]+');
        });
    });
});

Route::prefix('resource')->group(function () {
    Route::group(['middleware' => ['auth', 'permission']], function () {
        Route::prefix('coursesystem')->group(function(){
            Route::post('/', 'CourseSystemController@create');
            Route::put('/', 'CourseSystemController@update');
            Route::get('/', 'CourseSystemController@show');

#           /resource/coursesystem/all/list
            Route::get('/all/list', 'CourseSystemController@getAllCourseSystemList');
            Route::get('/categorylist', 'CourseSystemController@getCategoryList');
            Route::get('checkcode/{courseSystemCode}', 'CourseSystemController@checkCourseSystemCodeUnique');
            Route::get('/{courseSystemId}', 'CourseSystemController@getOneInfo');
            Route::post('/sequence', 'CourseSystemController@courseSystemOrder');
            Route::delete('/{courseSystemId}/{courseSystemCode}', 'CourseSystemController@remove');
            Route::prefix('course')->group(function(){
                Route::post('/', 'CourseController@create');
                Route::post('/sequence', 'CourseController@courseOrder');
                Route::put('/', 'CourseController@update');
                Route::get('list', 'CourseController@show');
                Route::get('/servicelist', 'CourseController@getServiceList');
                Route::get('/{courseCode}', 'CourseController@checkCourseCodeUnique');
                Route::get('/{courseId}/{courseSystemId}/{courseCode}', 'CourseController@getOneInfo');
                Route::patch('/', 'CourseController@search');
                Route::delete('/{courseId}/{courseCode}', 'CourseController@remove');
                Route::prefix('video')->group(function(){
                    Route::post('/', 'CourseVideoController@create');
                    Route::post('/sequence', 'CourseVideoController@courseVideoOrder');
                    Route::put('/', 'CourseVideoController@update');
                    Route::delete('/{videoId}/{courseVideoId}', 'CourseVideoController@remove');
                    Route::get('onecourse/{videoId}/{courseVideoId}', 'CourseVideoController@getCourseVideoInfo');
                    Route::get('list/onecourse/{courseCode}', 'CourseVideoController@getCourseVideoList');
                });
                Route::prefix('image')->group(function(){
                    Route::post('/', 'CourseVideoController@upload');
                    Route::post('imagepath', 'CourseVideoController@removeImage');
                });
            });
            Route::prefix('service')->group(function(){
                Route::get('list', 'BossController@getServiceList'); 
            });
        });
    });
    Route::group(['middleware' => 'auth'], function () {
        Route::prefix('video')->group(function(){
            Route::prefix('category')->group(function(){
                Route::get('/', 'VideoController@getCategoriesList');
                Route::post('/', 'VideoController@catsToTchs');
            });
            Route::get('/', 'VideoController@show');
            Route::post('/', 'VideoController@create');
            Route::put('/', 'VideoController@update');
            Route::delete('/{videoId}', 'VideoController@destory');
            Route::get('/{videoId}', 'VideoController@detail');
            Route::patch('/', 'VideoController@search');
        });
        Route::prefix('img')->group(function(){
            Route::post('/', 'VideoController@generateQrCode');
        });
        Route::prefix('image')->group(function () {
            Route::post('/', 'ResourceController@createImage');
        });
    });
});

Route::group(['middleware' => 'auth'], function () {
    Route::prefix('category')->group(function () {
        Route::get('list', 'CategoryController@getCategoryList');
        Route::get('mylist', 'CategoryController@getMyCategoryList');
        Route::get('qywxlist', 'CategoryController@getCategoryOfPushQywx');
        Route::prefix('{categoryCode}')->group(function () {
            Route::get('teacherlist', 'CategoryController@getCategoryTeacherList');
            Route::get('subcategory/list', 'CategoryController@getSubCategoryListByCategoryCode');
        });
    });
});

Route::group(['middleware' => ['auth', 'permission']], function () {
    Route::prefix('column')->group(function () {
        Route::prefix('category')->group(function () {
            Route::get('/list', 'CategoryController@search');

            // 管理端分页 20191210
            Route::get('/paging-list', 'CategoryController@getCategoryListOfPaging');

            Route::get('/{categoryId}', 'CategoryController@getCategoryInfoByCategoryId')->where('categoryId', '[0-9]+');
            Route::get('/service/list', 'CategoryController@getServiceList');
            Route::patch('/', 'CategoryController@getCategoryListOfPaging');
            Route::get('/checkcode/{categoryCode}', 'CategoryController@checkCategoryCodeUnique');
            Route::post('/create', 'CategoryController@create');
            Route::put('/update/{categoryId}', 'CategoryController@update')->where('categoryId', '[0-9]+');
            Route::get('/teacher/list/{categoryCode}', 'CategoryController@getTeacherList');
            Route::prefix('upload')->group(function () {
                Route::post('/cover', 'CategoryController@uploadCoverImage');
                Route::post('/adCover', 'CategoryController@uploadAdCoverImage');
            });
        });
        Route::prefix('subcategory')->group(function () {
            Route::get('/list/{categoryCode}', 'SubCategoryController@getSubCategoryList');
            Route::get('/checkcode/{categoryCode}/{subCategoryCode}', 'SubCategoryController@checkSubCategoryCodeUnique');
            Route::post('/create', 'SubCategoryController@create');
            Route::get('/{subCategoryId}', 'SubCategoryController@getSubCategoryInfoBySubCategoryId')->where('subCategoryId', '[0-9]+');
            Route::put('/update/{subCategoryId}', 'SubCategoryController@update')->where('subCategoryId', '[0-9]+');
            Route::delete('/delete/{subCategoryId}', 'SubCategoryController@delete')->where('subCategoryId', '[0-9]+');
#           PUT /subcategory/{subCategoryId}/active/{active}
            Route::put('/{subCategoryId}/active/{active}', 'SubCategoryController@activeSubCategory');
        });
        Route::prefix('category-group')->group(function () {
            Route::get('/list', 'CategoryGroupController@getCategoryGroupList');
            Route::get('/checkcode/{categoryGroupCode}', 'CategoryGroupController@checkCategoryGroupCodeUnique');
            Route::post('/create', 'CategoryGroupController@create');
            Route::get('/{categoryGroupCode}', 'CategoryGroupController@getCategoryGroupInfo');
            Route::put('/update', 'CategoryGroupController@update');
            Route::delete('/delete/{categoryGroupCode}', 'CategoryGroupController@delete');
            Route::get('/category/list', 'CategoryGroupController@getCategoryList');

            Route::prefix('member')->group(function () {
                Route::get('/list', 'CategoryGroupController@getCategoryGroupMemberList');

                Route::post('/', 'CategoryGroupController@createCategoryGroupMember');

                Route::get('/{categoryGroupId}', 'CategoryGroupController@getCategoryGroupMember')->where('categoryGroupId', '[0-9]+');

                Route::put('/{categoryGroupId}', 'CategoryGroupController@updateCategoryGroupMember')->where('categoryGroupId', '[0-9]+');

                Route::delete('/{categoryGroupId}', 'CategoryGroupController@deleteCategoryGroupMember')->where('categoryGroupId', '[0-9]+');
            });
        });
        Route::prefix('teacher')->group(function () {
            Route::get('/list', 'TeacherController@search');
            Route::patch('/', 'TeacherController@search');

            // 优化 前端分页
            Route::get('/paging-list', 'TeacherController@getTeacherListOfPaging');

            Route::patch('/user/list', 'TeacherController@getUserList');
            Route::get('/{teacherId}', 'TeacherController@getTeacherInfo')->where('teacherId', '[0-9]+');
            Route::post('/create', 'TeacherController@create');
            Route::put('/update/{teacherId}', 'TeacherController@update')->where('teacherId', '[0-9]+');
#           PUT /teacher/{teacherId}/active/{active}
            Route::put('/{teacherId}/active/{active}', 'TeacherController@activeTeacher');
            Route::prefix('upload')->group(function () {
                Route::post('/icon', 'TeacherController@uploadIconImage');
                Route::post('/cover', 'TeacherController@uploadCoverImage');
            });
        });
    });

    Route::prefix('stock/report')->group(function () {
        #报告类型
        Route::get('/category/list', 'StockReportController@getReportCategories');

        #报告推送状态类型
        Route::get('/publish-status/list', 'StockReportController@getPublishStatus');

        Route::get('/list', 'StockReportController@search');
        Route::patch('/', 'StockReportController@search');
        Route::post('/create', 'StockReportController@create');
        Route::get('/{id}', 'StockReportController@getStockReportInfo')->where('id', '[0-9]+');
        Route::put('/{id}', 'StockReportController@update')->where('id', '[0-9]+');
        Route::delete('/{id}', 'StockReportController@delete')->where('id', '[0-9]+');
        Route::get('/push/{id}', 'StockReportController@publish')->where('id', '[0-9]+');
        Route::post('/upload', 'StockReportController@upload');
    });
    
});

Route::group(['middleware' => 'permission'], function () {
    Route::prefix('article')->group(function () {
        Route::post('/', 'ArticleController@createArticle');
        Route::post('/preview', 'ArticleController@previewArticle');
        Route::get('list', 'ArticleController@getArticleList');
        Route::get('/{articleId}', 'ArticleController@getArticleInfo');
        Route::put('/{articleId}/show', 'ArticleController@updateArticleShow');
        Route::put('/{articleId}/push', 'ArticleController@updateArticlePushQywx');
        Route::put('/{articleId}', 'ArticleController@updateArticle');
        Route::delete('/{articleId}', 'ArticleController@trashArticle');
    });

    Route::prefix('twitter')->group(function () {
        Route::prefix('request')->group(function () {
            Route::put('{twitterGuardId}', 'TwitterController@processTwitterRequest');
            Route::get('list', 'TwitterController@getTwitterRequestList');
            Route::post('add', 'TwitterController@addTwitterRequest');

            Route::get('paging-list', 'TwitterController@getTwitterRequestListOfPaging');
        });
        Route::post('/', 'TwitterController@postTwitter');
        Route::get('list', 'TwitterController@getTwitterList');
    });

    Route::prefix('private-message')->group(function () {
        Route::prefix('request')->group(function () {
            Route::put('{privateMessageGuardId}', 'TwitterController@processPrivateMessageRequest');
            Route::get('list', 'TwitterController@getPrivateMessageRequestList');

            Route::get('paging-list', 'TwitterController@getPrivateMessageRequestListOfPaging');
        });
        Route::post('/', 'TwitterController@postPrivateMessage');
        Route::put('/{privateMessageId}/read', 'TwitterController@readPrivateMessage');
        Route::get('list', 'TwitterController@getPrivateMessageList');
        Route::prefix('session')->group(function () {
            Route::get('list', 'TwitterController@getSessionList');
        });
    });
});

Route::prefix('system-notice')->group(function () {
    Route::get('list', 'SystemNoticeController@getSystemNoticeList');
    Route::put('/{systemNoticeId}/read', 'SystemNoticeController@putRead');
});

Route::prefix('customer')->group(function () {
    Route::get('{openId}', 'CustomerController@getCustomerInfo');
    Route::get('card/{openId}', 'CustomerController@showInfoCard');
    Route::get('mobile/{mobile}', 'CustomerController@getCustomerInfoByMobile')->where('mobile', '[0-9]+');
});

Route::prefix('category-group')->group(function () {
    Route::get('{categoryGroupCode}', 'CategoryController@getCategoryListByGroupCode');
});

#Route::group(['middleware' => 'auth'], function () {
#    Route::prefix('log')->group(function(){
#        Route::get('/', 'LogController@show'); 
#    });
#});
Route::prefix('propaganda')->group(function() {
    Route::group(['middleware' => ['auth', 'permission']], function () {
        Route::get('/packages', 'AdController@getPackages');
        Route::get('/terminal/list', 'AdController@getAdTerminals');
        Route::get('/{locationCode}/terminal/list', 'AdController@getAdTerminalsOfLocationCode');
        Route::get('/media/list', 'AdController@getMediaTypes');
        Route::get('/operation/list', 'AdController@getOperationTypes');
        Route::get('/teacher/list', 'ForumController@getTeachers');
        Route::get('/locations', 'AdController@getAdLocations');
        Route::prefix('ad')->group(function () {
            Route::post('/create', 'AdController@create');
            Route::get('/', 'AdController@search');
            Route::patch('/', 'AdController@search');
            Route::put('/', 'AdController@update');
            Route::delete('/{adId}', 'AdController@destory')->where('adId', '[0-9]+');
            Route::get('/{adId}', 'AdController@detail')->where('adId', '[0-9]+');
        });
        Route::prefix('forum')->group(function () {
            Route::get('/', 'ForumController@search');
            Route::post('/', 'ForumController@create');
            Route::patch('/', 'ForumController@search');
            Route::put('/', 'ForumController@update');
            Route::delete('/{forumId}', 'ForumController@destory')->where('forumId', '[0-9]+');
            Route::get('/{forumId}', 'ForumController@detail')->where('forumId', '[0-9]+');
            Route::get('/ads/{forumId}', 'ForumController@getAdListDataOfForumId')->where('forumId', '[0-9]+');
        });
        // 跑马灯管理
        Route::prefix('dynamic/ad')->group(function () {
            Route::get('/source-type/list', 'DynamicAdController@getSourceTypes');
            Route::get('/terminal/list', 'DynamicAdController@getDynamicAdTerminals');

            Route::post('/create', 'DynamicAdController@create');
            Route::patch('/', 'DynamicAdController@search');
            Route::get('/list', 'DynamicAdController@search');
            Route::put('/{dynamicAdId}/active/{active}', 'DynamicAdController@changeActiveStatus')->where('dynamicAdId', '[0-9]+');
            Route::put('/{dynamicAdId}/sign/{sign}', 'DynamicAdController@changeSignStatus')->where('dynamicAdId', '[0-9]+');
            Route::get('/{dynamicAdId}', 'DynamicAdController@getDynamicAdInfo')->where('dynamicAdId', '[0-9]+');
            Route::put('/{dynamicAdId}', 'DynamicAdController@update')->where('dynamicAdId', '[0-9]+');
            Route::delete('/{dynamicAdId}', 'DynamicAdController@deltete')->where('dynamicAdId', '[0-9]+');
        });

        Route::prefix('img')->group(function () {
            Route::post('/upload', 'AdController@uploadImage');
        });
    });
});

Route::prefix('feed')->group(function () {
    Route::group(['middleware' => ['auth', 'permission']], function () {
        Route::get('list', 'FeedController@getFeedList');
        Route::put('/elite/{feedId}', 'FeedController@eliteFeed');
        Route::delete('/{feedId}/{delOriginal}', 'FeedController@deleteRecord');
        Route::put('/bypass/{feedId}', 'FeedController@bypassFeed');
    });
});

Route::prefix('interaction')->group(function () {
    Route::group(['middleware' => ['auth', 'permission']], function () {
        Route::prefix('teacher')->group(function () {
            Route::get('list', 'InteractionController@getTeacherList');
        });
        Route::prefix('content-type')->group(function () {
            Route::get('list', 'InteractionController@getContentTypeList');
        });
        Route::prefix('reply')->group(function () {
            Route::post('/', 'InteractionController@postReply');
            Route::get('list', 'InteractionController@getReplyList');
            Route::put('examine', 'InteractionController@examineReply');
            Route::put('batch-examine', 'InteractionController@batchExamineReply');
        });
    });
});

Route::prefix('content/feed')->group(function () {
    Route::group(['middleware' => ['auth', 'permission']], function () {
        Route::patch('/', 'FeedController@getFeedListOfDate');
        Route::get('/type/list', 'FeedController@getFeedTypeList');
    });
});

Route::prefix('kit')->group(function () {
    Route::group(['middleware' => ['auth', 'permission']], function () {
        Route::get('/buy-type/list', 'KitController@getBuyTypes');
        Route::get('/buy-states/list', 'KitController@getBuyStates');
        Route::get('/teacher/list', 'KitController@getTeacherList');

        Route::get('/list', 'KitController@search');
        Route::patch('/', 'KitController@search');
        Route::post('/create', 'KitController@create');
        Route::get('/{id}', 'KitController@getKitInfo')->where('id', '[0-9]+');
        Route::put('/{id}', 'KitController@update')->where('id', '[0-9]+');
        Route::delete('/{id}', 'KitController@delete')->where('id', '[0-9]+');
        Route::post('/upload/cover', 'KitController@uploadCoverImg');

        Route::prefix('report')->group(function () {
            Route::get('/kit/list', 'KitReportController@getKits');
            Route::get('/publish-status/list', 'KitReportController@getPublishStatus');
            Route::get('/valid-status/list', 'KitReportController@getValidStatus');

            Route::get('/list', 'KitReportController@search');
            Route::patch('/', 'KitReportController@search');
            Route::post('/create', 'KitReportController@create');
            Route::get('/{id}', 'KitReportController@getKitReportInfo')->where('id', '[0-9]+');
            Route::put('/{id}', 'KitReportController@update')->where('id', '[0-9]+');
            Route::delete('/{id}', 'KitReportController@delete')->where('id', '[0-9]+');
            Route::post('/upload/cover', 'KitReportController@uploadCover');
            Route::post('/upload/file', 'KitReportController@uploadFile');

            Route::get('/push/{id}', 'KitReportController@publish')->where('id', '[0-9]+');
        });
    });
});

