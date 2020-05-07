<?php

use Illuminate\Http\Request;

Route::put('vote', 'Interaction\ContentVoteController@putVote');
Route::put('vote/ugc', 'Interaction\ContentVoteController@ugcPutVote');
Route::get('vote/statistic/{type}/{article_id}/{udid}', 'Interaction\ContentVoteController@likeStatistic');
Route::get('reply/list', 'Interaction\ContentReplyController@getReplyList');
Route::get('reply/newlist', 'Interaction\ContentReplyController@getReplyNewList');
Route::post('reply', 'Interaction\ContentReplyController@postReply');
Route::get('reply/place', 'Interaction\ContentReplyController@topReplace');

Route::post('forward/twitter', 'Interaction\ContentForwardController@toTwitter');


Route::get('live/discuss/list', 'Interaction\LiveController@getLiveDiscussList');
Route::post('live/discuss', 'Interaction\LiveController@createDiscuss');
