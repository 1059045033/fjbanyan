<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    $user = $request->user();
    $workingTime = \App\Models\WorkingTime::where('user_id',$user['id'])->select('name','start_time','end_time')->get()->toArray();
    $user['working_time'] = $workingTime;
    return $user;
});

Route::post('/domain','SmsController@domain');
// 注册路由
Route::post('/register','RegisterController@register');
Route::post('/login','RegisterController@login');
Route::post('/fileUpdate','RegisterController@fileUpdate');
Route::post('/version','RegisterController@version');
Route::post('/version/save','RegisterController@versionSave');
Route::post('/sms','SmsController@send');
Route::post('/email','MailController@send');
Route::post('/sms_test','SmsController@send_bak');
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
Route::post('/messages/read/all','WorkNoticeController@readAll');

// 任务
Route::post('/task/detail','TaskController@detail');//任务详情
Route::post('/task/accept','TaskController@accept');//任务领取/接受
Route::post('/task/execute','TaskController@execute');//任务执行
Route::post('/task/execute_list','TaskController@executeList');//任务执行列表
Route::post('/task/distribute_list','TaskController@distributeList');//任务执行列表
Route::post('/task/today/progress','TaskController@todayProgress');//

Route::post('/task/logs','TaskLogController@logs');//


// 团队
Route::post('/teams','MemberController@teamList');
Route::post('/team/join','MemberController@teamJoinWorkRegion');

// 作业安排
Route::post('/work/teams','MemberController@workTeams');//作业队伍列表
Route::post('/work/region/set','MemberController@workRegionSet');//设置工作人员的工作区域
Route::post('/work/to_work_region','MemberController@usersToWorkRegion');//设置工作人员的工作区域
Route::post('/work/remove_user','MemberController@removeUser');//
Route::post('/work/one_click_arrange','MemberController@oneClickArrange');//


Route::post('/company/lists','CompanyController@lists');

// 公共

Route::post('/helper','MemberController@helper');
Route::post('/bicycle','MemberController@bicycle');
Route::post('/safe/education','MemberController@safeEducation');






// 轨迹
Route::post('/track/create','TrackController@create');//
Route::post('/user/track/history','TrackController@userHistory');//


// 异常 exception
Route::post('/exception/create','ExceptionMsgController@create');//
Route::post('/user/exception/history','ExceptionMsgController@userHistory');//
Route::post('/user/exception/check','ExceptionMsgController@check');//



Route::post('/member/upload/image','MemberController@uploadeImage');
Route::post('/member/upload/no_sy_image','MemberController@uploadeNoSYImage');

Route::post('/task/execute_no_sy','TaskController@executeNoSY');//任务执行
Route::post('/task/execute_list_no_sy','TaskController@executeListNoSY');//任务执行列表

Route::post('/member/upload/apk','MemberController@uploadeApk');



Route::apiResource('/member','MemberController');
Route::post('/sms_notify','MemberController@smsNotify');


Route::post('/jpush','RegisterController@jpush');

#  =================== 后台管理接口 ======

// 登入/获取信息/退出
Route::post('/admin/login','Admin\AdminController@login');// -- recordlogs  1
Route::get('/admin/info','Admin\AdminController@info');//->middleware('opt_record');;
Route::post('/admin/logout','Admin\AdminController@logout');// -- recordlogs  1
Route::post('/admin/update','Admin\AdminController@update');//->middleware('opt_record');;

// 获取 未被安排的管理人员
Route::get('/admin/user/unarrange','Admin\WorkRegionController@unArrange');// 未被安排的2级别人员
// 获取 三级人员
Route::get('/admin/user/role_one','Admin\WorkRegionController@roleOne');//

// 区域管理
Route::get('/admin/region/regions','Admin\WorkRegionController@regions');// 所有区域列表
Route::post('/admin/region/create','Admin\WorkRegionController@create');// 创建区域  // -- recordlogs  1
Route::post('/admin/region/delete','Admin\WorkRegionController@delete'); // -- recordlogs 1
Route::post('/admin/region/belong_to','Admin\WorkRegionController@belongTo');//分配人员的所属区域  // -- recordlogs 1 (修改)


// 人员管理
Route::get('/admin/user/lists','Admin\MemberController@lists');
Route::post('/admin/user/create','Admin\MemberController@create'); // -- recordlogs
Route::post('/admin/user/edit','Admin\MemberController@edit');  // -- recordlogs
Route::post('/admin/user/delete','Admin\MemberController@delete'); // -- recordlogs


// 公司管理
Route::get('/admin/company/lists','Admin\CompanyController@lists');
Route::post('/admin/company/create','Admin\CompanyController@create'); // -- recordlogs 1
Route::post('/admin/company/delete','Admin\CompanyController@delete'); // -- recordlogs 1

// 轨迹
Route::get('/admin/tracks/all_lists','Admin\TrackController@all_lists');//

Route::get('/admin/tracks/track','Admin\TrackController@track');//

// 任务列表
Route::get('/admin/task/logs','Admin\TaskLogController@logs');//
Route::get('/admin/task/export','Admin\TaskLogController@export');//

// 考勤明细
Route::get('/admin/attendance/lists','Admin\AttendanceController@lists');

//Dashboard 页面数据
Route::get('/admin/dashboard/attendances','Admin\DashboardController@attendances');
Route::get('/admin/dashboard/region_nobody','Admin\DashboardController@region_nobody');
Route::get('/admin/dashboard/late_early','Admin\DashboardController@late_early');

// 获取所有数据
Route::get('/admin/region/regions_all','Admin\WorkRegionController@regions_all');// 所有区域列表
Route::get('/admin/company/company_all','Admin\CompanyController@company_all');// 所有区域列表


// 操作日志
Route::get('/admin/optrecord/lists','Admin\OptRecordController@lists');


// 公司管理
Route::get('/admin/regiongroup/lists','Admin\RegionGroupController@lists');
Route::post('/admin/regiongroup/create','Admin\RegionGroupController@create');
Route::post('/admin/regiongroup/delete','Admin\RegionGroupController@delete');
Route::get('/admin/regiongroup/group_all','Admin\RegionGroupController@group_all');

// 人员时间集合
Route::get('/admin/workingtime/lists','Admin\WorkingTimeController@lists');
Route::post('/admin/workingtime/delete','Admin\WorkingTimeController@delete');
Route::post('/admin/workingtime/create','Admin\WorkingTimeController@create');














Route::get('/test','TestController@test');
Route::get('/test03','TestController@test03');
Route::get('/test04','TestController@test04');
Route::get('/test05','TestController@test05');










//Route::get('/admin/info','TestController@info');
//
//Route::post('/admin/logout','TestController@logout');
//
//Route::post('/admin/transaction/list','TestController@list');

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
