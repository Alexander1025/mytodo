<?php
namespace app\mite\controller;

use think\Controller;

class Reserve extends Controller {

	public function __construct() {

		parent::__construct();

		$this->userid = intval(session('ss_userid'));

		if (!$this->userid) {

			// $this->error('请在微信端访问');

			//记录ret_url
			$url = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : url('mite/index/index');
			session('ret_url', $url);

			//跳转登录页面
			$login_url = url('mite/user/login');
			header("Location: $login_url");
			exit;

		}

	}

	//预订首页、预订信息填写
	public function index() {

		if (!input('?houseid')) {

			$this->error('参数houseid缺失', 'Index/index'); //参数houseid缺失

		}

		$houseid = input('houseid');

		//用户常驻入住人信息获取
		$checkinlist = $this->getusercheckinlist();

		$this->assign('checkinlist', $checkinlist);

		return $this->fetch();

	}

	//预订信息查看
	public function information() {

		return $this->fetch();

	}

	//添加预订
	public function addreserve() {

		$userid = $this->userid;

		$post = input('post.');

		if (empty($post)) {

			return ['state' => 0, 'info' => '参数为空'];

		}

		if (empty($post['houseid'])) {

			return ['state' => 0, 'info' => '参数houseid为空'];

		}

		$houseData = db('House')->where("houseid={$post['houseid']}")->find(); //房源信息

		if (empty($houseData)) {

			return ['state' => 0, 'info' => "不存在houseid为{$post['houseid']}的房源"];

		}

		//判断入住时间和离开时间
		if ($post['checkintime'] <= 0 || $post['leavetime'] <= 0) {

			return ['state' => 0, 'info' => '请选择入住时间或离开时间'];

		}

		if ($post['checkintime'] >= $post['leavetime']) {

			return ['state' => 0, 'info' => '离开时间不能小于入住时间'];

		}

		//判断房源是否为可预订房源
		//unfinished！！！！！！！！！！！！

		//判断是否有入住人信息
		// if (!is_array($post['checkinuser'])) {

		// 	return ['state' => 0, 'info' => "请填写入住人"];

		// }

		// //判断入住人信息是否再HouseCheckinuser中
		// $count1 = db('HouseCheckinuser')->where(['checkinuserid' => ['IN', $post['checkinuser']], 'reserveid' => 0])->count();
		// if ($count1 != count($post['checkinuser'])) {

		// 	return ['state' => 0, 'info' => "checkinuser参数错误"];

		// }
		if (empty($post['usercheckinid'])) {

			return new \think\response\Json(['state' => 0, 'info' => "请填写或选择入住人"]);

		}

		$usercheckinid = implode(',', $post['usercheckinid']);

		$usercheckindata = db('UserCheckinuser')->where(['usercheckinid'=>['IN',$usercheckinid]])->select();

		if (empty($usercheckindata)) {

			return new \think\response\Json(['state' => 0, 'info' => "找不到用户常用入住人信息"]);

		}

		//验证......

		//预订数据
		$addData = [
			'userid' => $userid,
			'houseid' => $post['houseid'],
			'reservetime' => date('Y-m-d H:i:s'),
			// 'checkintime' => trim($post['checkintime']) . '00:00:00',
			// 'leavetime' => trim($post['leavetime']) . '00:00:00',
			'checkintime' => date('Y-m-d H:i:s', trim($post['checkintime'])),
			'leavetime' => date('Y-m-d H:i:s', trim($post['leavetime'])),
			'insurance' => (!!$post['insurance']) ? 1 : 0,
			'invoice' => (!!$post['invoice']) ? 1 : 0,
		];

		foreach ($usercheckindata as $v_u) {
			
			//房源入住人数据
			$CheckinAddData = [
				'userid' => $userid,
				'reserveid' => $reserveid,
				'realname' => $v_u['realname'],
				'idcard' => $v_u['idcard'],
				'mobile' => $v_u['mobile'],
			];

		}

		//添加预订信息
		$reserveid = db('HouseReserve')->insertGetId($addData);

		if (!$reserveid) {

			return new \think\response\Json(['state' => 0, 'info' => "添加预订信息失败"]);

		}

		//添加房源入住人
		db('HouseCheckinuser')->insertAll($CheckinAddData);

		return new \think\response\Json(['state' => 1, 'info' => "添加预订信息成功"]);

	}

	//添加用户常用入住人信息
	public function addcheckinuser() {

		$post = input('post.');

		$userid = $this->userid;

		if (empty($post['realname'])) {

			return new \think\response\Json(['state' => 0, 'info' => "缺少参数realname"]);

		}

		if (empty($post['idcard'])) {

			return new \think\response\Json(['state' => 0, 'info' => "缺少参数idcard"]);

		}

		if (empty($post['mobile'])) {

			return new \think\response\Json(['state' => 0, 'info' => "缺少参数mobile"]);

		}

		//验证手机号码
		if (!preg_match("/^1[34578]{1}\d{9}$/", $post['mobile'])) {

			return new \think\response\Json(['state' => 0, 'info' => "手机号码格式错误"]);

		}

		//验证身份证
		if (!$this->isidcard($post['idcard'])) {

			return new \think\response\Json(['state' => 0, 'info' => "身份证格式错误"]);

		}

		$addData = [
			'userid' => $userid,
			'realname' => $post['realname'],
			'idcard' => $post['idcard'],
			'mobile' => $post['mobile'],
		];

		$usercheckinid = db('UserCheckinuser')->insertGetId($addData);

		if (!$usercheckinid) {

			return new \think\response\Json(['state' => 0, 'info' => "添加失败"]);

		}

		$addData['usercheckinid'] = $usercheckinid;

		return new \think\response\Json(['state' => 1, 'info' => "添加成功", 'data' => $addData]);

	}

	// //添加房源入住人信息表
	// public function addhousecheckinuser() {

	// 	$post = input('post.');

	// 	$userid = $this->userid;

	// 	if (empty($post['reserveid'])) {

	// 		return new \think\response\Json(['state' => 0, 'info' => "缺少参数reserveid"]);

	// 	}

	// 	if (empty($post['usercheckinid'])) {

	// 		return new \think\response\Json(['state' => 0, 'info' => "缺少参数usercheckinid"]);

	// 	}

	// 	$usercheckinid = implode(',', $post['usercheckinid']);

	// 	$usercheckindata = db('UserCheckinuser')->where(['usercheckinid'=>['IN',$usercheckinid]])->select();

	// 	if (empty($usercheckindata)) {

	// 		return new \think\response\Json(['state' => 0, 'info' => "找不到用户常用入住人信息"]);

	// 	}

	// 	foreach ($usercheckindata as $v_u) {
			
	// 		$addData = [
	// 			'userid' => $userid,
	// 			// 'reserveid' => $post['reserveid'],
	// 			'realname' => $v_u['realname'],
	// 			'idcard' => $v_u['idcard'],
	// 			'mobile' => $v_u['mobile'],
	// 		];

			

	// 	}

	// 	$addcount = db('HouseCheckinuser')->insertAll($addData);

	// 	if (!$checkinuserid) {

	// 		return new \think\response\Json(['state' => 0, 'info' => "添加失败"]);

	// 	}

	// 	return new \think\response\Json(['state' => 1, 'info' => "添加成功", 'data' => $addData]);

	// }

	//删除入住人信息
	// public function delcheckinuser() {

	// 	$checkinuserid = input('post.checkinuserid');

	// 	if (!$checkinuserid) {

	// 		return new \think\response\Json(['state' => 0, 'info' => "缺少参数"]);

	// 	}

	// 	//检查是否绑定reserveid
	// 	$HouseCheckinuserData = db('HouseCheckinuser')->where(['checkinuserid' => $checkinuserid])->find();

	// 	if (isset($HouseReserveData['reserveid']) && $HouseReserveData['reserveid']) {

	// 		$reserveid = intval($HouseReserveData['reserveid']);

	// 		$HouseReserveData = db('HouseReserve')->where(['reserveid' => $reserveid])->find();

	// 		//检查关联预订信息是否可编辑修改
	// 		if (isset($HouseReserveData['status']) && $HouseReserveData['status']) {

	// 			return new \think\response\Json(['state' => 0, 'info' => "预订信息处于不可编辑状态"]);

	// 		}

	// 		$checkinuserarr = json_decode($post['checkinuser'], true);
	// 		array_splice($checkinuserarr, $checkinuserid); //移除$checkinuserid

	// 		$ret1 = db('HouseReserve')->where(['reserveid' => $reserveid])->setField(['checkinuser' => json_encode($checkinuserarr)]); //修改checkinuser字段

	// 		if ($ret1 === false) {

	// 			return new \think\response\Json(['state' => 0, 'info' => "删除失败"]);

	// 		}

	// 	}

	// 	$ret2 = db('HouseCheckinuser')->delete($checkinuserid); //删除

	// 	if ($ret2 === false) {

	// 		return new \think\response\Json(['state' => 0, 'info' => "删除失败"]);

	// 	}

	// 	return new \think\response\Json(['state' => 1, 'info' => "删除成功"]);

	// }

	//用户常驻入住人信息获取
	protected function getusercheckinlist() {

		$userid = $this->userid;

		if (!$userid) {

			return [];

		}

		$data = db('UserCheckinuser')->where(['userid' => $userid])->select();

		return $data;

	}

	//入住人信息获取
	// protected function checkinuserlist($checkinuserid) {

	// 	if (!$checkinuserid) {

	// 		return [];

	// 	}

	// 	$where = ['checkinuserid' => $checkinuserid];

	// 	if (is_array($checkinuserid)) {

	// 		$where['checkinuserid'] = ['IN', $checkinuserid];

	// 	}

	// 	$data = db('HouseCheckinuser')->where($map)->select();

	// 	return $data;

	// }

	//验证身份证
	public function isidcard($id) {

		$id = strtoupper($id);
		$regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
		$arr_split = array();

		if (!preg_match($regx, $id)) {

			return false;

		}

		if (15 == strlen($id)) {
			//检查15位
			$regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

			@preg_match($regx, $id, $arr_split);
			//检查生日日期是否正确
			$dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];

			if (!strtotime($dtm_birth)) {

				return false;

			} else {

				return true;

			}

		} else {
			//检查18位
			$regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";

			@preg_match($regx, $id, $arr_split);
			$dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];

			//检查生日日期是否正确
			if (!strtotime($dtm_birth)) {

				return false;

			} else {

				//检验18位身份证的校验码是否正确。
				//校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
				$arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
				$arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
				$sign = 0;

				for ($i = 0; $i < 17; $i++) {

					$b = (int) $id{$i};
					$w = $arr_int[$i];
					$sign += $b * $w;

				}

				$n = $sign % 11;
				$val_num = $arr_ch[$n];

				if ($val_num != substr($id, 17, 1)) {

					return false;

				} else {

					return true;

				}

			}

		}

	}

}
