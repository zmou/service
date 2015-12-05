<?php
/*
	用户反馈
*/
class FeedbackAction extends PublicAction{
	public $db;
	public function __construct() {
		parent::__construct();
		$this->db=M("feedback");
	}
	//反馈列表
	public function index(){
		import("@.ORG.Page");
		$count =$this->db->count();
		$Page = new Page($count,20);	
		$pagestr = $Page->show();
		$this->assign('show',$show);
		$list=$this->db->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select(); 
		foreach($list as $key=>$val){
			$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->field('nickname,name')->find();
		}
		$this->assign('list',$list);
		$this->display();
	}
		
    //反馈回复
	public function edit(){
		$id=I('get.id');
		$data=$this->db->where("id=$id")->find();
		$this->assign('info',$info);
		$this->display();
	}
	 //反馈回复
	public function del(){
		$id=I('get.id');
		if($this->db->delete($id)){
			$this->redirect('index');
		}
	}
	
}