<?php
namespace Ceo\Controller;
use Think\Controller;
/*
店铺管理
*/
class ShopController extends PublicController
{
    
    public function _initialize()
    {
        parent::_initialize();
        $_level_list = M('user_level')->select();
        foreach ($_level_list as $val) {
            $level_list[$val['id']] = $val;
        }
        $this->assign('level_list', $level_list);
    }
    
    /*
    店铺列表
    */
    public function index()
    {
        import("Library.Page");
        $sch_id = session('sch_id');
        $db     = M('wechat_user');
        
        $shop    = M('shop')->where(array(
            'sch_id' => array(
                'in',
                $sch_id
            ),
            'status' => 1
        ))->select();
        $userArr = array();
        foreach ($shop as $key => $val) {
            $userArr[] = $val['uid'];
        }
        $_userId   = implode(",", $userArr);
        $map['id'] = array(
            'in',
            $_userId
        );
        
        $so_key = I('get.key');
        if ($so_key) {
            $where['nickname']  = array(
                'like',
                '%' . $so_key . '%'
            );
            $where['shop_name'] = array(
                'like',
                '%' . $so_key . '%'
            );
            $where['name']      = array(
                'like',
                '%' . $so_key . '%'
            );
            $where['_logic']    = 'or';
            $map['_complex']    = $where;
        }
        
        //角色为店长
        $map['role_id'] = 2;
        
        $count = $db->where($map)->count();
        $Page  = new \Page($count, 10);
        $show  = $Page->show();
        $this->assign('show', $show);
        
        $list = $db->where($map)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        foreach ($list as $key => $val) {
            $shop                  = M('shop')->where(array(
                'uid' => $val['id']
            ))->find();
            $shop['prov']          = get_region_name($shop['prov_id']);
            $shop['city']          = get_region_name($shop['city_id']);
            $shop['town']          = get_region_name($shop['county_id']);
            $shop['school']        = M('school')->where(array(
                'id' => $shop['sch_id']
            ))->getField('name');
            $shop['build']         = M('building')->where(array(
                'id' => $shop['build_id']
            ))->getField('name');
            $list[$key]['shop']    = $shop;
            $list[$key]['shop_id'] = $shop['id'];
        }
        
        $this->assign('list', $list);
        $this->display();
    }
    
    /*
    店铺信息
    */
    public function shop_detail()
    {
        $db                = M('wechat_user');
        $id                = I('get.id');
        $info              = $db->find($id);
        $shop              = M('shop')->where(array(
            'uid' => $id
        ))->find();
        $info['shop_info'] = $shop;
        $this->assign('info', $info);
        $this->display();
    }
    
    /*
    待审核店铺列表
    */
    public function audit()
    {
        import("Library.Page");
        $sch_id = session('sch_id');
        $db     = M('shop');
        
        //等待审核，店铺
        $map = array(
            'status' => 0,
            'sch_id' => array(
                'in',
                $sch_id
            )
        );
        
        $count = $db->where($map)->count();
        $Page  = new \Page($count, 10);
        $show  = $Page->show();
        $this->assign('show', $show);
        
        $list = $db->where($map)->order('posttime desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $key => $val) {
            $list[$key]['user']   = M('wechat_user')->where(array(
                'id' => $val['uid']
            ))->field('name,sex,mobile')->find();
            $list[$key]['city']   = M('region')->where(array(
                'id' => $val['city_id']
            ))->getField('region_name');
            $list[$key]['county'] = M('region')->where(array(
                'id' => $val['county_id']
            ))->getField('region_name');
            $list[$key]['school'] = M('school')->where(array(
                'id' => $val['sch_id']
            ))->getField('name');
            $list[$key]['build']  = M('building')->where(array(
                'id' => $val['build_id']
            ))->getField('name');
        }
        $this->assign('list', $list);
        $this->display();
    }
    
    /*
    校园CEO提交开通店铺申请
    */
    public function submitApply()
    {
        $id     = I('get.id');
        $sch_id = session('sch_id');
        $i      = M('shop')->where(array(
            'id' => $id,
            'sch_id' => $sch_id
        ))->find();
        if ($i) {
            $res = M('shop')->where(array(
                'id' => $id,
                'sch_id' => $sch_id
            ))->save(array(
                'status' => 2
            ));
            if ($res) {
                $this->success('提交申请成功', U('audit'));
            }
        } else {
            $this->error('非法操作！');
        }
    }
    
    /*
    设置账号密码
    */
    public function set_account()
    {
        $uid  = I('get.id');
        $db   = M('wechat_user');
        $user = $db->find($uid); //基础信息
        $shop = M('shop')->where(array(
            'uid' => $uid
        ))->find(); //店铺信息
        $this->assign('info', $user);
        $arr = I('post.');
        if (!empty($arr)) {
            $i = $db->where(array(
                'username' => $arr['username']
            ))->find();
            if (empty($i)) {
                $arr['password'] = md5($arr['password']);
                $db->where(array(
                    'id' => $uid
                ))->save($arr);
                //审核通过
                M('shop')->where(array(
                    'uid' => $uid
                ))->save(array(
                    'status' => 1
                ));
                //角色改为店长
                $db->where(array(
                    'id' => $uid
                ))->save(array(
                    'role_id' => 2
                ));
                //楼栋开通店铺
                M('building')->where(array(
                    'id' => $shop['build_id']
                ))->save(array(
                    'status' => 1
                ));
                $this->success('开通账号成功', U('index'));
            } else {
                $this->error('账号已经存在，请重新输入');
            }
        } else {
            $this->display();
        }
    }
    
    /*
    校园CEO删除店铺开通申请
    */
    public function del()
    {
        $uid    = I('get.uid');
        $sch_id = session('sch_id');
        $map    = array(
            'uid'    => $uid,
            'status' => 0,
            'sch_id' => array('in',$sch_id)
        );
        $shop = M('shop')->where($map)->find();
        
        //delete shop
        if ($shop) {
            $build_id = $shop['build_id'];
            M('shop')->where($map)->delete();
            //update user role
            M('wechat_user')->where(array(
                'id' => $uid
            ))->save(array(
                'role_id' => 1
            ));
            //update building status
            M('building')->where(array(
                'id' => $build_id
            ))->save(array(
                'status' => 0
            ));
            
            $this->success('删除成功！', U('audit'));
        } else {
            $this->error('非法操作！');
        }
    }
    
}
