<?php
namespace Ceo\Controller;
use Think\Controller;
class IndexController extends PublicController {
    public function index() {
		$db=M('wechat_user');
		//统计分销商总数
		$resaler_nums=$db->where(array('role_id'=>3))->count();
		//统计店铺总数
		$shop_nums=$db->where(array('role_id'=>2))->count();
		//订单统计
		$today=strtotime(date('Y-m-d',time()));
		
		$map=array('order_time'=>array('egt',$today));
		//订单总数
		$order_total=M('order_info')->where($map)->count();
		//已付款订单
		$map['pay_status']=1;
		$pay_order_total=M('order_info')->where($map)->count();
		$map['pay_status']=0;
		$unpay_order_total=M('order_info')->where($map)->count();
		//商品统计
		//上架商品
		$sale_goods_total=M('goods')->where(array('is_sale'=>1))->count();
		//下架商品
		$unsale_goods_total=M('goods')->where(array('is_sale'=>0))->count();
		//最新商品
		$new_time=strtotime(date('Y-m-d',time()));
		$new_goods_total=M('goods')->where(array('is_sale'=>0,'posttime'=>array('egt',$new_time)))->count();
			
		$count_info['resaler_nums']=$resaler_nums;
		$count_info['shop_nums']=$shop_nums;
		$count_info['order_total']=$order_total;
		$count_info['pay_order_total']=$pay_order_total;
		$count_info['unpay_order_total']=$unpay_order_total;
		
		$count_info['sale_goods_total']=$sale_goods_total;
		$count_info['unsale_goods_total']=$unsale_goods_total;
		$count_info['new_goods_total']=$new_goods_total;
		
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