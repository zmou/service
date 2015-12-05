<?php
namespace Ceo\Controller;
use Think\Controller;

class OrderController extends PublicController
{
    // 学生订单
    public function index()
    {
        import("Library.Page");
        $sch_id     = session('sch_id');
        $db         = M('order_info');
        $so_key     = I('get.key');
        $so_val     = I('get.val');
        $begin_time = strtotime(I('get.begin_time'));
        $end_time   = strtotime(I('get.end_time'));

        // 学生订单统计
        $total = $db->where(array(
            'role_id'    => 1,
            'pay_status' => 1,
            'school_id'  => array('in', $sch_id)
            ))->sum('total_fee');
        
        $map = array(
            'school_id' => array('in', $sch_id),
            'role_id' => 1
        );
        
        $order_status = I('get.order_status');
        if ($order_status) {
            $map['order_status'] = $order_status;
        }
        
        if (in_array($so_key, array(
            'order_sn',
            'mobile',
            'consignee'
        ))) {
            if (!empty($so_val) && !empty($so_val)) {
                $map[$so_key] = array(
                    'like',
                    '%' . $so_val . '%'
                );
            }
        }
        if ($user_id = I('get.user_id')) {
            $map['user_id'] = $user_id;
        }
        
        if ($begin_time > 0 && $end_time > 0) {
            $map['order_time'] = array(
                array(
                    'egt',
                    $begin_time
                ),
                array(
                    'elt',
                    $end_time
                )
            );
        } else if ($begin_time > 0) {
            $map['order_time'] = array(
                'egt',
                $begin_time
            );
        } else if ($end_time > 0) {
            $map['order_time'] = array(
                'elt',
                $end_time
            );
        }
        
        $count = $db->where($map)->count();
        $Page  = new \Page($count, 10);
        
        $list = $db->where($map)->order('id desc')
                   ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $filterTotal = 0;
        foreach ($list as $key => $val) {
            $filterTotal += $val['total_fee'];
        }
        
        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('filterTotal', $filterTotal);
        
        //订单筛选条件
        $_SESSION['export_map'] = $map;
        $this->assign('show', $show);
        
        $this->display();
    }
    
    // 学生订单详情
    public function student_order_detail()
    {
        $order_id = $map['id'] = I('get.id');
        $db       = M('order_info');
        //订单信息
        $data     = $db->where($map)->find();
        if (empty($data)) {
            $this->error('该订单已不存在！');
        } else {
            $data['order_user'] = M('wechat_user')->field('id,nickname,name')->find($data['user_id']);
        }
        $this->assign('data', $data);
        //商品信息
        $order_goods = M('order_goods')->where(array(
            'order_id' => I('get.id')
        ))->order('id desc')->select();
        $this->assign('order_goods', $order_goods);
        $goods_list = M('order_goods')->where(array(
            'order_id' => $order_id
        ))->select();
        if ($data['shop_id'] > 0) {
            $shop = M('wechat_user')->find($data['shop_id']);
            $this->assign('shop', $shop);
        }
        
        //下单用户信息
        $user = M('wechat_user')->find($data['user_id']);
        $this->assign('user', $user);
        
        //分佣信息
        foreach ($order_goods as $val) {
            $goods_info = M('goods')->find($val['goods_id']);
            $yongjin += $val['goods_nums'] * $goods_info['yongjin'];
        }
        $config = M('resale_config')->find(1); //分佣配置
        if ($data['shop_id']) {
            $resaler1            = M('wechat_user')->find($data['shop_id']); //一级分销
            $resaler1['yongjin'] = $yongjin * ($config['parent_1'] * 0.01);
            $resaler1['percent'] = $config['parent_1'];
            $this->assign('resaler1', $resaler1);
            if ($resaler1['parent_id'] > 0) {
                $resaler2            = M('wechat_user')->find($resaler1['parent_id']); //二级分销
                $resaler2['yongjin'] = $yongjin * ($config['parent_2'] * 0.01);
                $resaler2['percent'] = $config['parent_2'];
                $this->assign('resaler2', $resaler2);
            }
        }
        
        $this->display();
    }
    
    // 店长订单
    public function shop_order()
    {
        import("Library.Page");
        $db         = M('order_info');
        $so_key     = I('get.key');
        $so_val     = I('get.val');
        $begin_time = strtotime(I('get.begin_time'));
        $end_time   = strtotime(I('get.end_time'));
        $sch_id     = session('sch_id');

        // 店长补货订单统计
        $shop = M('shop')->where(array('sch_id'=>array('in',$sch_id)))->select();
        $uid = array();
        foreach ($shop as $key => $val) {
            $uid[] = $val['uid'];
        }
        $total = $db->where(array(
            'role_id'    => 2,
            'user_id'    => array('in', $uid)
            ))->sum('total_fee');
        
        $map = array(
            'role_id' => 2,
            'user_id' => array('in', $uid)
        );
        $order_status = I('get.order_status');
        if ($order_status) {
            $map['order_status'] = $order_status;
        }
        
        if (in_array($so_key, array(
            'order_sn',
            'mobile',
            'consignee'
        ))) {
            if (!empty($so_val) && !empty($so_val)) {
                $map[$so_key] = array(
                    'like',
                    '%' . $so_val . '%'
                );
            }
        }
        if ($user_id = I('get.user_id')) {
            $map['user_id'] = $user_id;
        }
        
        if ($begin_time > 0 && $end_time > 0) {
            $map['order_time'] = array(
                array(
                    'egt',
                    $begin_time
                ),
                array(
                    'elt',
                    $end_time
                )
            );
        } else if ($begin_time > 0) {
            $map['order_time'] = array(
                'egt',
                $begin_time
            );
        } else if ($end_time > 0) {
            $map['order_time'] = array(
                'elt',
                $end_time
            );
        }
        
        $count = $db->where($map)->count();
        $Page  = new \Page($count, 10);
        // echo $db->getLastSql();
        $list  = $db->where($map)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show  = $Page->show();
        $filterTotal = 0;
        foreach ($list as $key => $val) {
            $filterTotal += $val['total_fee'];
        }
        
        $this->assign('map', $map);
        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('filterTotal', $filterTotal);
        
        //订单筛选条件
        $_SESSION['export_map'] = $map;
        $this->assign('show', $show);
        $this->display();
    }
    
    // 店长订单详情
    public function shop_order_detail()
    {
        $order_id = $map['id'] = I('get.id');
        $db       = M('order_info');
        //订单信息
        $data     = $db->where($map)->find();
        if (empty($data)) {
            $this->error('该订单已不存在！');
        } else {
            $data['order_user'] = M('wechat_user')->field('id,nickname,name')->find($data['user_id']);
        }
        $this->assign('data', $data);
        //商品信息
        $order_goods = M('order_goods')->where(array(
            'order_id' => I('get.id')
        ))->order('id desc')->select();
        $this->assign('order_goods', $order_goods);
        $goods_list = M('order_goods')->where(array(
            'order_id' => $order_id
        ))->select();
        if ($data['shop_id'] > 0) {
            $shop = M('wechat_user')->find($data['shop_id']);
            $this->assign('shop', $shop);
        }
        
        //下单用户信息
        $user = M('wechat_user')->find($data['user_id']);
        $this->assign('user', $user);
        
        //分佣信息
        foreach ($order_goods as $val) {
            $goods_info = M('goods')->find($val['goods_id']);
            $yongjin += $val['goods_nums'] * $goods_info['yongjin'];
        }
        $config = M('resale_config')->find(1); //分佣配置
        if ($data['shop_id']) {
            $resaler1            = M('wechat_user')->find($data['shop_id']); //一级分销
            $resaler1['yongjin'] = $yongjin * ($config['parent_1'] * 0.01);
            $resaler1['percent'] = $config['parent_1'];
            $this->assign('resaler1', $resaler1);
            if ($resaler1['parent_id'] > 0) {
                $resaler2            = M('wechat_user')->find($resaler1['parent_id']); //二级分销
                $resaler2['yongjin'] = $yongjin * ($config['parent_2'] * 0.01);
                $resaler2['percent'] = $config['parent_2'];
                $this->assign('resaler2', $resaler2);
            }
        }
        
        $this->display();
    }
    
    // 导出店长订单
    public function export_shop_order()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('Europe/London');
        
        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');
        
        import("Library.PHPExcel");
        
        $order_id    = I('get.order_id');
        $order_info  = M('order_info')->find($order_id);
        $order_goods = M('order_goods')->where(array(
            'order_id' => $order_id
        ))->select();
        
        foreach ($order_goods as $key => $val) {
            $order_goods[$key]['goods_info'] = M('goods')->where(array(
                'id' => $val['goods_id']
            ))->select();
        }
        
        $file_name = date('Y-m-d') . '-' . $order_info['build'];
        
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")->setLastModifiedBy("Maarten Balliauw")->setTitle("Office 2007 XLSX Test Document")->setSubject("Office 2007 XLSX Test Document")->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")->setKeywords("office 2007 openxml php")->setCategory("Test result file");
        
        $subObject = $objPHPExcel->getSheet(0);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
        $subObject->getColumnDimension('A')->setWidth(10);
        $subObject->getColumnDimension('B')->setWidth(10);
        $subObject->getColumnDimension('C')->setWidth(50);
        $subObject->getColumnDimension('D')->setWidth(10);
        $subObject->getColumnDimension('E')->setWidth(15);
        $subObject->getColumnDimension('F')->setWidth(15);
        $subObject->getColumnDimension('G')->setWidth(15);
        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '【叮咕寝室便利店】店长补货订单');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Add some data
        $objPHPExcel->getActiveSheet()->mergeCells('A2:C2');
        $objPHPExcel->getActiveSheet()->mergeCells('D2:G2');
        $objPHPExcel->getActiveSheet()->mergeCells('D3:G3');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:C3');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', '姓名：' . $order_info['consignee'])->setCellValue('D2', '电话：' . $order_info['mobile'])->setCellValue('A3', '地址：' . $order_info['province'] . '-' . $order_info['city'] . '-' . $order_info['district'] . '-' . $order_info['address'])->setCellValue('D3', '下单时间：' . date("Y-m-d H:i:s", $order_info['order_time']));
        
        $objPHPExcel->getActiveSheet()->getStyle('D3')->getFont()->getColor()->setARGB('#FF0000');
        
        $objPHPExcel->getActiveSheet()->setCellValue('A4', '编号');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', 'ID');
        $objPHPExcel->getActiveSheet()->setCellValue('C4', '商品名');
        $objPHPExcel->getActiveSheet()->setCellValue('D4', '规格');
        $objPHPExcel->getActiveSheet()->setCellValue('E4', '数量');
        $objPHPExcel->getActiveSheet()->setCellValue('F4', '店长采购价');
        $objPHPExcel->getActiveSheet()->setCellValue('G4', '店长留言');
        
        $row = 5;
        $ii  = 1;
        foreach ($order_goods as $goods) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $row, $ii);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B' . $row, $goods['goods_id']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C' . $row, $goods['goods_name']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $row, '1×' . $goods['goods_info'][0]['package_num']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E' . $row, $goods['goods_nums']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F' . $row, $goods['goods_nums'] * $goods['goods_info'][0]['trade_price'] * $goods['goods_info'][0]['package_num']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G' . $row, $order_info['order_message']);
            
            $row += 1;
            $ii += 1;
        }
        
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $row . ':C' . $row);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, '合计');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFont()->getColor()->setARGB('#FF0000');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E' . $row, '=SUM(E5:E' . ($row - 1) . ')');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F' . $row, '=SUM(F5:F' . ($row - 1) . ')');
        $objPHPExcel->getActiveSheet()->mergeCells('G5:G' . $row);
        
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 1) . ':C' . ($row + 1));
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 1))->getFont()->getColor()->setARGB('#FF0000');
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 1), '叮咕店长服务专线：13572962720');
        
        $objPHPExcel->getActiveSheet()->mergeCells('D' . ($row + 1) . ':F' . ($row + 1));
        $objPHPExcel->getActiveSheet()->getStyle('D' . ($row + 1))->getFont()->getColor()->setARGB('#FF0000');
        $objPHPExcel->getActiveSheet()->setCellValue('D' . ($row + 1), '送货人：                                   电话：');
        
        $objPHPExcel->getActiveSheet()->mergeCells('G' . ($row + 1) . ':H' . ($row + 1));
        $objPHPExcel->getActiveSheet()->getStyle('G' . ($row + 1))->getFont()->getColor()->setARGB('#FF0000');
        $objPHPExcel->getActiveSheet()->setCellValue('G' . ($row + 1), '收货人：                   电话：');
        
        $objPHPExcel->getActiveSheet()->getRowDimension($row + 2)->setRowHeight(200);
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->mergeCells('A' . ($row + 2) . ':H' . ($row + 2));
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 2))->getFont()->getColor()->setARGB('#FF0000');
        $objPHPExcel->getActiveSheet()->setCellValue('A' . ($row + 2), "注：1、烦请叮咕供货合作伙伴留意店长的下单时间，力争在规定48小时内按店长所补货商品种类、按时且保质保量、准确无误地送到提货人指定地点。\r\n2、烦请叮咕收货人务必仔细核对商品名称、数量、质量等相关信息，若发现任何有缺货、残货等其他商品数量和质量类问题，收货人可与送货人现场协商解决，若协商不成，收货人有权当场拒签并要求送货人退还收货人相应问题货物（包括缺货商品）的双倍费用，若有任何疑问，收货人可直接拨打叮咕店长服务专线帮助其解决。");
        $objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 2))->getAlignment()->setWrapText(true);
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    
    
}
