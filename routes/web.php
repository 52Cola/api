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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/info', function () {
    phpinfo();
});

Route::get('/test/pay','TestController@alipay');   //去支付
Route::get('/test/alipay/return','Alipay\PayController@aliReturn');//同步
Route::post('/test/alipay/notify','Alipay\PayController@notify');//异步

Route::get('/test/ascii','TestController@ascii');   //测试加密
Route::get('/test/dec','TestController@dec');   //测试解密
Route::get('/test/postman','Api\TestController@postman');
Route::get('/test/postman1','Api\TestController@postman1');//防刷测试
Route::get('/test/rsa1','Api\TestController@rsa1');//私钥验签

Route::get('sign/aes','Sign\SignController@aes');//对称加密



Route::get('/test/md5','Api\TestController@md5test'); //注册 签名
Route::get('/test/sign2','Api\TestController@sign2');   //post


//接口
Route::get('/api/test','Api\TestController@test');   
Route::post('/api/user/reg','Api\TestController@reg');        //用户注册  
Route::post('/api/user/login','Api\TestController@login');        //用户登录  
Route::get('/api/user/list','Api\TestController@userList')->middleware('filter','check.token');      //用户列表
Route::get('/api/show/data','Api\TestController@showData');     //获取数据接口