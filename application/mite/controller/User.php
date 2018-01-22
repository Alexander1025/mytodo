<?php
namespace app\mite\controller;

use think\Controller;
class User extends Controller {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 用户登录
	 * @return [type] [description]
	 */
	public function login()
	{
		if(session('?ss_userid')){
			$this->error('您已经登录');
		}
		if (request()->isPost()){
			$username = input('post.username');
			$password = input('post.password');
			$userphone = input('post.userphone');
			$phonecode = input('post.phonecode');
			$response_data['code'] = 0;
			if(!empty($username)){
				//账号密码登录
				// db('user')->where()->value('password');
			}else{
				//手机动态码登录
				if(cache("authphonecode_{$userphone}") == trim($phonecode)){
					//执行登录
					$map['mobile'] = trim($userphone);
					$userid = db('user')->where($map)->value("userid");
					session('ss_userid',$userid);
					cache("authphonecode_{$userphone}",'');
					$response_data['msg'] = "登录成功";
					$response_data['url'] = url('mite/index/index');
				}else{
					$response_data['code'] = 400;
					$response_data['msg'] = "验证码不正确";
				}
			}
			echo json_encode($response_data);
			exit;
		}

		//判断运行环境 微信 : 自动授权登录 ， APP / 手机浏览器 输入手机号码登录 
	    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false && input('get.no_wechat') != 1) { 
	    	if(!input('get.wechat_openid')){
		        $url = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : url('mite/index/index');
	    		session('ret_url', $url);
		        //执行微信授权登录
				$auth_url = "http://www.xiniuw.com/lzt/authorize.php?url=" . base64_encode(url('mite/user/login'));
				header("Location: $auth_url");
	    	}
	    	//获取到openid 自动登录
	    	if(!input('get.wallet_mobile')){
	    		//没有账号执行注册
	    		$insert_data['username'] = 'wx_'. time() . rand(1000,9999);
	    		$insert_data['openid'] = trim(input('get.wechat_openid'));
	    		$userid = db('user')->insertGetId($insertdata);
	    		session('ss_userid',$userid);
	    		//跳转到原来的页面
	    		$this->redirect(session('ret_url'),302);
	    	}
	    	$mobile = trim(input('get.wallet_mobile'));
	    	$userid = db('user')->where("mobile = {$mobile}")->value("userid");
	    	session('ss_userid',$userid);
	    	$this->redirect(session('ret_url'),302);
	    }
		return $this->fetch();
	}

	/**
	 * 用户登出
	 * @return [type] [description]
	 */
	public function logout()
	{
		session('ss_userid',null);
	}

	/**
	 * 用户中心
	 * @return [type] [description]
	 */
	public function profile()
	{
		# code...
	}

	public function register()
	{
		if (request()->isPost()){
			$userphone = input('post.userphone');
			$phonecode = input('post.phonecode');
			$response_data['code'] = 0;

			if(cache("regphonecode_{$userphone}") == trim($phonecode)){
				//执行注册
				$user_data['username'] = $userphone;
				session('ss_userid',$userid);
				$response_data['msg'] = "注册成功";
			}else{
				$response_data['code'] = 400;
				$response_data['msg'] = "验证码不正确";
			}
			echo json_encode($response_data);
			exit;
		}
	}


	public function getLoginPhoneCode()
	{
		$userphone = trim(input('post.userphone'));
		$map['mobile'] = $userphone;
		$hasUser = db('user')->where($map)->count();
		$response_data['code'] = 0;
		if(!$hasUser){
			$response_data['code'] = 400;
			$response_data['msg'] = "该用户不存在";
			echo json_encode($response_data);
			exit;
		}
		//检查验证码是否存在
		if(cache("authphonecode_{$userphone}")){
			$response_data['code'] = 400;
			$response_data['msg'] = "请勿频繁获取验证码";
			echo json_encode($response_data);
			exit;
		}

		$phonecode = rand(100000,999999);
		$phonecode = 1234;
		cache("authphonecode_{$userphone}", $phonecode, 600);
		//发送验证码 TODO ...

		$response_data['msg'] = "验证码已发送";
		echo json_encode($response_data);
		exit;

	}

	/**
	 * 获取注册验证码
	 * @return [type] [description]
	 */
	public function getRegPhoneCode()
	{
		$userphone = trim(input('get.userphone'));
		$map['mobile'] = $userphone;
		$hasUser = db('user')->where($map)->count();
		$response_data['code'] = 0;
		if($hasUser){
			$response_data['code'] = 400;
			$response_data['msg'] = "该用户已存在";
			echo json_encode($response_data);
			exit;
		}

		//检查验证码是否存在
		if(cache("regphonecode_{$userphone}")){
			$response_data['code'] = 400;
			$response_data['msg'] = "请勿频繁获取验证码";
			echo json_encode($response_data);
			exit;
		}

		$phonecode = rand(100000,999999);
		$phonecode = 1234;
		cache("regphonecode_{$userphone}", $phonecode, 600);
		//发送验证码 TODO ...

		$response_data['msg'] = "验证码已发送";
		echo json_encode($response_data);
		exit;

	}


}