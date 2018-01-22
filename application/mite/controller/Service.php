<?php
namespace app\mite\controller;

use think\Controller;

class Service extends Controller {

	public function assemble(&$result) {
		$n = count($result);
		for ($i = 0; $i < $n; $i++) {
			$result[$i]['id'] = intval($result[$i]['id']);
			$result[$i]['longitude'] = floatval($result[$i]['longitude']);
			$result[$i]['latitude'] = floatval($result[$i]['latitude']);
			$result[$i]['level'] = intval($result[$i]['level']);
		}
		return $result;
	}

	public function data($id = 0) {
		$model = db("address");
		$data = $model->field("id,name,longitude,latitude,level")->where("parentid", $id)->select();
		return json($this->assemble($data));
	}

	public function country() {
		$model = db("addr_country");
		$data = $model->field("id,name,longitude,latitude,scale as level")->select();
		$n = count($data);
		for ($i = 0; $i < $n; $i++) {
			$data[$i]['level'] = 4;
		}
		return json($this->assemble($data));
	}

	public function province($id = 0) {
		$model = db("addr_mayi1");
		$data = $model->field("id,name,longitude,latitude,level")->where("parentid", $id)->select();
		return json($this->assemble($data));
	}

	public function city($id = 0) {
		$model = db("addr_mayi2");
		$data = $model->field("id,name,longitude,latitude,level")->where("parentid", $id)->select();
		return json($this->assemble($data));
	}

	public function district($id = 0) {
		$model = db("addr_mayi3");
		$data = $model->field("id,name,longitude,latitude,level")->where("parentid", $id)->select();
		return json($this->assemble($data));
	}

	public function street($id = 0) {
		$model = db("addr_mayi4");
		$data = $model->field("id,name,longitude,latitude,level")->where("parentid", $id)->select();
		return json($this->assemble($data));
	}

}
