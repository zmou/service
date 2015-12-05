<?php
 
/**
 * 微信网页授权认证接口
 * 微信授权相关接口
 */
 
class WxOAuth {
    
    //高级功能-》开发者模式-》获取
    private $app_id;// = 'wxde86151d025b9ef1';
    private $app_secret;// = '4f6ff375b2a08e49b9709df51ef5a771';
 	
 	public function __construct($param){
		$this->app_id=$param['appid'];
		$this->app_secret=$param['appsecret'];
	}
	
    /**
     * 获取微信授权链接
     * 
     * @param string $redirect_uri 跳转地址
     * @param mixed $state 参数
     */
    public function get_authorize_url($redirect_uri, $state = '')
    {
        $redirect_uri = urlencode($redirect_uri);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
    }
    
    /**
     * 获取授权token
     * $app_id = '', $app_secret = '',
     * @param string $code 通过get_authorize_url获取到的code
     */
    public function get_access_token($code)
    {
        
					
		$token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code=".$code."&grant_type=authorization_code";
       
		/*$token_data = $this->http($token_url);
		if($token_data[0] == 200)
        {
            return json_decode($token_data[1], TRUE);
        }
        return FALSE;*/
		$token_data=$this->httpGet($token_url);

        return json_decode($token_data, TRUE);
    }
    
    /**
     * 获取授权后的微信用户信息
     * 
     * @param string $access_token
     * @param string $open_id
     */
    public function get_user_info($access_token,$open_id)
    {
        if($access_token && $open_id)
        {
            $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            /*$info_data = @$this->http($info_url);
            
            if($info_data[0] == 200)
            {
                return json_decode($info_data[1], TRUE);
            }*/
			$info_data=$this->httpGet($info_url);

        	return json_decode($info_data, TRUE);
        }
        
        return FALSE;
    }
    
	public function httpGet($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);
	
		$res = curl_exec($curl);
		curl_close($curl);
	
		return $res;
	 }
	
	 /*
	*	功能：curl发送方post请求
	*	@param string $url
	*	@param string|json|array|xml $data
	*/
	private function httpPost($url,$data) {
		$curl = curl_init();
		//post提交方式
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);	//要求结果为字符串且输出到屏幕上
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		
		$res = curl_exec($curl);	
		curl_close($curl);
		return $res;
	}
	
    public function http($url, $method, $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
 
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
 
        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
 
            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));
 
            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return array($http_code, $response);
    }
 
}