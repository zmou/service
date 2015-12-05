<?php
	/*
	常用公共函数
	 */
	/*
	根据城市名称，从数据库中匹配城市id
	 */
function get_city_id(){
	$ip=I('server.REMOTE_ADDR');			//用户ip
	$res=ip_2_position($ip);
	$city=$res['retData']['city'];
	$city_info=M('region')->where(array('region_name'=>array('like','%'.$city.'%')))->find();
	if(!empty($city_info)){
		return $city_info['id'];
	}else{
		return false;
	}

}
	/*
	根据IP地址定位
	eg:117.89.35.58
	JSON返回示例 :
	{
		"errNum": 0,
		"errMsg": "success",
		"retData": {
			"ip": "117.89.35.58",
			"country": "中国",
			"province": "江苏",
			"city": "南京",
			"district": "鼓楼",
			"carrier": "中国电信"
		}
	}
	 */
function ip_2_position($ip){
	$ch = curl_init();
	$url = 'http://apis.baidu.com/apistore/iplookupservice/iplookup?ip='.$ip;
	$header = array(
		'apikey:7dcef56486067467f307322fe75e21cd',
	);
	// 添加apikey到header
	curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 执行HTTP请求
	curl_setopt($ch , CURLOPT_URL , $url);
	$res = curl_exec($ch);
	//var_dump(json_decode($res,true));
	return json_decode($res,true);
}
	/*
	根据地区id获得地区名成
	region表
	 */
function get_region_name($id){
	$res=M('region')->where(array('id'=>$id))->getField('region_name');
	return $res;
}

	/*
	地理位置定位
	@param int	build_id	建筑【宿舍楼】id
	 */

function position_fix($build_id){
	//宿舍楼信息
	$build_info=M('building')->find($build_id);
	//学校信息
	$sch_info=M('school')->find($build_info['sch_id']);
	$position=array(
		'prov_id'=>$sch_info['prov_id'],			//省份
		'prov'=>$sch_info['prov'],
		'city_id'=>$sch_info['city_id'],			//城市
		'city'=>$sch_info['city'],
		'county_id'=>$sch_info['county_id'],		//区县
		'county'=>$sch_info['county'],
		'school_id'=>$sch_info['id'],				//学校
		'school'=>$sch_info['name'],
		'build_id'=>$build_id,						//建筑【宿舍楼】
		'build'=>$build_info['name'],
	);	
	return $position;
}

	/*
	提现状态
	 */
function apply_status($state){
	$arr=array(0=>'<font color="red">等待处理</font>',
		1=>'<font color="green">提现成功</font>',
		2=>'<font color="red"提现失败</font>');
	return $arr[$state];
}
	/*
	/*
	根据id获取用户信息
	 */
function get_userinfo($user_id){
	$info=M('wechatuser')->find($user_id);
	return $info;
}
	/*
	php无限分级
	 */
function order($array,$pid=0,$level=0){
	$arr = array();
	foreach($array as $v){
		if($v['fup']==$pid){	//||$v['parent_id']==$pid
			$v['pre']=str_repeat('—',$level);
			$arr[] = $v;
			$arr = array_merge($arr,order($array,$v['id'],$level+1));
		}
	}
	return $arr;
}
/*
订单状态
	 */
function order_status($state){
	$arr=array(1=>'未发货',2=>'已发货',3=>'已签收');
	return $arr[$state];
}
/*
获取品牌名称
	 */
function get_brandname($bid){
	$db=M('goods_brand');
	$info=$db->find($bid);
	return $info['name'];	
}
/*
获取分类名称
	 */
function get_catename($cid){
	$db=M('goods_category');
	$info=$db->find($cid);
	return $info['name'];	
}
/*
获取性别
	 */
function get_sex($sex){
	$arr=array(0=>'未知',1=>'男',2=>'女');
	return $arr[$sex];
}

function node_merge($node,$access=null,$pid=0){
	$arr=array();
	foreach($node as $v){
		if(is_array($access)){
			$v['access']=in_array($v['id'],$access)?1:0;
		}
		if($v['pid']==$pid){
			$v['child']=node_merge($node,$access,$v['id']);
			$arr[]=$v;

		}
	}
	return $arr;
}
function cmstype($t,$i){
	$sort[1] = array('分类','栏目','单篇');
	$sort[2] = array('文章','图片','房产');
	return $sort[$t][$i];
}



/*
获取缩略图地址
	 */
function get_thumb($picurl){
	//$picurl="./Data/upload/photo/20141121/1416550895914.png";
	$picurl=str_replace('thumb_','',$picurl);
	$pathinfo=pathinfo($picurl);
	return $pathinfo['dirname'].'/thumb_'.$pathinfo['basename'];
}

/*
获取原图地址
	 */
function get_pic($picurl){
	$picurl=str_replace('thumb_','',$picurl);
	return $picurl;
}
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++购物车函数++++++++++++++++++++++++++++++++++++++++++++++++++//

/*
添加购物车
	 */
function addcart($goods_id,$goods_num,$goods_price){
	//$cur_cart_array =unserialize(stripslashes($_COOKIE['shop_cart_info']));
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	if(empty($cur_cart_arr)){
		$cart_info[$goods_id]['goods_id'] = $goods_id;
		$cart_info[$goods_id]['goods_nums'] = $goods_num;
		$cart_info[$goods_id]['goods_price'] = $goods_price;
		//setcookie("shop_cart_info",serialize($cart_info),time()+3600);
		$_SESSION['shop_cart_info']=$cart_info;
	}elseif($cur_cart_arr<>""){
		//遍历当前的购物车数组
		//如果键值为0且货号相同则购物车存在相同货品
		$is_exist=0;
		foreach($cur_cart_arr as $key=>$goods_current_cart){
			if($goods_current_cart['goods_id']==$goods_id){
				$cur_cart_arr[$key]['goods_nums']=$goods_current_cart['goods_nums']+$goods_num;
				$is_exist=1;
			}
		}
		if($is_exist==0){
			$cur_cart_arr[$goods_id]=array('goods_id'=>$goods_id,'goods_nums'=>$goods_num,'goods_price'=>$goods_price) ;
		}
		//setcookie("shop_cart_info",serialize($cur_cart_array),time()+3600);
		$_SESSION['shop_cart_info']=$cur_cart_arr;
	}	

}

/*
删除购物车
	 */
function delcart($goods_array_id){
	//$cur_goods_arr =unserialize(stripslashes($_COOKIE['shop_cart_info']));
	$cur_goods_arr=$_SESSION['shop_cart_info'];
	//删除该商品在数组中的位置
	unset($cur_goods_arr[$goods_array_id]);
	//setcookie("shop_cart_info",serialize($cur_goods_array));
	$_SESSION['shop_cart_info']=$cur_goods_arr;
}

/*
修改购物车
	 */
function updatecart($goods_id,$action='add', $package_num=1){
	//$cur_cart_array =unserialize(stripslashes($_COOKIE['shop_cart_info']));
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	if($action=='add'){
		$cur_cart_arr[$goods_id]['goods_nums']+=$package_num;
	}else{
		$cur_cart_arr[$goods_id]['goods_nums']-=$package_num;
		if($cur_cart_arr[$goods_id]['goods_nums']<1){
			unset($cur_cart_arr[$goods_id]);
		}
	}
	//setcookie("shop_cart_info",serialize($cur_cart_array),time()+3600);
	$_SESSION['shop_cart_info']=$cur_cart_arr;
}
//+++++++++++++++++++++++++++++++++++++++++++++++/购物车结束++++++++++++++++++++++++++++++++++++++++++++++++++++++//
/*
获取当前url
	 */
function get_curr_url() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}


function replace_pic($content){
	preg_match_all('/\[.*?\]/is',$content,$arr);
	if($arr[0]){
		$pic=F('pic','','./data/');
		foreach($arr[0] as $v){
			foreach($pic as $key=>$val){
				if($v=='['.$val.']'){
					$content=str_replace($v,'<img src="'.__ROOT__.'/Public/Images/phiz/'.$key.'.gif"/>',$content);
				}
				continue;
			}
		}
	}
	return $content;
}

/*
按键值对查找数组
	 */
function seekarr($arr=array(),$key,$val){
	$res = array();
	$str = json_encode($arr);
	preg_match_all("/\{[^\{]*\"".$key."\"\:\"".$val."\"[^\}]*\}/",$str,$m);
	if($m && $m[0]){
		foreach($m[0] as $val) $res[] = json_decode($val,true);
	}
	return $res;
}
/*
递归-按照分类子级关系重排栏目
	 */
function sarr($arr,$id){
	global $ic;
	$thisa=array();
	$aarr=seekarr($arr,'fup',$id);	//fup 上级
	if(count($aarr)>0){
		for($i=0;$i<count($aarr);$i++){
			$thisa[$ic]=$aarr[$i];
			$ic+=1;
			$o=$aarr[$i]['id'];	//fid 栏目id
			$toarr=sarr($arr,$o);
			if(count($toarr)>0){
				$thisa=array_merge($thisa,$toarr);
			}
		}
		return $thisa;
	}
}
/*
对二维数组按键值排序
	 */
function array_sort($arr,$keys,$type='asc'){
	$keysvalue = $new_array = array();
	foreach ($arr as $k=>$v){
		$keysvalue[$k] = $v[$keys];
	}
	if($type == 'asc'){
		asort($keysvalue);
	}else{
		arsort($keysvalue);
	}
	reset($keysvalue);
	foreach ($keysvalue as $k=>$v){
		$new_array[$k] = $arr[$k];
	}
	return $new_array;
}

/**

* 生成随机字符串，由小写英文和数字组成。去掉了容易混淆的0o1l之类

* @param int $int 生成的随机字串长度

* @param boolean $caps 大小写，默认返回小写组合。true为大写，false为小写

* @return string 返回生成好的随机字串

	 */

function randStr($int = 6, $caps = false) {

	$strings = 'abcdefghjkmnpqrstuvwxyz23456789';

	$return = '';

	for ($i = 0; $i < $int; $i++) {

		srand();

		$rnd = mt_rand(0, 30);

		$return = $return . $strings[$rnd];

	}

	return $caps ? srttoupper($return) : $return;

}

/*
判断是否为"微信浏览器"
	 */
function is_weixin(){

	$agent = $_SERVER['HTTP_USER_AGENT']; 
	if(strpos($agent,"icroMessenger")===false) {
		$return=false;  						//不是微信
		//file_put_contents('a.txt','liulanqi');
	}else{
		//file_put_contents('a.txt','weixin');
		$return=true;							//是微信
	}
	return $return;
}

/*
判断是否为移动设备
	 */
function is_mobile()
{ 
	// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
	{
		return true;
	} 
	// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
	if (isset ($_SERVER['HTTP_VIA']))
	{ 
		// 找不到为flase,否则为true
		return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
	} 
	// 脑残法，判断手机发送的客户端标志,兼容性有待提高
	if (isset ($_SERVER['HTTP_USER_AGENT']))
	{
		$clientkeywords = array ('nokia',
			'sony',
			'ericsson',
			'mot',
			'samsung',
			'htc',
			'sgh',
			'lg',
			'sharp',
			'sie-',
			'philips',
			'panasonic',
			'alcatel',
			'lenovo',
			'iphone',
			'ipod',
			'blackberry',
			'meizu',
			'android',
			'netfront',
			'symbian',
			'ucweb',
			'windowsce',
			'palm',
			'operamini',
			'operamobi',
			'openwave',
			'nexusone',
			'cldc',
			'midp',
			'wap',
			'mobile'
		); 
		// 从HTTP_USER_AGENT中查找手机浏览器的关键字
		if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
		{
			return true;
		} 
	} 
	// 协议法，因为有可能不准确，放到最后判断
	if (isset ($_SERVER['HTTP_ACCEPT']))
	{ 
		// 如果只支持wml并且不支持html那一定是移动设备
		// 如果支持wml和html但是wml在html之前则是移动设备
		if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
		{
			return true;
		} 
	} 
	return false;
}

function extend($file_name){
	$extend = pathinfo($file_name);
	$extend = strtolower($extend["extension"]);
	return $extend;
}

/*
积分策略
@param $type  1收,2支出
@param $act 积分动作
@param $user_id	用户id
	 */
function return_jifen($type,$act,$user_id){
	//查询积分策略
	$jifen_conf=M('jifen_config')->find(1);
	//积分日志数据
	$log['type']=$type;
	$log['user_id']=$user_id;
	$log['posttime']=time();			
	switch($act){
		//注册
	case 'reg':
		$log['way']='reg';				
		$log['way_name']='注册';
		$log['amount']=$jifen_conf['reg'];		//积分数量		
		break;
		//推荐注册
	case 'reg_tui':
		$log['way']='reg_tui';				
		$log['way_name']='推荐用户注册';
		$log['amount']=$jifen_conf['reg_tui'];		//积分数量		
		break;
		//登录
	case 'login':
		$log['way']='login';				
		$log['way_name']='每日登录';
		$log['amount']=$jifen_conf['login'];		//积分数量		
		break;
		//分享
	case 'share':
		$log['way']='share';				
		$log['way_name']='分享';
		$log['amount']=$jifen_conf['share'];		//积分数量		
		break;
		//签到
	case 'sign':
		$log['way']='sign';				
		$log['way_name']='签到';
		$log['amount']=$jifen_conf['sign'];		//积分数量		
		break;
		//好友请
	case 'friend':
		$log['way']='friend';				
		$log['way_name']='好友请';
		$log['amount']=$jifen_conf['friend'];		//积分数量		
		break;
	}
	if($type==1){			//收入		
		M('wechat_user')->where(array('id'=>$user_id))->setInc('jifen',$log['amount']);
	}elseif($type==2){		//支出
		M('wechat_user')->where(array('id'=>$user_id))->setDec('jifen',$log['amount']);
	}
	//记录日志
	M('jifen_water')->add($log);
}


function Pinyin($_String, $_Code='UTF8'){ //GBK页面可改为gb2312，其他随意填写为UTF8
	$_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha". 
		"|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|". 
		"cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er". 
		"|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui". 
		"|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang". 
		"|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang". 
		"|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue". 
		"|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne". 
		"|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen". 
		"|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang". 
		"|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|". 
		"she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|". 
		"tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu". 
		"|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you". 
		"|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|". 
		"zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo"; 
	$_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990". 
		"|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725". 
		"|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263". 
		"|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003". 
		"|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697". 
		"|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211". 
		"|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922". 
		"|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468". 
		"|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664". 
		"|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407". 
		"|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959". 
		"|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652". 
		"|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369". 
		"|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128". 
		"|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914". 
		"|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645". 
		"|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149". 
		"|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087". 
		"|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658". 
		"|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340". 
		"|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888". 
		"|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585". 
		"|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847". 
		"|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055". 
		"|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780". 
		"|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274". 
		"|-10270|-10262|-10260|-10256|-10254"; 
	$_TDataKey   = explode('|', $_DataKey); 
	$_TDataValue = explode('|', $_DataValue);
	$_Data = array_combine($_TDataKey, $_TDataValue);
	arsort($_Data); 
	reset($_Data);
	if($_Code!= 'gb2312') $_String = _U2_Utf8_Gb($_String); 
	$_Res = ''; 
	for($i=0; $i<strlen($_String); $i++) { 
		$_P = ord(substr($_String, $i, 1)); 
		if($_P>160) { 
			$_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536;
		} 
		$_Res .= _Pinyin($_P, $_Data); 
	} 
	return preg_replace("/[^a-z0-9]*/", '', $_Res); 
} 
function _Pinyin($_Num, $_Data){ 
	if($_Num>0 && $_Num<160 ){
		return chr($_Num);
	}elseif($_Num<-20319 || $_Num>-10247){
		return '';
	}else{ 
		foreach($_Data as $k=>$v){ if($v<=$_Num) break; } 
	return $k; 
	} 
}
function _U2_Utf8_Gb($_C){ 
	$_String = ''; 
	if($_C < 0x80){
		$_String .= $_C;
	}elseif($_C < 0x800) { 
		$_String .= chr(0xC0 | $_C>>6); 
		$_String .= chr(0x80 | $_C & 0x3F); 
	}elseif($_C < 0x10000){ 
		$_String .= chr(0xE0 | $_C>>12); 
		$_String .= chr(0x80 | $_C>>6 & 0x3F); 
		$_String .= chr(0x80 | $_C & 0x3F); 
	}elseif($_C < 0x200000) { 
		$_String .= chr(0xF0 | $_C>>18); 
		$_String .= chr(0x80 | $_C>>12 & 0x3F); 
		$_String .= chr(0x80 | $_C>>6 & 0x3F); 
		$_String .= chr(0x80 | $_C & 0x3F); 
	} 
	return iconv('UTF-8', 'GB2312', $_String); 
}

function send_sms($mobile, $content)
{
	$cust_code = '001025';									//账号
	$password = 'CXITIV9MLF';						//密码
	$sp_code = '106904561025';										//扩展码
	$content = $content;					//发送内容
	$destMobiles = $mobile;		 						//手机号码，使用逗号隔开可以发送多个号码
	$url='http://120.26.220.72:8860/';												//URL地址
	$post_data = array();
	$post_data['cust_code'] = $cust_code;																	
	$post_data['destMobiles'] = $destMobiles;									
	//$post_data['content'] =  mb_convert_encoding($content, 'utf-8', 'gb2312');
	//$post_data['sign'] = md5(urlencode(mb_convert_encoding($content, 'utf-8', 'gb2312').$password));								//签名
	$post_data['content'] =  $content;
	$post_data['sign'] = md5(urlencode($content.$password));								//签名
	$post_data['sp_code'] = $sp_code;	
	$o="";
	foreach ($post_data as $k=>$v)
	{
		if($k =='content')
			$o.= "$k=".urlencode($v)."&";
		else
			$o.= "$k=".($v)."&";
	}
	$post_data=substr($o,0,-1);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL,$url);
	//为了支持cookie
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$result = curl_exec($ch);

	return $result;
}
//测试
//echo Pinyin('中文字','gb2312'); //第二个参数“1”可随意设置即为utf8编码
