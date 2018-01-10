<?php
namespace app\index2\controller;

class Index
{
    public function index()
    {
        $asd = db('test1')->where('id',2)->select();
        var_dump($asd);
    }
}
