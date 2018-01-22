<?php
return array(
	// 视图输出字符串内容替换
	'view_replace_str' => [
		'__PUBLIC__' => __PUBLIC__,
		'__MODULE__' => "/" . \think\Request::instance()->module(),
	],
);
