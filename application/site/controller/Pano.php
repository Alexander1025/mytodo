<?php
namespace app\site\controller;

use think\Controller;

class Pano extends Controller {

	public function data($id = 1001, $angle = null) {
		$id = intval($id);

		$scene = db()->table("pano_scene")->find($id);
		if (is_null($scene)) {
			return "";
		} else {
			if (is_null($angle)) {
				$angle = $scene['angle'];
			}
			$others = db()->table("pano_scene")->where("place_id", $scene['place_id'])->select();
			$place = db()->table("pano_place")->find($scene['place_id']);

			$data = array();
			$data[$scene['scene_id']] = $scene;
			foreach ($others as $key => $item) {
				if ($item['scene_id'] != $scene['scene_id']) {
					$data[$item['scene_id']] = $item;
				}
			}

			foreach ($data as $key => $item) {
				$roam = db()->table("pano_roam")->where("scene_id", $item['scene_id'])->select();

				$n = count($roam);
				for ($i = 0; $i < $n; $i++) {
					$target_id = $roam[$i]['target_id'];
					$roam[$i]['target_img'] = isset($data[$target_id]) ? $data[$target_id]['photo_id'] : "";
					$roam[$i]['text'] = isset($data[$target_id]) ? $data[$target_id]['name'] : "";
				}

				$data[$key]['roam'] = $roam;
			}

			$this->assign("data", $data);

			return $this->fetch();
		}
	}

	public function view($id = 1001) {
		$id = intval($id);

		$place = db()->table("pano_place")->find($id);
		if (is_null($place)) {
			$scene = db()->table("pano_scene")->find($id);
		} else {
			$scene = db()->table("pano_scene")->find($place['scene_id']);
		}
		if (is_null($scene)) {
			return "";
		} else {
			$place = db()->table("pano_place")->find($scene['place_id']);
			$data = $scene;
			$data['place_name'] = $place['name'];
			$this->assign("data", $data);

			$url = url("data", ["id" => $data['scene_id']], "xml");
			config("url_html_suffix", "");
			$this->redirect("/pano/tour.html?xml=$url");
		}

	}

	public function index() {
		$data = db()->table("pano_place")->select();
		$this->assign("data", $data);
		return $this->fetch();
	}

}
