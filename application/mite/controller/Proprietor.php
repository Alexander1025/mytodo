<?php
namespace app\mite\controller;

use think\Controller;

class Proprietor extends Controller {

	public function __construct() {

		parent::__construct();

		$this->userid = intval(session('ss_userid'));

		if (!$this->userid) {

			$this->error('请在微信端访问');

		}

	}

	// 发布房源
	public function issued() {

		$userid = $this->userid;

		//房源列表
		$houseData = db('House')->where("userid={$userid}")->select();

		$this->assign('houseData', $houseData);

		// echo '<pre>';
		// var_dump($houseData);exit;

		return $this->fetch();

	}

	// 发布房源-房屋类型选择 1-1
	public function housetype() {

		return $this->fetch();

	}

	// 发布房源-出租时间选择 1-2
	public function leaseperiodtype() {

		$userid = $this->userid;

		if (!input('?housetype')) {

			$this->error('参数housetype缺失', 'Proprietor/housetype'); //参数housetype缺失

		}

		$this->assign('housetype', input('param.housetype'));

		return $this->fetch();

	}

	// 发布房源-出租类型选择 1-3
	public function renttype() {

		$userid = $this->userid;

		if (!input('?housetype')) {

			$this->error('参数housetype缺失', 'Proprietor/housetype'); //参数housetype缺失

		}

		if (!input('?leaseperiodtype')) {

			$this->error('参数leaseperiodtype缺失', 'Proprietor/leaseperiodtype'); //参数leaseperiodtype缺失

		}

		$this->assign('housetype', input('param.housetype'));
		$this->assign('leaseperiodtype', input('param.leaseperiodtype'));

		return $this->fetch();

	}

	// 发布房源-房源信息首页 2
	public function rentinfo() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据

		if (empty($houseData)) {

			$this->error('找不到该房源信息', 'Proprietor/issued'); //找不到该房源信息

		}

		$houseImageData = db('HouseImage')->where(['houseid' => $houseid, 'status' => ['IN', [1, 2]]])->select(); //房源图片 status:1有效 2等待审核 0被删除
		$houseData['houseImageData'] = $houseImageData; //房源图片放到房源数据中

		//计算完成进度
		$schedule = $this->schedule($houseData);

		$this->assign('houseData', $houseData); //房源数据
		$this->assign('schedule', $schedule); //完成进度

		// echo '<pre>';
		// var_dump($houseImageData);exit;

		return $this->fetch();

	}

	// 发布房源-房源信息首页-房源信息录入页面 2-1
	public function basicinfo() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据
		$this->assign('houseData', $houseData);

		return $this->fetch();

	}

	// 发布房源-房源信息首页-床铺信息显示页面 2-2
	public function bedinfo() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据
		$this->assign('houseData', $houseData);
		$bedinfo = json_decode($houseData['bed'], true) ?: []; //床铺信息
		$this->assign('bedinfo', $bedinfo);

		return $this->fetch();

	}

	// 发布房源-房源信息首页-床铺信息录入页面 2-2-1
	public function bedinfoentering() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$this->assign('houseid', $houseid);

		return $this->fetch();

	}

	// 发布房源-房源信息首页-房源描述录入页面 2-3
	public function describeinfo() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据
		$this->assign('houseData', $houseData);

		return $this->fetch();

	}

	// 发布房源-房源信息首页-配套设备录入页面 2-4
	public function facilityinfo() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据
		$this->assign('houseData', $houseData);

		return $this->fetch();

	}

	// 发布房源-房源信息首页-价格规则录入页面 2-5
	public function priceinfo() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据
		$this->assign('houseData', $houseData);

		return $this->fetch();

	}

	// 发布房源-房源信息首页-地理位置信息录入 2-6
	public function mapinfo() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据
		$this->assign('houseData', $houseData);

		return $this->fetch();

	}

	//新增房源
	public function addrenttype() {

		$userid = $this->userid;

		if (!input('?housetype')) {

			return new \think\response\Json(['state' => 0, 'info' => '参数housetype缺失']);

		}

		if (!input('?leaseperiodtype')) {

			return new \think\response\Json(['state' => 0, 'info' => '参数leaseperiodtype缺失']);

		}

		if (!input('?leasetype')) {

			return new \think\response\Json(['state' => 0, 'info' => '参数leasetype缺失']);

		}

		$housetype = input('param.housetype');
		$leaseperiodtype = input('param.leaseperiodtype');
		$leasetype = input('param.leasetype');

		//判断数据是否正确
		if (!in_array($housetype, [0, 1, 2]) || !in_array($leaseperiodtype, [0, 1]) || !in_array($leasetype, [0, 1, 2, 3])) {

			return new \think\response\Json(['state' => 0, 'info' => '参数错误']);

		}

		//添加数据
		$houseid = db('House')->insertGetId(['userid' => $userid, 'housetype' => $housetype, 'leaseperiod' => $leaseperiodtype, 'leasetype' => $leasetype, 'status' => 3]); //'status'=>3,已选分类未编辑

		if (!!$houseid) {

			// $this->success('新增成功', 'renttype');
			// $this->redirect('Proprietor/rentinfo',['house_id' => $houseid]);
			return new \think\response\Json(['state' => 1, 'info' => '添加成功！', 'data' => ['houseid' => $houseid]]);

		}

		return new \think\response\Json(['state' => 0, 'info' => '未知错误']);

	}

	//更新房源信息
	public function updhousedata() {

		$userid = $this->userid;
		$post = input('post.');

		// if (empty($post)) {

		// 	return new \think\response\Json(['state' => 0, 'info' => '参数为空']);

		// }

		// if (!(isset($post['houseid']) && !empty($post['houseid']))) {

		// 	return new \think\response\Json(['state' => 0, 'info' => '参数houseid为空']);

		// }

		// $houseData = db('House')->where("houseid={$post['houseid']}")->find(); //房源信息

		// if (empty($houseData)) {

		// 	return new \think\response\Json(['state' => 0, 'info' => "不存在houseid为{$post['houseid']}的房源"]);

		// }

		// if ($houseData['userid'] != $userid) {

		// 	return new \think\response\Json(['state' => 0, 'info' => "userid不匹配,无法更新"]);

		// }

		$checked = $this->updchecked($post); //更新前检查一下

		if (!$checked['status']) {

			return new \think\response\Json($checked['aReturn']); //检查不通过,返回更新失败

		}

		$houseData = $checked['houseData']; //房源信息

		//更新的数据进行过滤
		$updData = $this->updfieldfilter($post);

		$updData['status'] = 0; //修改状态为0未审核

		$bool = db('House')->where("houseid={$post['houseid']}")->setField($updData); //更新

		if ($bool === false) {

			return new \think\response\Json(['state' => 0, 'info' => "更新失败"]);

		}

		return new \think\response\Json(['state' => 1, 'info' => "更新成功"]);

	}

	//更新房源信息(更新床铺信息)
	public function updbedinfo() {

		$userid = $this->userid;
		$post = input('post.');

		$checked = $this->updchecked($post); //更新前检查一下

		if (!$checked['status']) {

			return new \think\response\Json($checked['aReturn']); //检查不通过,返回更新失败

		}

		$houseData = $checked['houseData']; //房源信息

		//数据过滤
		$verifyfield = ['form', 'length', 'width', 'num'];
		$updData = array_intersect_key($post, array_flip($verifyfield));

		//form、length、width、num必须都存在
		if (count($updData) != 4) {

			return new \think\response\Json(['state' => 0, 'info' => "更新失败,缺少参数"]); //缺少参数

		}

		//数据处理
		$updData['form'] = intval($updData['form']);
		$updData['length'] = floatval($updData['length']);
		$updData['width'] = floatval($updData['width']);
		$updData['num'] = intval($updData['num']);

		//检查bed字段是否为空
		$bedDataJsonStr = trim($houseData['bed']);
		$bedDataArr = json_decode($bedDataJsonStr, true); //强制转数组

		if (empty($bedDataArr)) {

			$bedDataArr = [];
			$bedDataArr[1] = $updData; //key要从1开始，如果从0开始，json_encode后的json字符串不带key

		} else {

			$bedDataArr[] = $updData;

		}

		$updDataJsonStr = ['bed' => json_encode($bedDataArr)]; //转换成json字符串

		$updDataJsonStr['status'] = 0; //修改状态为0未审核

		$bool = db('House')->where("houseid={$post['houseid']}")->setField($updDataJsonStr); //更新

		if ($bool === false) {

			return new \think\response\Json(['state' => 0, 'info' => "更新失败"]);

		}

		return new \think\response\Json(['state' => 1, 'info' => "更新成功"]);

	}

	//更新房源信息(删除床铺信息)
	public function delbedinfo() {

		$userid = $this->userid;
		$post = input('post.');

		$checked = $this->updchecked($post); //更新前检查一下

		if (!$checked['status']) {

			return new \think\response\Json($checked['aReturn']); //检查不通过,返回更新失败

		}

		if (input('?post.bedid') === false) {

			return new \think\response\Json(['state' => 0, 'info' => "缺少参数"]);

		}

		$bedid = input('post.bedid');

		$houseData = $checked['houseData']; //房源信息

		//检查bed字段是否为空
		$bedDataJsonStr = trim($houseData['bed']);
		$bedDataArr = json_decode($bedDataJsonStr, true); //强制转数组

		if (empty($bedDataArr)) {

			return new \think\response\Json(['state' => 0, 'info' => "没有可删除的床铺"]);

		}

		if (!isset($bedDataArr[$bedid])) {

			return new \think\response\Json(['state' => 0, 'info' => "无法删除,不存在该床铺"]);

		}

		unset($bedDataArr[$bedid]); //删除床铺数据

		$updDataJsonStr = ['bed' => json_encode($bedDataArr)]; //转换成json字符串

		$updDataJsonStr['status'] = 0; //修改状态为0未审核

		$bool = db('House')->where("houseid={$post['houseid']}")->setField($updDataJsonStr); //删除后,更新

		if ($bool === false) {

			return new \think\response\Json(['state' => 0, 'info' => "更新失败"]);

		}

		return new \think\response\Json(['state' => 1, 'info' => "更新成功"]);

	}

	//更新房源信息(更新配套设备)
	public function updoptionsinfo() {

		$userid = $this->userid;
		$post = input('post.');

		$checked = $this->updchecked($post); //更新前检查一下

		if (!$checked['status']) {

			return new \think\response\Json($checked['aReturn']); //检查不通过,返回更新失败

		}

		$houseData = $checked['houseData']; //房源信息

		if (!input('?post.optionids')) {

			return new \think\response\Json(['state' => 0, 'info' => "参数optionids为空"]);

		}

		$optionids = isset($post['optionids']) ? $post['optionids'] : []; //已选中的房屋选项optionid
		// $optionids = [21,22,23,24];

		//数据进行过滤
		$houseOption = db('HouseOption')->field('optionid')->select();
		$optionidAll = array_column($houseOption, 'optionid'); //数据表中所有的optionid
		$optionids = array_intersect($optionids, $optionidAll); //取交集
		$optionids = array_values($optionids); //关联数组转换成索引数组
		$updDataJsonStr = ['options' => json_encode($optionids)]; //转换成json字符串

		// echo '<pre>';
		// var_dump($updDataJsonStr);exit;

		$updDataJsonStr['status'] = 0; //修改状态为0未审核

		$bool = db('House')->where("houseid={$post['houseid']}")->setField($updDataJsonStr); //更新

		if ($bool === false) {

			return new \think\response\Json(['state' => 0, 'info' => "更新失败"]);

		}

		return new \think\response\Json(['state' => 1, 'info' => "更新成功"]);

	}

	//删除房源图片(只删除未发布成功的)
	public function delimage() {

		$userid = $this->userid;

		if (!input('?imageid')) {

			return new \think\response\Json(['state' => 0, 'info' => "参数imageid缺失"]);

		}

		$imageid = input('imageid');
		$houseImageData = db('HouseImage')->where(['imageid' => $imageid, 'userid' => $userid])->find(); //房源图片数据

		if (empty($houseImageData)) {

			return new \think\response\Json(['state' => 0, 'info' => "找不到房源图片"]);

		}

		$houseData = db('House')->where(['houseid' => $houseImageData['houseid'], 'userid' => $userid])->find(); //房源数据

		if (empty($houseData)) {

			return new \think\response\Json(['state' => 0, 'info' => "找不到房源数据"]);

		}

		if (($houseData['status'] == 1) || ($houseData['status'] == 2)) {

			return new \think\response\Json(['state' => 0, 'info' => "发布成功或已禁用的房源无法删除图片"]);

		}

		$imgPath = INDEX_PATH . '../miteuploads/' . $houseImageData['url'];
		is_file($imgPath) && unlink($imgPath);
		db('HouseImage')->delete([$houseImageData['imageid']]); //删除图片

		return new \think\response\Json(['state' => 1, 'info' => "删除成功"]);

	}

	//删除房源(只删除未发布成功的)
	public function delunfinished() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			return new \think\response\Json(['state' => 0, 'info' => "参数houseid缺失"]);

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据

		if (empty($houseData)) {

			return new \think\response\Json(['state' => 0, 'info' => "找不到房源数据"]);

		}

		if (($houseData['status'] == 1) || ($houseData['status'] == 2)) {

			return new \think\response\Json(['state' => 0, 'info' => "发布成功或已禁用的房源无法删除"]);

		}

		$imgData = db('HouseImage')->where(['houseid' => $houseid, 'userid' => $userid])->select(); //房源数据

		foreach ($imgData as $k => $v) {

			$imgPath = INDEX_PATH . '../miteuploads/' . $v['url'];
			is_file($imgPath) && unlink($imgPath);
			db('HouseImage')->delete([$v['imageid']]); //删除图片

		}

		db('House')->delete([$houseData['houseid']]); //删除房源

		return new \think\response\Json(['state' => 1, 'info' => "删除成功"]);

	}

	//发布房源-确认发布 3
	public function confirmissued() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$houseData = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->find(); //房源数据

		$schedule = $this->schedule($houseData); //计算完成进度

		//判断必填信息是否完整
		if (!$schedule['basicinfo']['state'] || !$schedule['bedinfo']['state'] || !$schedule['describeinfo']['state'] || !$schedule['priceinfo']['state'] || !$schedule['mapinfo']['state']) {

			return new \think\response\Json(['state' => 0, 'info' => "请填写完必填信息后再发布"]);

		}

		$imgcount = db('HouseImage')->where(['houseid' => $houseid, 'status' => ['NEQ', 0]])->count(); //上传图片数量

		if (!$imgcount) {

			return new \think\response\Json(['state' => 0, 'info' => "请至少上传一张房源图片"]);

		}

		$bool = db('House')->where(['houseid' => $houseid, 'userid' => $userid])->setField(['status' => 1]); //更新房源状态 1正常

		if ($bool === false) {

			return new \think\response\Json(['state' => 0, 'info' => "发布失败"]);

		}

		return new \think\response\Json(['state' => 1, 'info' => "发布成功"]);

	}

	/**
	 * 更新前检查一下
	 * @param $post 数据
	 * @return $data 过滤后的数据
	 */
	protected function updchecked($post) {

		$userid = $this->userid;

		if (empty($post)) {

			return ['status' => 0, 'aReturn' => ['state' => 0, 'info' => '参数为空']];

		}

		if (!(isset($post['houseid']) && !empty($post['houseid']))) {

			return ['status' => 0, 'aReturn' => ['state' => 0, 'info' => '参数houseid为空']];

		}

		$houseData = db('House')->where("houseid={$post['houseid']}")->find(); //房源信息

		if (empty($houseData)) {

			return ['status' => 0, 'aReturn' => ['state' => 0, 'info' => "不存在houseid为{$post['houseid']}的房源"]];

		}

		if ($houseData['status'] == 1 || $houseData['status'] == 2) {

			return ['status' => 0, 'aReturn' => ['state' => 0, 'info' => "房源已发布，无法修改"]]; //1:正常 2:禁用

		}

		if ($houseData['userid'] != $userid) {

			return ['status' => 0, 'aReturn' => ['state' => 0, 'info' => "userid不匹配,无法更新"]];

		}

		return ['status' => 1, 'houseData' => $houseData];

	}

	/**
	 * 房源更新的数据过滤
	 * @param $data 数据
	 * @return $data 过滤后的数据
	 */
	protected function updfieldfilter($data) {

		$verifyfield = [
			//房源信息
			'housearea', //房源面积
			'livingroom', //客厅
			'bedroom', //房间
			'bathroom', //卫生间
			'kitchen', //厨房
			'veranda', //阳台
			'tenantnum', //宜居人数
			'bedsheet', //被单更换 0:每客 1:每日
			'toilettype', //卫生间类型 0:共用 1:独立
			//床铺信息
			// 'bed', //床  updbedinfo方法更新
			//房源描述
			'title', //房源标题
			'description', //个性描述
			'innerinfo', //内部情况
			'trafficinfo', //交通情况
			'otherinfo', //周边情况
			//配套设备
			// 'options', //服务设施  updoptionsinfo方法更新
			//价格规则
			'cashpledge', //押金
			'monthprice', //月租
			'dayprice', //日租
			'additionprice', //其他额外费用
			'foreigner', //接待外国人
			'children', //接待儿童
			'elder', //接待老人
			'userule', //使用要求
			'hiddentips', //入住提示
			//房源地址
			'province', //省 int
			'city', //城市 int
			'district', //区 int
			'street', //街道 int
			'address', //地址 varchar
			'doornumber', //房号 varchar
			'longitude', //经度 decimal
			'latitude', //纬度 decimal
			//房源编号
			//出租类型
			//房源状态
		];

		return array_intersect_key($data, array_flip($verifyfield));

	}

	/**
	 * 计算完成进度
	 * @param $houseData 房源数据
	 * @return $schedule
	 */
	private function schedule($houseData) {

		$schedule = []; //完成进度

		//房源信息
		$schedule['basicinfo'] = [
			'state' => 0, //1完成0未完成
			'ratio' => 0, //完成比例%
			'num' => 5, //必填项目个数
			'ratiobyone' => intval(100 / 5), //每个必填项目所占百分比
		];

		if ($houseData['housearea'] > 0) {

			$schedule['basicinfo']['ratio'] += $schedule['basicinfo']['ratiobyone'];

		}

		if ($houseData['livingroom'] + $houseData['bedroom'] + $houseData['bathroom'] + $houseData['kitchen'] + $houseData['veranda'] > 0) {

			$schedule['basicinfo']['ratio'] += $schedule['basicinfo']['ratiobyone'];

		}

		if ($houseData['tenantnum'] > 0) {

			$schedule['basicinfo']['ratio'] += $schedule['basicinfo']['ratiobyone'];

		}

		if ($houseData['bedsheet'] > 0) {

			$schedule['basicinfo']['ratio'] += $schedule['basicinfo']['ratiobyone'];

		}

		if ($houseData['toilettype'] > 0) {

			$schedule['basicinfo']['ratio'] += $schedule['basicinfo']['ratiobyone'];

		}

		if ($schedule['basicinfo']['ratio'] == $schedule['basicinfo']['num'] * $schedule['basicinfo']['ratiobyone']) {

			$schedule['basicinfo']['state'] = 1;

		}

		//床铺信息
		$schedule['bedinfo'] = [
			'state' => 0, //1完成0未完成
			'ratio' => 0, //完成比例%
			'num' => 1, //必填项目个数
			'ratiobyone' => intval(100 / 1), //每个必填项目所占百分比
		];

		if (!empty(json_decode($houseData['bed'], true))) {

			$schedule['bedinfo']['ratio'] += $schedule['bedinfo']['ratiobyone'];

		}

		if ($schedule['bedinfo']['ratio'] == $schedule['bedinfo']['num'] * $schedule['bedinfo']['ratiobyone']) {

			$schedule['bedinfo']['state'] = 1;

		}

		//房源描述
		$schedule['describeinfo'] = [
			'state' => 0, //1完成0未完成
			'ratio' => 0, //完成比例%
			'num' => 3, //必填项目个数
			'ratiobyone' => intval(100 / 3), //每个必填项目所占百分比
		];

		if (!empty(trim($houseData['title']))) {

			$schedule['describeinfo']['ratio'] += $schedule['describeinfo']['ratiobyone'];

		}

		if (!empty(trim($houseData['description']))) {

			$schedule['describeinfo']['ratio'] += $schedule['describeinfo']['ratiobyone'];

		}

		if (!empty(trim($houseData['innerinfo']))) {

			$schedule['describeinfo']['ratio'] += $schedule['describeinfo']['ratiobyone'];

		}

		if ($schedule['describeinfo']['ratio'] == $schedule['describeinfo']['num'] * $schedule['describeinfo']['ratiobyone']) {

			$schedule['describeinfo']['state'] = 1;

		}

		//价格规则
		$schedule['priceinfo'] = [
			'state' => 0, //1完成0未完成
			'ratio' => 0, //完成比例%
			// 'num' => 3, //必填项目个数
			'num' => 2, //必填项目个数
			// 'ratiobyone' => intval(100 / 3), //每个必填项目所占百分比
			'ratiobyone' => intval(100 / 2), //每个必填项目所占百分比
		];

		if ($houseData['monthprice'] > 0 || $houseData['dayprice'] > 0) {

			$schedule['priceinfo']['ratio'] += $schedule['priceinfo']['ratiobyone'];

		}

		if ($houseData['cashpledge'] > 0) {

			$schedule['priceinfo']['ratio'] += $schedule['priceinfo']['ratiobyone'];

		}

		// if ($houseData['foreigner'] > 0) {

		// 	$schedule['priceinfo']['ratio'] += $schedule['priceinfo']['ratiobyone'];

		// }

		if ($schedule['priceinfo']['ratio'] == $schedule['priceinfo']['num'] * $schedule['priceinfo']['ratiobyone']) {

			$schedule['priceinfo']['state'] = 1;

		}

		//房源地址
		$schedule['mapinfo'] = [
			'state' => 0, //1完成0未完成
			'ratio' => 0, //完成比例%
			'num' => 6, //必填项目个数
			'ratiobyone' => intval(100 / 6), //每个必填项目所占百分比
		];

		//省
		if ($houseData['province'] > 0) {

			$schedule['mapinfo']['ratio'] += $schedule['mapinfo']['ratiobyone'];

		}

		//城市
		if ($houseData['city'] > 0) {

			$schedule['mapinfo']['ratio'] += $schedule['mapinfo']['ratiobyone'];

		}

		//区
		if ($houseData['district'] > 0) {

			$schedule['mapinfo']['ratio'] += $schedule['mapinfo']['ratiobyone'];

		}

		//地址
		if (!empty(trim($houseData['address']))) {

			$schedule['mapinfo']['ratio'] += $schedule['mapinfo']['ratiobyone'];

		}

		//经度
		if ($houseData['longitude'] > 0) {

			$schedule['mapinfo']['ratio'] += $schedule['mapinfo']['ratiobyone'];

		}

		//纬度
		if ($houseData['latitude'] > 0) {

			$schedule['mapinfo']['ratio'] += $schedule['mapinfo']['ratiobyone'];

		}

		if ($schedule['mapinfo']['ratio'] == $schedule['mapinfo']['num'] * $schedule['mapinfo']['ratiobyone']) {

			$schedule['mapinfo']['state'] = 1;

		}

		return $schedule;

	}

}
