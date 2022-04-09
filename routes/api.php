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
Route::post('/member/upload/face','MemberController@uploadeFace');





Route::apiResource('/member','MemberController');





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
