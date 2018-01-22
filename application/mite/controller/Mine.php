<?php
namespace app\mite\controller;

use think\Controller;

class Mine extends Controller {

	public function __construct() {

		parent::__construct();

		$this->userid = intval(session('ss_userid'));

		if (!$this->userid) {

			$this->error('请在微信端访问');

		}

	}

	public function index() {

		return $this->fetch();

	}

	public function usercheckin() {

		return $this->fetch();

	}

	public function liuliuindex() {

		return $this->fetch();

	}
}
