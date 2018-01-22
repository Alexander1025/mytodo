<?php
namespace app\mite\controller;

use think\Controller;

class Index extends Controller {

	public function __construct() {

		parent::__construct();

		$this->userid = intval(session('ss_userid'));

		// if (!$this->userid) {

		// 	$this->error('请在微信端访问');

		// }

	}

	//登陆(登陆)
	public function login() {

		return $this->fetch();

	}

	//首页(房源展示)
	public function index() {

		return $this->fetch();

	}

	//首页(房源展示)
	public function liuliuindex() {

		return $this->fetch();

	}

	//地图找房
	public function map() {

		return $this->fetch();

	}
	
	//首页(房源展示)
	public function detail() {

		if (!input('?houseid')) {

			$this->error('参数houseid缺失', 'Index/index'); //参数houseid缺失

		}

		$houseid = input('houseid');
		// $userid = $this->userid;

		//条件
		$conditions = [
			'houseid' => $houseid,
			'status' => 1, //正常状态的房源
		];

		//房源详情
		$housedetail = db('House')->where($conditions)->find();

		if (empty($housedetail)) {

			$this->error('找不到该房源', 'Index/index'); //housedetail为空

		}

		//查找所有配套设施
		$optionsList = db('HouseOption')->where('status=1')->order('ord')->select();

		//添加、更新浏览记录(登陆了才可以进行一下操作!!!!!!)
		if($this->userid){

			$userid = $this->userid;

			$pageViewCount = db('HousePageView')->where(['houseid' => $houseid, 'userid' => $userid])->count();

			if (!$pageViewCount) {

				//添加
				$insViewData = [
					'userid' => $userid,
					'houseid' => $houseid,
					'viewtime' => date('Y-m-d H:i:s'),
				];

				$viewid = db('HousePageView')->insertGetId($insViewData);

			} else {

				//更新浏览时间
				$viewConditions = [
					'userid' => $userid,
					'houseid' => $houseid,
				];

				$updViewData = [
					'viewtime' => date('Y-m-d H:i:s'),
				];

				$viewUpdBool = db('HousePageView')->where($viewConditions)->setField($updViewData);

			}

		}

		//城市名称
		$cityNameArr = db('Address')->where(['id' => $housedetail['city']])->find();
		$housedetail['cityName'] = isset($cityNameArr['name']) ? $cityNameArr['name'] : '';

		//床铺信息
		$housedetail['bedArr'] = json_decode($housedetail['bed'], true);

		//服务设施
		$housedetail['optionsArr'] = json_decode($housedetail['options'], true);

		//房源照片数据
		$housedetail['imageData'] = $this->getimagebyhouseid($houseid);

		$placeid = intval($housedetail['place_id']);

		if($placeid>0){

			$housedetail['3Ddata'] = $this->panodata($placeid);

		}


		//可租日期..........有待完善
		//...........   筛选出已租的时间，返回格式，如2011.11.11，只查一个月内的，全部查数据量太大

		$this->assign('housedetail', $housedetail); //房源详情
		$this->assign('optionsList', $optionsList); //所有的配套设施

		return $this->fetch();
		// echo '<pre>';var_dump($optionsList);exit;

	}

	/**
	 * 房源信息滑动分页(ajax)
	 */
	public function houseslidepage() {

		$post = input('post.');

		// if (!isset($post['ajaxPage'])) {

		// 	return new \think\response\Json(['state' => 0, 'info' => '参数缺失']); //无法获取页码

		// }

		//页码
		$page = isset($post['ajaxPage']) ? intval($post['ajaxPage']) : 1; //参数不存在默认1
		$page > 0 || $page = 1;

		//限制字段
		$field = '*';

		//条件
		$conditions = [
			'status' => 1, //正常状态的房源
		];

		//如果存在title条件
		if (isset($post['title'])) {

			$title = trim($post['title']);

			if (!empty($title)) {

				$conditions['title'] = ['like', '%' . $title . '%'];

			}

		}

		//条件过滤

		//排序
		$order = 'houseid';

		//限制
		$limit = isset($post['limit']) ? intval($post['limit']) : 6; //限制条数
		$limit > 0 || $limit = 1;
		$start_limit = ($page - 1) * $limit; //从$start_limit行开始

		//获取房源数据
		$list = db('House')->field($field)->where($conditions)->order($order)->limit($start_limit, $limit)->select();

		//返回数据
		$return = [
			'state' => 1, //状态
			'info' => '加载成功', //信息
		];

		if (empty($list)) {

			$return['info'] = '没有更多了'; //没有更多了

		} else {

			$houseids = array_column($list, 'houseid');
			$houseImageData = $this->getimagebyhouseid($houseids, true); //房源照片数据

			//将houseImageData合并到list中
			foreach ($list as $k_l => $v_l) {

				$list[$k_l]['imageData'] = [];

				$_key1 = $v_l['houseid'];

				isset($houseImageData[$_key1]) && $list[$k_l]['imageData'] = $houseImageData[$_key1];

			}

		}

		$return['list'] = $list;

		return new \think\response\Json($return);
		// echo '<pre>';var_dump($list);exit;

	}

	/**
	 * 获取房源照片信息
	 * @param $houseids 房源id
	 * @param $trans (true/false)
	 * @return 房源照片数据
	 */
	protected function getimagebyhouseid($houseids, $trans = false) {

		if (empty($houseids)) {

			return []; //$houseids为空，返回空数组

		}

		$conditions = [
			'houseid' => $houseids,
			'status' => [
				'NEQ',
				0,
			], //排除已删除的图片
		];

		if (is_array($houseids)) {

			$conditions['houseid'] = [
				'IN',
				array_values($houseids),
			];

		}

		$imageData = db('HouseImage')->where($conditions)->select(); //房源图片 status:1有效 2等待审核 0被删除

		!$imageData && $imageData = [];

		if ($trans) {

			$ret = [];

			foreach ($imageData as $value) {

				$_key = $value['houseid'];

				isset($ret[$_key]) || $ret[$_key] = [];

				$ret[$_key][] = $value;

			}

			return $ret;

		} else {

			return $imageData;

		}

	}

	/**
	 * 房源全景信息
	 * @param $id place_id
	 * @return $data 房源全景数据
	 */
	public function panodata($id) {

		$id = intval($id);

		$data = [
			'place' => [], //场地数据
			'scene' => [], //场景数据
		];

		if ($id > 1000) {

			$scene = db()->table("pano_scene")->find($id);
			$data['scene'][] = $scene;

		} else {

			$place = db()->table("pano_place")->find($id);

			if (!is_null($place)) {

				$data['place'] = $place;
				$scene = db()->table("pano_scene")->where(['place_id' => $place['place_id']])->select();
				$data['scene'] = $scene;

			}

		}

		return $data;

	}

	/**
	 * 房源收藏操作
	 */
	public function housecollect() {

		$userid = $this->userid;

		if (!input('?houseid')) {

			return new \think\response\Json(['state' => 0, 'info' => "参数houseid缺失"]);

		}

		$houseid = input('houseid');

		$pageViewCount = db('HousePageView')->where(['houseid' => $houseid, 'userid' => $userid])->count();

		if (!$pageViewCount) {

			//添加记录
			$insViewData = [
				'userid' => $userid,
				'houseid' => $houseid,
				'collect' => 1,
			];

			$viewid = db('HousePageView')->insertGetId($insViewData);

			if (!$viewid) {

				return new \think\response\Json(['state' => 0, 'info' => "收藏失败"]);

			}

		} else {

			//修改记录
			$viewConditions = [
				'userid' => $userid,
				'houseid' => $houseid,
			];

			$updViewData = [
				'collect' => 1,
			];

			$viewUpdBool = db('HousePageView')->where($viewConditions)->setField($updViewData);
			if ($viewUpdBool === false) {

				return new \think\response\Json(['state' => 0, 'info' => "收藏失败"]);

			}

		}

		return new \think\response\Json(['state' => 1, 'info' => "收藏成功"]);

	}

}
