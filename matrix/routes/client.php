<?php

use Illuminate\Http\Request;

Route::get('teacher', 'Client\TeacherController@getTeacherInfo');
Route::get('user-group/{userGroupCode}', 'Client\TeacherController@getTeacherList');
Route::prefix('twitter')->group(function () {
    Route::get('list', 'Client\TwitterController@getTwitterList');
    Route::get('{twitterId}', 'Client\TwitterController@getTwitterInfo');
});
Route::put('professor/follow', 'Client\TeacherController@putFollow');
Route::prefix('course')->group(function () {
    Route::get('list', 'Client\CourseController@getCourseList');
    Route::get('list/{course_code}', 'Client\CourseController@getOneCourseVideoList');
    Route::get('detail/{video_key}', 'Client\CourseController@getCourseVideoDetail');
    Route::get('{courseCode}', 'Client\CourseController@getCourseInfo');
    Route::get('/{courseCode}/description', 'Client\CourseController@getCourseDescription');
});
Route::get('article/list', 'Client\ArticleController@getArticleList');
Route::get('article/test', 'Client\ArticleController@Test');
Route::get('article/{article_id}', 'Client\ArticleController@getArticleInfo');
Route::get('professor/follow-count', 'Client\TeacherController@getUserFollowCount');
Route::prefix('ad')->group(function () {
    Route::get('/', 'Client\AdController@getAdListByLocationCodes');
    Route::get('{locationCode}', 'Client\AdController@getAdList');
});
Route::get('/special/ad', 'Client\AdController@getAdListBySpecialLocationCodes');

Route::get('news', 'Client\NewsController@getNewInfo');

Route::get('talkshow/predict', 'Client\TalkshowController@getPredictInfo');
Route::get('talkshow/today-list', 'Client\TalkshowController@getTodayTalkshowList');

Route::prefix('talkshow')->group(function () {
    Route::get('/{video_key}', 'Client\TalkshowController@getVideoDetail');
});

Route::get('/rechristen', 'Client\NicknameController@nicknameBlade');
Route::get('/cache/{session_id}', 'Client\NicknameController@forgetCache');

Route::get('articles/article/{detailId?}', 'Client\ContentController@getContentInfo');
Route::get('history', 'Client\HistoryController@getHistoryData');

Route::get('stock_report/{report_id}', 'Client\StockReportController@getStockReportInfo');
Route::get('stock/report', 'Client\StockReportController@getStockReportListByDate');

Route::get('stocks/cyzb/report/{stock_code}/{category_id}', 'Client\StockReportController@getStockReportList');

Route::get('kit_report/{report_id}', 'Client\KitReportController@getKitReportInfo');
Route::get('kit/report', 'Client\KitReportController@getkits');
Route::get('kit/{kit_code}', 'Client\KitReportController@getKitInfo');

Route::get('kit/detail/{kit_code}', 'Client\KitReportController@getKitDetailInfo');

Route::get('kgs/twitter', 'Client\ContentController@getKgsTwitterInfo');
Route::get('reply/list', 'Client\ContentReplyController@getReplyList');
Route::get('reply/examine', 'Client\ContentReplyController@examineReply');

Route::get('content/reply', 'Client\ContentController@getContentInfoByReplyId');
// ZYAPP-841
Route::get('/select/video/list', 'Client\VideoController@getAllVideoList');

Route::get('dynamic/ad', 'Client\DynamicAdController@getDynamicAdList');

// 直播接口详情页
Route::get('/live/talkshow/{talkshow_code}', 'Client\TalkshowController@getLiveTalkshowInfo');

// 微吼视频参数
Route::get('/params', 'Client\ConfigController@getVhallParams');

