<?php
class AjaxAction extends Action{
	public function index(){
		import("@.ORG.Page");
		$db=M('photo');
		if($wechatid=I('wechatid')){
			$map=array('wechatid'=>$wechatid);	
		}else{
			$map=array();
		}
		$count = $db->where($map)->count();
		$Page = new Page($count,20);		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['thumb']=get_thumb($val['photo']);
			$list[$key]['uname']=M('wechatuser')->where(array('wechatid'=>$val['wechatid']))->getField('uname');
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();    
	}
	public function del(){
		if($id=I('get.id')){
			$db=M('photo');
			$db->where(array('id'=>$id))->delete();
			$this->redirect('index');
		}	
	}
	public function upload(){
		import("@.ORG.Thumb");
		$date=date('Ymd',time());
        $folder="./Data/upload/slide/$date";
        if(!file_exists($folder)){
            mkdir($folder);
        }
        $targetFolder=$folder;
		if (!empty($_FILES)){
			$return=array('flag'=>false);
			if($_FILES['Filedata']['size']>2*1000*1000){
				$return['msg']='图片大小不能超过2M';
				echo json_encode($return);
				die();
			}
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $targetFolder ;//$_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			//重新构造图片名称
			$fileParts=pathinfo($_FILES['Filedata']['name']);
			$picname=rand(1111,9999).time().'.'.$fileParts['extension'];
			$targetFile=rtrim($targetPath,'/') . '/' . $picname;
			$thumbFile=rtrim($targetPath,'/') . '/thumb_' . $picname;
			//$targetFile=rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
			
			// Validate the file type
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);
				//生成缩略图
				$thumb=new ResizeImage($targetFile, '120', '90', '0',$thumbFile);
				/*$source = imagecreatefromjpeg($targetFile);
				list($width, $height) = getimagesize($targetFile);
				list($newwidth,$newheight)=array(200,170);
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
				if($fileParts['extension']=='png'){
					imagepng($thumb,$thumbFile);
				}elseif($fileParts['extension']=='jpg'){
					imagejpeg($thumb,$thumbFile);
				}elseif($fileParts['extension']=='gif'){
					imagegif($thumb,$thumbFile);
				}*/
				$return['flag']=true;
				$return['url']=substr($targetFile,1);
				echo json_encode($return);
				die();
			}else{
				$return['msg']='图片格式不正确';
				echo json_encode($return);
				die();
			}
		}
	}
	//地区联动
	/*
		省份
	*/
	public function province(){
		$db=M('region');
		$map=array('region_type'=>1);
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		echo json_encode($list);die();
	}
	/*
		城市
	*/
	public function city(){
		$db=M('region');
		$map=array('region_type'=>2,'parent_id'=>I('post.parent_id'));
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		
		echo json_encode($list);die();
	}
	/*
		区县
	*/
	public function district(){
		$db=M('region');
		$map=array('region_type'=>3,'parent_id'=>I('post.parent_id'));
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		
		echo json_encode($list);die();
	}

	public function school()
	{
		$county_id = I('post.county_id');

		$school = M('school');
		$data = $school->where(array('county_id'=>$county_id))->select();

		echo json_encode($data);exit;
	}
	/*
		根据学校id获取楼栋列表
	*/
	public function build(){
		$db=M('building');
		$map=array('sch_id'=>I('post.sch_id'),'status'=>0);			//未开启店铺的楼栋
		$list=$db->where($map)->field('id,name')->select();
		echo json_encode($list);die();
	}
}
