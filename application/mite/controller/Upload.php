<?php
namespace app\mite\controller;

use think\Controller;

class Upload extends Controller {

	public function __construct() {

		parent::__construct();

		$this->userid = intval(session('ss_userid'));

		if (!$this->userid) {

			$this->error('请在微信端访问');

		}

	}

	//房源图片上传
	public function houseimageupload() {

		if (!input('?houseid')) {

			$this->error('参数错误001', 'Proprietor/issued'); //参数houseid缺失

		}

		$houseid = input('houseid');
		$this->assign('houseid', $houseid);
		return $this->fetch();

	}

	/**
	 * 上传文件类型控制 此方法仅限ajax上传使用
	 * @param  integer  $maxSize 允许的上传文件最大值 52428800(50MB)
	 * @return booler   返回ajax的json格式数据
	 */
	public function ajax_upload($maxSize = '52428800') {

		ini_set('max_execution_time', '0');

		$userid = $this->userid;

		$requestData = input('param.'); // 当前请求的变量

		$data['state'] = 0; // $data: Ajax返回数据

		// 获取不到houseid,返回错误信息
		if (!(isset($requestData['houseid']) && !empty($requestData['houseid']))) {

			$data['error_info'] = '获取不到houseid';
			return new \think\response\Json($data);

		}

		$houseid = $requestData['houseid']; // 房源id

		// 获取表单上传文件
		$file = request()->file('file');

		$path = INDEX_PATH . 'miteuploads';

		// 移动到框架应用根目录$path目录下
		$info = $file->validate(['size' => $maxSize, 'ext' => 'jpg,png,gif'])->move($path);

		// $data = [input('param.'), $info, $info->getExtension(), $info->getSaveName(), $info->getFilename()];
		// echo json_encode($data);
		// return;

		if (!$info) {

			// 上传失败获取错误信息,返回错误信息
			$error = $file->getError();
			$data['error_info'] = $error;
			return new \think\response\Json($data);


		} else {

			//旋转图像,还没测试！！！！！！！！！先屏蔽
			if (input('?degrees')) {

				$degrees = input('degrees');

				if ($degrees % 360 != 0) {

					$imgfilename = $path . '/' . $info->getSaveName();
					$this->rotate($imgfilename, $degrees); //以$degrees角度旋转图像

				}

			}

			// // 成功上传后 获取上传信息
			// // 输出 jpg
			// echo $info->getExtension();
			// // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
			// echo $info->getSaveName();
			// // 输出 42a79759f284b767dfcb2a0197904287.jpg
			// echo $info->getFilename();

			$insData = [
				'houseid' => $houseid,
				'userid' => $userid,
				'type' => 1, //默认类型
				'title' => $info->getFilename(),
				'url' => $info->getSaveName(),
				'addtime' => date('Y-m-d H:i:s', time()),
				'status' => 2, //等待审核
			];

			$imageid = db('HouseImage')->insertGetId($insData);

			if (!$imageid) {

				$data['error_info'] = '上传失败';

			} else {

				$data['state'] = 1;
				$data['name'] = $info->getSaveName();

			}

			return new \think\response\Json($data);

		}

	}

	/**
	 * 用给定角度旋转图像
	 * @param  $filename 图片路径
	 * @param  $degrees  角度
	 */
	public function rotate($filename, $degrees) {

		$ext = pathinfo($filename)['extension'];

		switch ($ext) {

			case 'gif':
				$img = imagecreatefromgif($filename);
				break;

			case 'jpg':
				$img = imagecreatefromjpeg($filename);
				break;

			case 'jpeg':
				$img = imagecreatefromjpeg($filename);
				break;

			case 'png':
				$img = imagecreatefrompng($filename);
				break;

			default:
				die('图片格式错误!');
				break;

		}

		//创建图像资源
		$source = imagecreatefromjpeg($filename);

		//使用imagerotate()函数按指定的角度旋转
		$rotate = imagerotate($source, $degrees, 0);

		//旋转后的图片保存
		switch ($ext) {

			case 'gif':
				imagegif($rotate, $filename);
				break;

			case 'jpg':
				imagejpeg($rotate, $filename);
				break;

			case 'jpeg':
				imagejpeg($rotate, $filename);
				break;

			case 'png':
				imagepng($rotate, $filename);
				break;

			default:
				die('图片格式错误!');
				break;
		}

	}

}
