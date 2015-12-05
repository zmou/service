<?php
namespace Ceo\Controller;
use Think\Controller;
/*
	个人账户
*/
class AccountController extends PublicController
{
	
	public function index()
	{
		$uid = I('session.uid');
		$ceoInfo = M('school_ceo')->find($uid);

		$this->assign('ceoInfo', $ceoInfo);
		$this->display();
	}

	// 保存个人资料
	public function updateAccount()
	{
		$db  = M('school_ceo');
		$uid = I('session.uid');

		$real_name         = I('post.real_name');
		$mobile            = I('post.mobile');
		$email 	           = I('post.email');
		$id_card  	  	   = I('post.id_card');
		$student_card 	   = I('post.student_card');
		$receiving_address = I('post.receiving_address');

		$account = array(
			'real_name'    		=> $real_name,
			'mobile'       		=> $mobile,
			'email'        		=> $email,
			'id_card' 	   		=> $id_card,
			'student_card' 		=> $student_card,
			'receiving_address' => $receiving_address
		);

		$res = $db->where(array('id'=>$uid))->data($account)->save();
		if($res){
			$this->success('更新成功', U('index'));
		} else {
			$this->error('更新失败', U('index'));
		}

	}

	// 提现申请
	public function withdrawDeposit()
	{
		$db  = M('school_ceo');
		$uid = I('session.uid');

		$userInfo = $db->find($uid);

		$MoneyLog = M('take_money')->where(array('user_id'=>$uid,'role_id'=>2,'status'=>1))->sum('money');

		$map = array(
			'role_id' => 1,
			'order_status' => 3,
			'pay_status' => 1,
			'school_id' => array('in', $userInfo['school_id']),
			'pay_time' => array('EGT', strtotime($userInfo['take_office_time']))
		);

		$totalFee 	  = M('order_info')->where($map)->sum('total_fee');
		$availableFee = round($totalFee * 0.02 - $MoneyLog, 2);
		
		$this->assign('user', $userInfo);
		$this->assign('availableFee', $availableFee);
		$this->display('withdraw_deposit');
	}

	// 提交提现
	public function doWithdrawDeposit()
	{
		$uid = I('session.uid');
		$arr = I('post.');
		
		if ( !empty($arr) ) {
			$data = array(
				'user_id' => $uid,
				'role_id' => 2,
				'money' => $arr['money'],
				'apply_time' => time(),
				'bank_card' => $arr['bank_card'],
				'bank_name' => $arr['bank_name'],
				'city' => $arr['city'],
				'true_name' => $arr['true_name'],
				'mobile' => $arr['mobile']
			);
			//var_dump($data);
			$res = M('take_money')->data($data)->add();
			//echo M('take_money')->getLastSql();exit;
			if ($res) {
				$this->success('申请成功', U('withdrawDeposit'));
			} else {
				$this->error('申请失败', U('withdrawDeposit'));
			}
		}
		else {
			$this->error('操作错误', U('withdrawDeposit'));
		}
	}

}
