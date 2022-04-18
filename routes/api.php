<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// 注册路由
Route::post('/register','RegisterController@register');
Route::post('/login','RegisterController@login');
Route::post('/sms','SmsController@send');
Route::post('/banner','BannerController@index');
Route::post('/online','OnlineOfflineController@online');
Route::post('/offline','OnlineOfflineController@offline');
Route::post('/onoffline/history','OnlineOfflineController@history');
Route::get('/regions','WorkRegionController@regions');
Route::get('/region','WorkRegionController@region');
Route::post('/member/upload/face','MemberController@uploadeFace');

// 消息通知
Route::post('/messages/notice','WorkNoticeController@index');
Route::post('/messages/notice/read','WorkNoticeController@read');
Route::post('/messages/notice/create','WorkNoticeController@create');
Route::post('/messages/unread/tip','WorkNoticeController@unReadTip');

// 任务
Route::post('/task/detail','TaskController@detail');//任务详情
Route::post('/task/accept','TaskController@accept');//任务领取/接受
Route::post('/task/execute','TaskController@execute');//任务执行
Route::post('/task/execute_list','TaskController@executeList');//任务执行列表
Route::post('/task/distribute_list','TaskController@distributeList');//任务执行列表
Route::post('/task/today/progress','TaskController@todayProgress');//


// 团队
Route::post('/teams','MemberController@teamList');
Route::post('/team/join','MemberController@teamJoinWorkRegion');

// 作业安排
Route::post('/work/teams','MemberController@workTeams');//作业队伍列表
Route::post('/work/region/set','MemberController@workRegionSet');//设置工作人员的工作区域
Route::post('/work/to_work_region','MemberController@usersToWorkRegion');//设置工作人员的工作区域
Route::post('/work/remove_user','MemberController@removeUser');//


// 公共

Route::post('/helper','MemberController@helper');
Route::post('/bicycle','MemberController@bicycle');
Route::post('/safe/education','MemberController@safeEducation');






// 轨迹
Route::post('/track/create','TrackController@create');//
Route::post('/user/track/history','TrackController@userHistory');//



Route::post('/member/upload/image','MemberController@uploadeImage');



Route::apiResource('/member','MemberController');


Route::post('/jpush','RegisterController@jpush');


//Route::post('/register',  function (Request $request) {
//    echo '23424242';
//});
//Route::post('/register', [RegisterController::class, 'register']);
//Route::post('/register', 'RegisterController@register');
//Route::apiResource('/topics','TopicController');
//Route::apiResource('/discussions','DiscussionController');
//Route::apiResource('/likes','LikeController');
//Route::post('{likeable}/likes','LikeController@store');//likeable 使用占位符

//Route::apiResource('/topics',TopicController::class);
//Route::apiResources([
//    'topics'=>'TopicController'
//]);
