<?php
namespace Ceo\Controller;
use Think\Controller;
class PublicController extends Controller {
	//判断用户是否登录
	public function _initialize()
	{
		header("Content-type: text/html; charset=utf-8");
		if( ! isset($_SESSION[C('USER_AUTH_KEY')]) ) {
			$this->redirect('Ceo/Login/index');
		}
	}
	
	/*
		图片预览
	*/
	public function show_img()
	{
		$picurl=I('get.picurl','','base64_decode');
		echo "<img src='$picurl' style='width:500px'/>";
	}
	
}