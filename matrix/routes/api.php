<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v2')->group(function () {
    Route::prefix('openapi')->group(function () {
        Route::post('token', 'OpenApi\CustomAppController@applyToken');
        Route::group(['middleware' => ['openapi']], function () {
            Route::prefix('teacher')->group(function () {
                Route::get('select-list/{userGroupCode?}', 'OpenApi\TeacherController@getTeacherListForSelect');
                Route::post('select/{enterpriseUserId}', 'OpenApi\TeacherController@selectTeacher');
            });
            Route::prefix('kgs')->group( function () {
                Route::post('/', 'OpenApi\KgsController@createKgs');
                Route::get('/list', 'OpenApi\KgsController@getKgsList');
                Route::get('/record', 'OpenApi\KgsController@getKgsInfo');
                Route::post('/like', 'OpenApi\KgsController@like');
            });
        });
    });

    Route::group(['middleware' => 'sessionverify'], function () {
        Route::get('articles/course/xuezhanfa/{courseSystemCode}/{courseCode?}', 'CourseController@apiGetXueZhanFaList');
    });
    Route::get('articles/coursevideo/detail/{videoKey}', 'CourseVideoController@apiGetCourseVideoDetail');

    Route::prefix('article')->group(function () {
        Route::prefix('{categoryCode}')->group(function () {
            Route::get('list', 'Api\ArticleApiController@getHsggListData');
        });
        Route::prefix('{articleId}')->group(function () {
            Route::get('/', 'Api\ArticleApiController@getArticleInfo');
            Route::post('read', 'Api\ArticleApiController@read');
            Route::post('like', 'Api\ArticleApiController@like');
        });
    });

    Route::prefix('category')->group(function () {
        Route::prefix('{categoryCode}')->group(function () {
            Route::get('info', 'Api\CategoryApiController@getCategoryInfo');
        });
    });

    Route::prefix('customer')->group(function () {
        Route::post('login', 'Api\CustomerApiController@login');
        Route::post('logout', 'Api\CustomerApiController@logout');
    });

    Route::prefix('twitter')->group(function () {
        Route::post('request', 'Api\TwitterApiController@requestTwitter');
        Route::get('list', 'Api\TwitterApiController@getTwitterList');
        Route::prefix('{twitterId}')->group(function () {
            Route::post('like', 'Api\TwitterApiController@like');
        });
    });

    Route::prefix('private-message')->group(function () {
        Route::get('list', 'Api\TwitterApiController@getPrivateMessageList');
        Route::get('request', 'Api\TwitterApiController@getLastPrivateMessageGuard');
        Route::post('request', 'Api\TwitterApiController@requestPrivateMessage');
        Route::post('/', 'Api\TwitterApiController@postPrivateMessage');
        Route::put('/{privateMessageId}/read', 'Api\TwitterApiController@readPrivateMessage');
    });

    Route::prefix('system-notice')->group(function () {
        Route::get('list', 'Api\SystemNoticeApiController@getSystemNoticeList');
        Route::put('/{systemNoticeId}/read', 'Api\SystemNoticeApiController@putRead');
    });

    Route::prefix('category-group')->group(function () {
        Route::get('{categoryGroupCode}', 'Api\CategoryApiController@getCategoryListByGroupCode');
    });

    Route::prefix('resource')->group(function () {
        Route::prefix('image')->group(function () {
            Route::post('/', 'Api\ResourceController@createImage');
        });
    });
    Route::prefix('ad')->group(function () {
        Route::get('/', 'Api\AdApiController@getAdListByLocationCodes');
        Route::get('{locationCode}', 'Api\AdApiController@getAdList');
    });
    
    Route::get('/forum/{forumId}', 'Api\ForumApiController@getForumDetail');

    Route::group(['middleware' => 'cookiejudge'], function () {
        Route::prefix('forum')->group(function () {
            Route::get('/', 'Api\ForumApiController@getForumList');
        });
    });

    Route::get('/stock/report', 'Api\StockReportApiController@getStockReportListByDate');

    Route::get('/token/vhall/sign', 'Api\TokenApiController@getVhallSign');

    Route::get('/talkshow/predict', 'Api\TalkshowApiController@getPredictInfo');
});

Route::prefix('v1')->group(function () {
    Route::group(['middleware' => 'compatibleinterfacecookiejudge'], function () {
        Route::prefix('/feeds/ad')->group(function () {
            Route::get('/', 'Api\AdApiController@getAdListOfCompatible');
        });
    });
});

Route::prefix('v1.5')->group(function () {
    Route::get('/stocks/cyzb/report/{stock_code}/{category_id}', 'Api\StockReportApiController@getStockReportList');
});
