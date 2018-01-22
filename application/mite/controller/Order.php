<?php
namespace app\mite\controller;

use think\Controller;

class Order extends Controller {

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

	public function information() {

		return $this->fetch();

	}
}
