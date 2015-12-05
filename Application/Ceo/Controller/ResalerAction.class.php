<?php
//分销商管理
class ResalerAction extends PublicAction{
	
	private $pubwechat;	//公众号信息

	public function _initialize(){
		parent::_initialize();
		$this->pubwechat=M('wechat_config')->find(1);
		//会员等级
		$_user_level=M('user_level')->select();
		foreach($_user_level as $val){
			$user_level[$val['id']]=$val;
		}
		$this->assign('user_level',$user_level);
	}
	/*
		分销商列表
	*/	
	public function index(){
		import("@.ORG.Page");
		$map=array();
		
		//$map['role_id']=3;				//分销商
		
			$so_key=I('post.key');
			$where['nickname']  = array('like', '%'.$so_key.'%');
			$where['shop_name']  = array('like','%'.$so_key.'%');
			$where['username']  = array('like','%'.$so_key.'%');
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
			$map['role_id']  = array('eq',3);	//分销商
			
		$db=M('wechat_user');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($list AS $k=>$val){
				$list[$k]['invite_num']=M("wechat_user")->where(array('parent_id'=>$val['id']))->count();
				//$data[$k]['hdrenshu']=M("kanjiadata")->where(array('tid'=>$val['id']))->count();
			}
			
			//foreach ($list as $user) {
			//$invite_num[] = $user['invite_num'];
			//}
		/*$groups=F('wx_groups');
		foreach($wxuser as $key=>$val){
			$wxuser[$key]['group_name']=$groups[$val['group_id']]['name'];
		}*/
		$show = $Page->show();
		//array_multisort($invite_num, SORT_DESC, $list);
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	
		/*
		分销商列表
	*/	
	public function top100(){
		$map=array();
		
		$map['role_id']=3;				//分销商
		$db=M('wechat_user');
		$list=$db->where($map)->order('id DESC')->select();
		foreach ($list AS $k=>$val){
				$list[$k]['invite_num']=M("wechat_user")->where(array('parent_id'=>$val['id']))->count();
				//$data[$k]['hdrenshu']=M("kanjiadata")->where(array('tid'=>$val['id']))->count();
			}
			
			foreach ($list as $user) {
			$invite_num[] = $user['invite_num'];
			}

		array_multisort($invite_num, SORT_DESC, $list);
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		分销商邀请店铺列表
	*/	
	public function store(){
		import("@.ORG.Page");
		$map=array();
		
		$map['role_id']=2;				//店铺
		$map['parent_id']=I('get.id');
		$db=M('wechat_user');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	/*
		待审核分销商
	*/
	public function audit(){
		import("@.ORG.Page");
		$map=array();
		$map['status']=0;
		$map['role_id']=3;				//分销商
		
		$db=M('wechat_user');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	/*
		通过审核
	*/
	public function pass_audit(){
		$id=I('get.id');
		M('wechat_user')->where(array('id'=>$id))->save(array('status'=>1));
		$this->success('审核成功',U('index'));
	}
	/*
		取消分销商
	*/
	public function cancel_resaler(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->find($id);
		if(!empty($info['shop_name'])){
			M('wechat_user')->where(array('id'=>$id))->save(array('role_id'=>2));			//微店店主
		}else{
			M('wechat_user')->where(array('id'=>$id))->save(array('role_id'=>1));			//普通会员
		}
		$this->success('取消成功',U('index')); 
	}
	/*
		编辑分销商信息
	*/
	public function edit(){
		$id=I('get.id');
		$info=M('wechat_user')->find($id);
		$this->assign('info',$info);
		if($data=$this->_post()){
			$w['id']=I('get.id');
			M('wechat_user')->where($w)->save($data);
			$this->success('保存成功',U('edit',array('id'=>$w['id'])));
		}else{
			$this->display();
		}
	}
	/*
		删除分销商
	*/
	public function del(){
		$user_id=I('get.id');
		if(M('wechat_user')->delete($user_id)){
			$this->success('操作成功！');
		}
	}
	
}