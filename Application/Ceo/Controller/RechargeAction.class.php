<?php
/*
	充值金额配置
*/
class RechargeAction extends PublicAction{
	/*
		充值金额配置列表
	*/
	public function index(){
		import("@.ORG.Page");
		$db=M('recharge_config');
		$map=array();
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		$show = $Page->show();
		$this->assign('show',$show);
		
		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	/*
		新增充值金额配置
	*/
	public function add(){
		$db=M('recharge_config');
		if($arr=$this->_post()){
			$db->add($arr);
			$this->redirect('index');
		}
		$this->display();
	}
	/*
		编辑充值金额配置
	*/
	public function edit(){
		$db=M('recharge_config');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			$this->redirect('index');
		}
		$this->display();
	}
	/*
		删除充值金额配置
	*/
	public function del(){
		$id=I('get.id');
		M('recharge_config')->delete($id);
		$this->redirect('index');
	}
	
	/*
		订单列表
	*/
	public function order_list(){
		import("@.ORG.Page");
		$db=M('jifen_order');
		
		$map=array();
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		$show = $Page->show();
		$this->assign('show',$show);
		
		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	/*
		订单详情
	*/
	public function order_edit(){
		$db=M('jifen_order');
		$id=I('get.id');
		$order=$db->find($id);
		$goods=M('jifen_order_goods')->where(array('order_id'=>$id))->select();
		$this->assign('data',$order);
		$this->assign('goods',$goods);
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			$this->success('操作成功',U('order_list'));
		}else{
			$this->display();
		}
		
	}
	/*
		订单删除
	*/
	public function order_del(){
		$id=I('get.id');
		if(M('jifen_order')->delete($id)){
			M('jifen_order_goods')->where(array('order_id'=>$id))->delete();
			$this->success('删除成功',U('order_list'));
		}
		
	}
	/*
		快速上下架
	*/
	public function up2down(){
		$db=M('recharge_config');
		$id=I('get.id');
		$info=$db->find($id);
		if($info['is_sale']==1){
			$data['is_sale']=2;
		}else{
			$data['is_sale']=1;
		}
		$db->where(array('id'=>$id))->save($data);
		//$this->success('操作成功！');
		$this->redirect('index');
	}
}
