<?php
//命名空间
namespace app\home\controller;
use houdunwang\core\Controller;
use houdunwang\view\View;
use system\model\Article;
class Entry extends Controller {
//    定义一个方法
    public function index(){
        $data=['name'=>'哈哈1'];
      p(Article::where("aid=2")->insert($data)) ;
//    Article::where("name = '谢飞'")->getAll()->toArray();
//        $text='houdunwang';
//        return View::with(compact('text'))->make();
    }
    public function add(){
        echo '333333333';
    }
}