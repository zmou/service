<?php
//系统设置
class WechatPubAction extends PublicAction{
	private $pubwechat;
	public function _initialize(){
		parent::_initialize();
		$this->wechatid=I('get.wechatid');
		$this->pubwechat=M('wechat_config')->find(1);	//公众号信息
	}
	public function index(){
		$con=M('wechat_config')->find(1);
		$this->assign('conf',$con);
		$this->display();
	}
	public function confHandle(){
		M('wechat_config')->where('id=1')->save($_POST);
		$wx_config=M('wechat_config')->find(1);
		F('wx_config',$wx_config);
		$this->success('设置成功！');
	}
	public function get_wx_ip(){
		import('@.ORG.Wxhelper');
		$helper=new Wxhelper($this->pubwechat);
		$return=$helper->get_wx_ip();
		foreach($return['ip_list'] as $val){
			$ip_str.=$val.'<br/>';
		}
		echo "<h5>微信服务器IP地址：<h5/><hr/>".$ip_str;
	}
}