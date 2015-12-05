<?php
/*
	热门城市管理
 */
class HotcityAction extends PublicAction{

	public function _initialize(){
		header("content-type:text/html;charset=utf-8");
		parent::_initialize();
		//城市列表
		$map=array('region_type'=>2);
		$keyword=I('get.keyword');
		if(!empty($keyword)){
			$map['region_name']=array('like','%'.$keyword.'%');
		}
		$city_list=M('region')->where($map)->order('first_spell')->select();
		$this->assign('city_list',$city_list);
	}
	/*
		热门城市列表
	 */	
	public function index(){
		import("@.ORG.Page");
		$map=array();
		$db=M('hot_city');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();

		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}

	/*
		新增热门城市
	 */
	public function add(){
		if($this->_post()){
			$data['region_id']=I('post.region_id');
			//check is exist
			$city = M('hot_city')->where(array('region_id'=>$data['region_id']))->find();
			if($city)
			{
				$this->error('该城市已存在！');	
			}
			if(empty($data['region_id'])){
				$this->error('请选择城市');	
			}else{
				$city_info=M('region')->find($data['region_id']);
				$data['name']=$city_info['region_name'];
				M('hot_city')->add($data);
				$this->redirect('index');
			}
		}else{
			$this->display();
		}

	}

	/*
		编辑热门城市信息
	 */
	public function edit(){
		$id=I('get.id');
		$info=M('hot_city')->find($id);
		$this->assign('info',$info);

		if($data=$this->_post()){
			$w['id']=I('get.id');

			if(empty($data['region_id'])){
				$this->error('请选择城市');	
			}else{
				$city_info=M('region')->find($data['region_id']);
				$data['name']=$city_info['region_name'];

				M('hot_city')->where($w)->save($data);
				$this->redirect('index');
			}
		}else{
			$this->display();
		}
	}
	/*
		删除热门城市
	 */
	public function del(){
		$id=I('get.id');
		if(M('hot_city')->delete($id)){
			$this->redirect('index');
		}
	}



	//change county sort
	public function county()
	{
		//county list
		$map=array('region_type'=>3);
		$keyword=I('get.county');
		if(!empty($keyword)){
			$map['region_name']=array('like','%'.$keyword.'%');

			//var_dump($map);
			//
			$county_list=M('region')->where($map)->order('sort asc')->select();

			foreach($county_list as $k=>$v)
			{
				$parent = M('region')->where()->find($v['parent_id']);
				$county_list[$k]['city'] = $parent['region_name'];
			}
			//var_dump($county_list);
			$this->assign('county_list',$county_list);

		}


		if($this->_post()){
			$data['region_id']=I('post.region_id');
			$sort = I('post.sort');
			if(empty($data['region_id'])){
				$this->error('请选择区县');	
			}else{
				M('region')->where(array('id'=>$data['region_id']))->save(array('sort'=>$sort));
				$this->redirect('index');
			}
		}

		$this->display();
	}

}
