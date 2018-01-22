<?php
namespace app\mite\model;

use think\Model;

class House extends Model
{
	/**
	 * 获得房子列表
	 * @param  string  $keyword [description]
	 * @param  string  $filter  [description]
	 * @param  string  $order   [description]
	 * @param  integer $lat     [description]
	 * @param  integer $lng     [description]
	 * @return [type]           [description]
	 */
	public static function getHouseList($keyword = '', $filter = '', $order = '', $limit = 10,$lat = 0 , $lng = 0)
	{
		
	}
}