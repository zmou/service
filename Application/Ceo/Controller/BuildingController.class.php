<?php
namespace Ceo\Controller;
use Think\Controller;
/*
    宿舍楼管理
*/
class BuildingController extends PublicController
{
    
    public function _initialize()
    {
        parent::_initialize();
        //学校列表
        $school = M('school')->select();
        $this->assign('school', $school);
    }

    /*
    宿舍楼列表
    */
    public function index()
    {
        import("Library.Page");
        $sch_id = I('get.sch_id');
        $db     = M('building');
        $map    = array(
            'sch_id' => $sch_id
        );
        $count  = $db->where($map)->count();
        $Page   = new \Page($count, 10);
        
        $list = $db->where($map)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $this->assign('show', $show);
        $this->assign('list', $list);
        $this->assign('sch_id', $sch_id);
        $this->display();
    }
    
    /*
        新增楼栋
    */
    public function add()
    {
        $sch_id = I('get.sch_id');
        $db     = M('building');
        $school = M('school')->find($sch_id);
        $arr    = I('post.');
        $this->assign('school', $school);

        if ( !empty($arr) ) {
            if (empty($arr['sch_id'])) {
                $this->error('请选择学校！');
            } else {
                $arr['posttime'] = time();
                $arr['sch_name'] = M('school')->where(array(
                    'id' => $arr['sch_id']
                ))->getField('name');
                $db->add($arr);
                $this->redirect('index', array(
                    'sch_id' => $arr['sch_id']
                ));
            }
        } else {
            $this->display();
        }
        
    }
    /*
        编辑 宿舍楼信息
    */
    public function edit()
    {
        $id     = I('get.id');
        $info   = M('building')->find($id);
        $school = M('school')->find($info['sch_id']);

        $this->assign('info', $info);
        $this->assign('school', $school);
        $arr = I('post.');
        if ( !empty($arr) ) {
            $w['id']         = I('get.id');
            $arr['posttime'] = time();
            $arr['prov']     = M('region')->where(array(
                'id' => $arr['prov_id']
            ))->getField('region_name');
            $arr['city']     = M('region')->where(array(
                'id' => $arr['city_id']
            ))->getField('region_name');
            $arr['county']   = M('region')->where(array(
                'id' => $arr['county_id']
            ))->getField('region_name');
            
            M('building')->where($w)->save($arr);
            $this->success('保存成功', U('index', array(
                'sch_id' => $info['sch_id']
            )));
        } else {
            $this->display();
        }
    }
    
    /*
    服务区域
    */
    public function area_edit()
    {
        header("content-type:text/html;charset=utf-8");
        $db       = M('region');
        $id       = I('get.id'); // 宿舍楼id
        $province = $db->where(array(
            'region_type' => 1
        ))->select();
        foreach ($province as $key => $val) {
            $city = $db->where(array(
                'parent_id' => $val['id']
            ))->select();
            foreach ($city as $k => $v) {
                $city[$k]['county'] = $db->where(array(
                    'parent_id' => $v['id']
                ))->select();
            }
            $province[$key]['city'] = $city;
            unset($city);
        }
        $this->assign('province', $province);
        if ($arr = $this->_post()) {
            $arr['area_list'] = implode(',', $arr['area_list']);
            $arr['area_list'] .= ',';
            M('building')->where(array(
                'id' => $id
            ))->save($arr);
            $this->redirect('index');
        }
        //已分配区域
        $info              = M('building')->find($id);
        $info['area_list'] = array_filter(explode(',', $info['area_list']));
        $this->assign('area_list', $info['area_list']);
        $this->display();
    }
    
    

}