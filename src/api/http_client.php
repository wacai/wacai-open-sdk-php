<?php
namespace wacai\open\api;
require_once dirname(__DIR__) . '/libs/curl.php';
require_once dirname(__DIR__) . '/libs/base64.php';
require_once dirname(__DIR__) . '/config/web_config.php';
require_once dirname(__DIR__) . '/libs/utils.php';
require_once dirname(__DIR__) . '/libs/cache/local_cache.php';
require_once dirname(__DIR__). '/token/token_service.php';

class HttpClient
{
    private $api_name;
    private $api_version;
    private $token_service;
    private $token;

    // Token服务
    public function __construct($api_name, $api_version)
    {
        $this->api_name = $api_name;
        $this->api_version = $api_version;
        // token初始化
        $this->token_service = new \wacai\open\token\TokenService();
        $this->token = $this->token_service->getToken();
    }

    /**
     * http-post调用
     * @param $json_data 业务参数 json格式
     * @param bool $is_debug 是否开启调试
     * @param ref $res response结果
     */
    public function http_post_json($json_data, $is_debug = false, &$res)
    {
        // 检查apiName and apiVersion
        if(empty($this->api_name) || empty($this->api_version)){
            throw new \Exception("Api name or version is null");      
        }
        
        $body_md5 = '';
        if (!isset($json_data) || strlen($json_data) == 0) {
            $body_md5 = "1B2M2Y8AsgTpgAmY7PhCfg==";
        } else {
            // 业务数据md
            $body_md5 = base64_encode(md5($json_data, true));
        }

        // 检查token是否为空
        if(is_object($this->token) && !empty($this->token)){
            // access token
            $access_token = $this->token->getAccessToken();
            if(empty($access_token)){
                throw new \Exception("token is null");
            }
        } else{
            throw new \Exception("获取token失败,请检测app_key/app_secret");
        }

        // 时间戳
        $time_stamp = \wacai\open\lib\Util::getMillisecond();
        // http-header
        $param_header = [
            'x-wac-version' => \wacai\open\config\WebConfig::X_WAC_VERSION,
            'x-wac-timestamp' => $time_stamp,
            'x-wac-access-token' => $access_token,
        ];
        
        //排序(计算的header按照headerName的字母表升序排列)
        ksort($param_header);
        
        // 组装head-string
        $headerString = '';
        foreach ($param_header as $key => $val) {
            $headerString .= "$key=$val" . '&';
        }
        $headerString = substr($headerString, 0, strlen($headerString) - 1);

        // 待签名的数据
        $strToSign = $this->api_name . '|' . $this->api_version . '|' . $headerString . '|' . $body_md5;
        
        // 已签名(signature)
        $signature = \wacai\open\lib\Base64::base64url_encode(hash_hmac('sha256', $strToSign, \wacai\open\config\WebConfig::APP_SECRET, true));

        $curl = new \wacai\open\lib\Curl();
        // 设置请求的header
        $curl->add_header("x-wac-version: " .\wacai\open\config\WebConfig::X_WAC_VERSION);
        $curl->add_header("x-wac-timestamp: " . $time_stamp);
        $curl->add_header("x-wac-access-token: " . $access_token);
        $curl->add_header("x-wac-signature: " . $signature);
        
        // 需要调试时开启(查看request/reponse...) 不需要时设置为：false
        $curl->set_verbose($is_debug);

        // 业务请求
        $uri = \wacai\open\config\WebConfig::GW_URL . '/' . $this->api_name . '/' . $this->api_version;

        $err = $curl->http_post_json($uri, $json_data, $res, false, \wacai\open\config\WebConfig::DEFAULT_ENCODING);
        if ($err != NULL) {
            throw new Exception("请求出错" . $res);
        } else {
            // 检查是否token过期
            $isExpireToken = $this->token_service->checkExpire($res);
            if ($isExpireToken) {
                // 如果token过期，则强制缓存刷新
                $this->token_service->getToken(true);
            }
        }
    }
}

?>
