<?php 
/*
	导航管理
*/
class NavlinkAction extends PublicAction{
		public function _initialize(){
			parent::_initialize();
			$navlist=M('navlink')->where(array('fup'=>0))->select();
			$this->assign('navlist',$navlist);
			//前台链接地址
			$url=array(
				array('title'=>'商城首页','url'=>U('Weixin/Index/index')),
				array('title'=>'商品列表','url'=>U('Weixin/Index/product_list')),
				array('title'=>'商品分类','url'=>U('Weixin/Index/category')),
				array('title'=>'购物车','url'=>U('Weixin/Index/cart')),
				array('title'=>'注册','url'=>U('Weixin/Member/reg')),
				array('title'=>'我要开店','url'=>U('Weixin/Member/shop_reg')),
				array('title'=>'登录','url'=>U('Weixin/Member/login')),
				array('title'=>'开店','url'=>U('Weixin/Member/shop_reg')),
				array('title'=>'我的店铺','url'=>U('Weixin/Index/shop_index')),
				array('title'=>'个人中心','url'=>U('Weixin/Ucenter/index')),
				array('title'=>'我的订单','url'=>U('Weixin/Ucenter/order_list')),
				array('title'=>'积分订单','url'=>U('Weixin/Ucenter/jifen_order')),
				array('title'=>'销售订单','url'=>U('Weixin/Ucenter/sale_order')),
				array('title'=>'店铺管理','url'=>U('Weixin/Ucenter/shop_config')),
				array('title'=>'资金账户','url'=>U('Weixin/Ucenter/fund')),
				array('title'=>'积分账户','url'=>U('Weixin/Ucenter/jifen')),
			);
			//产品分类列表
			$cate=M('goods_category')->field('id,name')->select();
			foreach($cate as $val){
				$cate_nav[]=array('title'=>$val['name'],'url'=>U('Weixin/Index/product_list',array('id'=>$val['id'])));
			}
			$all_url=array_merge($cate_nav,$url);
			$this->assign('url',$all_url);
			
			//文章列表
			$cms_list=M('cms_sort')->field('id,name')->select();
			foreach($cms_list as $key=>$val){
				$art=M('cms_article')->field('id,title')->where(array('fid'=>$val['id']))->select();
				foreach($art as $k=>$v){
					$art[$k]['url']=U('Weixin/Cms/read',array('id'=>$v['id']));
				}
				$cms_list[$key]['art']=$art;
				unset($art);
			}
			$this->assign('cms_list',$cms_list);
			//dump($cms_list);die();
		}
		//导航列表
		public function index(){
			import("@.ORG.Page");
			$map=array();
			
			$db = M('navlink');
			
			$map=array('fup'=>0);
			
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['child']=M('navlink')->where(array('fup'=>$val['id']))->select();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();   
			//dump($list); 
			
		}
		
		//添加商品
		public function add(){
			$db=M('navlink');
			if($arr=$this->_post()){
				$arr['cid']=1;
				$id=$db->data($arr)->add();
				$this->success('保存成功',U('index'));
			}else{
				$this->display();
			}
			
		}
		//编辑商品
		public function edit(){
			$db = M('navlink');
			$id=I('get.id');
			$map=array('id'=>$id);
			$info=$db->find($id);
			$this->assign('info',$info);
			if($arr=$this->_post()){
				$arr['cid']=1;
				$db->where($map)->data($arr)->save();
				$this->success('保存成功',U('index'));
			}else{
				$this->display();
			}
			
		}
		//删除商品
		public function del(){
			M('navlink')->delete(I('get.id'));
			$this->redirect('index');
		}
}