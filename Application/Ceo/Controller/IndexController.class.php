<?php
namespace Ceo\Controller;
use Think\Controller;
class IndexController extends PublicController {
    public function index()
    {
        $sch_id = I('session.sch_id');
		$db=M('wechat_user');
		
		//统计店铺总数
        $shop_nums = M('shop')->where(array('sch_id'=>array('in',$sch_id),'status'=>1))->count();
		$shop      = M('shop')->field('id')->where(array('sch_id'=>array('in',$sch_id),'status'=>1))->select();
        $shop_id = array(); 
        $shop_id = array_reduce($shop, create_function('$v,$w', '$v[]=$w["id"];return $v;'));

        //等待审核，店铺
        $map = array(
            'status' => 0,
            'sch_id' => array('in',$sch_id)
        );
        $audit_shop = M('shop')->where($map)->count();

		//订单统计
		$today = strtotime(date('Y-m-d',time()));
		$map   = array(
            'order_time' => array('egt',$today),
            'pay_status' => 1,
            'school_id'  => array('in',$sch_id)
        );
		// 学生订单
        $map['role_id']   = 1;
		$user_order_total = M('order_info')->where($map)->count();
		// 店长订单
		$map['role_id']   = 2;
		$shop_order_total = M('order_info')->where($map)->count();
			
		$count_info                       = array();
		$count_info['shop_nums']          = $shop_nums;
		$count_info['user_order_total']   = $user_order_total;
        $count_info['shop_order_total']   = $shop_order_total;
		$count_info['audit_shop']         = $audit_shop;
		
		$this->assign('count_info',$count_info);
        $this->display();
    }

    public function test() {
        $this->display();
    }

    public function ajaxGetData()
    {
        $data = array(
            'eg',
            'fweg',
            'yukyuk',
            'wdqcs',
            'entyjtyg'
        );
        $this->assign('data',$data);
        $this->display('Inc:list');
    }

    public function ajaxGet2()
    {
        $this->display('Inc:test2');
    }


}