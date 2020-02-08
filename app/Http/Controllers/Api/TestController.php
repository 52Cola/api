<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\UserModel;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class TestController extends Controller
{
    public function test()
    {
        // echo date('Y-m-d H:i:s');

        // $user_info = [
        //     'uid'       =>123,
        //     'name'      =>'xiaoma',
        //     'email'     =>'429324527@qq.com',
        //     'age'       =>11
        // ];
        // // echo json_encode($user_info);
        // $response = [
        //     'error'     =>0,
        //     'msg'       =>'ojbk',
        //     'data'      =>[
        //         'user_info' =>$user_info
        //     ]
        // ];
        // echo json_encode($response);
        echo '<pre>';print_r($_SERVER);echo '</pre>';
    }

    //////////////////用户注册
    public function reg0(Request $request)
    {
        // echo '<pre>';print_r($request->input());echo '</pre>';
        //验证用户名   验证手机号  验证email
        $pass1 = $request->input('pass1'); 
        $pass2 = $request->input('pass2'); 
        if($pass1!=$pass2){
            die('两次输入密码不一样');
        }
        $password = password_hash($pass1,PASSWORD_BCRYPT);
        $data = [
            'email'     =>  $request->input('email'),
            'name'      =>  $request->input('name'),
            'password'  =>  $password,
            'mobile'    =>  $request->input('mobile'),
            'last_login'=>  time(),
            'last_ip'   =>  $_SERVER['REMOTE_ADDR'],    //获取远程IP
            
        ];
        $uid = UserModel::insertGetID($data);
        var_dump($uid);
    }

    public function login0(Request $request)
    {
        $name = $request->input('name');
        $pass = $request->input('pass');
        $u = UserModel::where(['name'=>$name])->first();
        if($u){
            //验证密码
            if( password_verify($pass,$u->password) ){
                // 登录成功
                //echo '登录成功';
                //生成token
                $token = Str::random(32);
                $response = [
                    'error' => 0,
                    'msg'   => 'ok',
                    'data'  => [
                        'token' => $token
                    ]
                ];
            }else{
                $response = [
                    'error' => 400003,
                    'msg'   => '密码不正确'
                ];
            }
        }else{
            $response = [
                'error' => 400004,
                'msg'   => '用户不存在'
            ];
        }
        return $response;
    }
    /**
     * 获取用户列表
     * 2020年1月2日16:32:07
     */
    public function userList()
    {
        $list = UserModel::all();
        print_r($list->toArray());
    }

    public function reg()
    {
        //请求passport
        $url = 'http://passport.mayang.xn--6qq986b3xl/api/user/reg';
        $response = UserModel::curlPost($url,$_POST);
        return $response;
    }
    /**
     * APP 登录
     */
    public function login()
    {
        //请求passport
        $url = 'http://passport.mayang.xn--6qq986b3xl/api/user/login';
        $response = UserModel::curlPost($url,$_POST);
        return $response;
    }
    public function showData()
    {
        // 收到 token
        $uid = $_SERVER['HTTP_UID'];
        $token = $_SERVER['HTTP_TOKEN'];
        // 请求passport鉴权
        $url = 'http://passport.mayang.xn--6qq986b3xl/api/auth';         //鉴权接口
        $response = UserModel::curlPost($url,['uid'=>$uid,'token'=>$token]);
        $status = json_decode($response,true);
        //处理鉴权结果
        if($status['errno']==0)     //鉴权通过
        {
            $data = "sdlfkjsldfkjsdlf";
            $response = [
                'errno' => 0,
                'msg'   => 'ok',
                'data'  => $data
            ];
        }else{          //鉴权失败
            $response = [
                'errno' => 40003,
                'msg'   => '授权失败'
            ];
        }
        return $response;
    }


    //防刷测试
    public function postman()
    {
        echo __METHOD__;
    }

    public function postman1()
    {
        $data = [
            'user_name' => 'zhangsan',
            'email'     => 'zhangsan@qq.com',
            'amount'    => 10000
        ];

        echo json_encode($data);
    }
    public function md5test()
    {
        $data = "Hello world";      //要发送的数据
        $key = "1905";              //计算签名的key  发送端与接收端拥有相同的key

        //计算签名  MD5($data . $key)
        //$signature = md5($data . $key);
        $signature = 'sdlfkjsldfkjsfd';

        echo "待发送的数据：". $data;echo '</br>';
        echo "签名：". $signature;echo '</br>';

        //发送数据
        $url = "http://passport.mayang.xn--6qq986b3xl/test/check?data=".$data . '&signature='.$signature;
        echo $url;echo '<hr>';

        $response = file_get_contents($url);
        echo $response;
    }

    //post签名的这个玩意
    public function sign2()
    {
        $key = "1905";          // 签名使用key  发送端与接收端 使用同一个key 计算签名

        //待签名的数据
        $order_info = [
            "order_id"          => 'LN_' . mt_rand(111111,999999),
            "order_amount"      => mt_rand(111,999),
            "uid"               => 12345,
            "add_time"          => time(),
        ];

        $data_json = json_encode($order_info);

        //计算签名
        $sign = md5($data_json.$key);

        // post 表单（form-data）发送数据
        $client = new Client();
        $url = 'http://passport.mayang.xn--6qq986b3xl/test/check2';
        $response = $client->request("POST",$url,[
            "form_params"   => [
                "data"  => $data_json,
                "sign"  => $sign
            ]
        ]);

        //接收服务器端响应的数据
        $response_data = $response->getBody();
        echo $response_data;

    }

       /**
     * 使用私钥验签
     */
    public function rsa1()
    {
        $priv_key = file_get_contents(storage_path('keys/priv.key'));
        echo $priv_key;echo "<hr>";
        $data = "小小马"; 
        echo "待价加密数据 ：".$data; echo "<hr>";
        openssl_private_encrypt($data,$enc_data,$priv_key);
        var_dump($enc_data);
        echo "<hr>";

        $base64_encode_str = base64_encode($enc_data);
        echo "base64数据: ".$base64_encode_str;
        $url = 'http://passport.mayang.xn--6qq986b3xl/rsa?data='.urlencode($base64_encode_str);
        echo '发送地址: '.$url;
        file_get_contents($url);    //发送请求

    }

    // 对称加密
    public function aes(){
        echo '<hr>';
        $str=request()->input('str');
        // echo $str;
        $method='AES-256-CBC';  //加密方式
        $key='sbhsfubsfdfqwijpjpsjasfnkoihogub';    //加密的密钥
        $iv='jingtdjopvrfhutd';       //必须为16位
        $enc=base64_decode($str);
        $d=openssl_decrypt($enc,$method,$key,OPENSSL_RAW_DATA,$iv);
        echo '解密后的数据：'.$d;
    }

   

}
