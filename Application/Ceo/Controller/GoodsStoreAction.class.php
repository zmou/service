<?php
// 商品库存管理
class GoodsStoreAction extends PublicAction{	

	public function _initialize(){
		parent::_initialize();
		//仓库列表
		$storage=M('storage')->select();
		$this->assign('storage',$storage);
		
		//商品列表
		$goods_list=M('goods')->select();
		$this->assign('goods_list',$goods_list);
	}
	/*
		商品库存列表
	*/
	public function index(){
		import("@.ORG.Page");
		$db=M('goods_store');
		
		$count = $db->count();
		$Page = new Page($count,10);
		$show = $Page->show();
		$this->assign('show',$show);
		
		$list = $db->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->group('goods_id')->select();
		
		foreach($list as $key=>$val){
			$info=M('goods')->field('id,name,spic')->find($val['goods_id']);
			$list[$key]['goods']=$info;
		}
		
		
		$this->assign('list',$list);
		$this->display();
	}
	/*
		新增
	*/
	public function add(){
		$db=M('goods_store');
		//商品列表
		$goods_list=M('goods')->select();
		$this->assign('goods_list',$goods_list);
		if($arr=$this->_post()){
			$g_info=M('goods')->where(array('id'=>$arr['goods_id']))->find();
			//商品编号
			$arr['goods_code']=$g_info['goods_code'];
			//商品名称
			$arr['goods_name']=$g_info['name'];
			//仓库名称
			$arr['storage']=M('storage')->where(array('id'=>$arr['storage_id']))->getField('name');
			$db->add($arr);
			$this->redirect('index');
		}else{
			$this->display();
		}
		
	}
	/*
		编辑
	*/
	public function edit(){
		$id=I('get.id');
		$db=M('goods_store');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$g_info=M('goods')->where(array('id'=>$arr['goods_id']))->find();
			//商品编号
			$arr['goods_code']=$g_info['goods_code'];
			//商品名称
			$arr['goods_name']=$g_info['name'];
			//仓库名称
			$arr['storage']=M('storage')->where(array('id'=>$arr['storage_id']))->getField('name');
			$db->where(array('id'=>$id))->save($arr);
			$this->redirect('storage_list',array('goods_id'=>$info['goods_id']));
		}else{
			$this->display();
		}
	}
	/*
		地区管理
	*/
	public function area(){
		$map=array('region_type'=>1);
		$list=M('region')->where($map)->select();
		$list=order($list);
		$this->assign('list',$list);
		$this->display();
	}
	/*
		导入excel
	*/
	public function import_excel(){
		header('content-type:text/html;charset=utf-8');
		import('@.ORG.PHPExcel');
		import('@.ORG.PHPExcel.IOFactory');
		if($this->_post()){
			//$uploadfile="./Data/upload/file/20150731/20150731200716_58477.xlsx";
			$uploadfile='.'.I('post.excel');	
			//dump($uploadfile);
			//如果上传文件成功，就执行导入 excel操作  
			if($uploadfile){ 
				   // $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2003   
				   $objReader =PHPExcel_IOFactory::createReader('Excel2007');//use excel2003 和  2007 format   
				   // $objPHPExcel = $objReader->load($uploadfile); //这个容易造成httpd崩溃   
				   $objPHPExcel =PHPExcel_IOFactory::load($uploadfile);//改成这个写法就好了   
			  
				   $sheet = $objPHPExcel->getSheet(0);    
				   $highestRow = $sheet->getHighestRow(); // 取得总行数    
				   $highestColumn = $sheet->getHighestColumn(); // 取得总列数   
				   
					//循环读取excel文件,读取一条,插入一条   
					for($j=2;$j<=$highestRow;$j++)   
					{    
						for($k='A';$k<=$highestColumn;$k++)   
						 {    
							 //$str .= iconv('gbk','utf-8',$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue()).'\\';		
							 $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'\\';		
							 //读 取单元格  
						}   
						//explode:函 数把字符串分割为数组。  
						$strs=explode("\\",$str);  
						  
						$_data['goods_id']=$strs[0];
						$_data['goods_code']=$strs[1];
						$_data['goods_name']=$strs[2];
						$_data['storage_id']=$strs[3];
						$_data['storage']=$strs[4];
						$_data['store_nums']=$strs[5];
						M('goods_store')->add($_data);
						unset($_data);
						$str ="";  
				   }   
				   /*echo '<pre>';
				   var_dump ($strs);  
				   die();  */
				   unlink ($uploadfile); //删除上传的excel文件  
				  // $msg = "导入成功！";  
				  $this->success('导入成功！',U('index'));
				}else{  
				   //$msg = "导入失败！";   
				   $this->success('导入失败！',U('index'));
				}   
				return $msg;   
				
				//导入数据完成
		}else{
			$this->display();
		}
		
	}
	
	/*
		 商品对应仓库信息	
	*/
	public function storage_list(){
		import("@.ORG.Page");
		$db=M('goods_store');
		$goods_id=I('get.goods_id');
		
		$map=array('goods_id'=>$goods_id);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		$show = $Page->show();
		$this->assign('show',$show);
		
		$list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
		public function del(){
		$db=M('goods_store');
		$id=I('get.id');
		$goods_id=I('get.goods_id');
		$db->delete($id);
		$this->success('删除成功！',U('GoodsStore/storage_list',array('goods_id'=>$goods_id)));
	}
}