<?php
require_once './curl.php';
require_once './base64.php';
require_once './web_config.php';
require_once './utils.php';
require_once './local_cache.php';
require_once './token_service.php';

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
        $this->token_service = new TokenService();
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
        // 业务数据md
        $body_md5 = base64_encode(md5($json_data, true));
        // 时间戳
        $time_stamp = Util::getMillisecond();

        $access_token = $this->token->getAccessToken();
        // http-header
        $param_header = [
            'x-wac-version' => WebConfig::X_WAC_VERSION,
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
        // 签名(signature)
        $signature = Base64::base64url_encode(hash_hmac('sha256', $strToSign, WebConfig::APP_SECRET, true));

        $curl = new \Curl();
        // 设置请求的header
        $curl->add_header("x-wac-version: " . WebConfig::X_WAC_VERSION);
        $curl->add_header("x-wac-timestamp: " . $time_stamp);
        $curl->add_header("x-wac-access-token: " . $access_token);
        $curl->add_header("x-wac-signature: " . $signature);
        // 需要调试时开启(查看request/reponse...) 不需要时设置为：false
        $curl->set_verbose($is_debug);

        // 业务请求
        $uri = WebConfig::GW_URL . '/' . $this->api_name . '/' . $this->api_version;
        $err = $curl->http_post_json($uri, $json_data, $res, false, WebConfig::DEFAULT_ENCODING);
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
