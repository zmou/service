<?php
namespace Ceo\Controller;
use Think\Controller;
//后台登录控制
class LoginController extends Controller{
	//后台登录模板输出
	public function index(){
		$this->display('login');
	}
	
	//登录方法
	public function login()
	{
		$username = $_POST['username'];
		$pwd      = md5($_POST['password']);
		
		$i = M('school_ceo')->where(array('username'=>$username))->find();
		if($i['password'] != $pwd) {
			$this->error('用户名或者密码错误！');
		}
		if($i['is_freeze'] == 1){
			$this->error('非常抱歉！您的账户已被冻结！');
		}
		
		$data = array(
			'id'         => $i['id'],
			'login_time' => date("Y-m-d H:i:s", time()),
			'login_ip'   => get_client_ip()
		);
		M('school_ceo')->save($data);
		
		session(C('USER_AUTH_KEY'),$i['id']);
		session('userinfo',$i);
		session('uid',$i['id']);
		session('sch_id',$i['school_id']);
		
		$this->redirect('Ceo/Index/index');
	}
	//登录日志
	public function login_log($arr){
		$db=M('admin_action_log');
		$arr['descript']='登录后台';
		$arr['posttime']=time();
		$db->add($arr);
	}
	//退出登录
	public function logout(){
		session(C('USER_AUTH_KEY'),null);
		session('username',null);
		//session_unset();
		//session_destroy();
		$this->redirect('Login/index');
	}
	//引入验证码
	public function yzm(){
		import('ORG.Util.Image');
		Image::buildImageVerify(4,1,'png',80,32);
	}
	
}