<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->display();
    }

    public function test() {
        $this->display();
    }

    public function ajaxGetData()
    {
        $data = array(
            'eg',
            'fweg',
            'yukyuk',
            'wdqcs',
            'entyjtyg'
        );
        $this->assign('data',$data);
        $this->display('Inc:list');
    }

    public function ajaxGet2()
    {
        $this->display('Inc:test2');
    }


}