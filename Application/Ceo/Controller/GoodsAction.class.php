<?php 
/*
	商品控制器
*/
class GoodsAction extends PublicAction{
		public function _initialize(){
			parent::_initialize();
			$_brands=M('goods_brand')->select();
			foreach($_brands as $val){
				$brands[$val['id']]=$val;
			}
			$_categorys=M('goods_category')->select();
			foreach($_categorys as $val){
				$categorys[$val['id']]=$val;
			}
			$this->assign('brands',$brands);
			$this->assign('categorys',$categorys);	
		}
		//商品列表
		public function index(){
			import("@.ORG.Page");
			$map=array();
			if($cid=I('get.cid')){
				$map['cid']=$cid;
			}
			if($bid=I('get.bid')){
				$map['bid']=$bid;
			}
			$db = M('goods');
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();    
		}
		//goods collection
		public function collection()
		{
			import("@.ORG.Page");
			$map=array();
			if($uid=I('get.uid')){
				$map['uid']=$uid;
			}

			$db = M('goods_collect');
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$list = $db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();    
		}
		/*
			分类id=>分类名称
			@params string 1,2,3
			@return string	a,b,c
		*/
		public function id2name($id_str){
			$id_arr=explode(',',$id_str);
			$db=M('goods_category');
			$catelist=$db->select();
			foreach($catelist as $key=>$val){
				$c_list[$val['id']]=$val['name'];
			}
			foreach($id_arr as $val){
				$arr_id[]=$c_list[$val];
			}
			$name_str=implode(',',$arr_id);
			return $name_str;
		}
		//添加商品
		public function add(){
			$db=M('goods');
			if($this->isPost()){
				$arr=$this->_post();
				$arr['posttime']=time();
				/*if(is_array($arr['cid'])){
					$arr['cid']=implode(',',$arr['cid']);
					$arr['cid']=$arr['cid'].',';
				}*/
				$id=$db->data($arr)->add();
				$this->success('发布成功',U('index'));
			}else{
				$this->display();
			}
			
		}
		//编辑商品
		public function edit(){
			$db=M('goods');
			$id=I('get.id');
			$info=$db->find($id);
			$this->assign('info',$info);
			if($this->isPost()){
				$map=array('id'=>$id);
				$arr=$this->_post();
				if(!$arr['is_tui']){$arr['is_tui']=0;}
				if(!$arr['is_hot']){$arr['is_hot']=0;}
				if(!$arr['is_active']){$arr['is_active']=0;}
				/*if(is_array($arr['cid'])){
					$arr['cid']=implode(',',$arr['cid']);
					$arr['cid']=$arr['cid'].',';
				}*/
				$db->where($map)->data($arr)->save();
				$this->success('操作成功',U('index'));
			}else{
				$this->display();
			}
			
		}
		//删除商品
		public function del(){
			M('goods')->delete(I('get.id'));
			$this->redirect('index');
		}
		
		/*
			图片预览
		*/
		public function show_img(){
			$picurl=I('get.picurl','','base64_decode');
			echo "<img src='$picurl' style='width:500px'/>";
		}
		
		/*
			快速上下架
		*/
		public function up2down(){
			$db=M('goods');
			$id=I('get.id');
			$info=$db->find($id);
			if($info['is_sale']==1){
				$data['is_sale']=2;
			}else{
				$data['is_sale']=1;
			}
			$db->where(array('id'=>$id))->save($data);
			$this->success('操作成功！');
		}
		
}
