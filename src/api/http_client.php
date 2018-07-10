<?php
namespace wacai\open\api;
require_once dirname(__DIR__) . '/libs/curl.php';
require_once dirname(__DIR__) . '/libs/base64.php';
require_once dirname(__DIR__) . '/config/web_config.php';
require_once dirname(__DIR__) . '/libs/utils.php';
require_once dirname(__DIR__) . '/libs/debug_util.php';

class HttpClient
{
    private $api_name;
    private $api_version;

    // Token服务
    public function __construct($api_name, $api_version)
    {
        // 检查apiName and apiVersion
        if(empty($api_name) || empty($api_version)){
            throw new \Exception("Api name or version is null(from http_post_json)");
        }
        $this->api_name = $api_name;
        $this->api_version = $api_version;
    }

    /**
     * http-post调用
     * @param $json_data 业务参数 json格式
     * @param bool $is_debug 是否开启调试
     * @param ref $res response结果
     */
    public function http_post_json($json_data, &$res)
    {
        // 检查json数据是否为空
        if(empty($json_data)){
            throw new \Exception("json_data is null(from http_post_json)");
        }

        $body_md5 = '';
        if (!isset($json_data) || strlen($json_data) == 0) {
            $body_md5 = "1B2M2Y8AsgTpgAmY7PhCfg==";
        } else {
            // 业务数据md
            $body_md5 = base64_encode(md5($json_data, true));
        }

        // 时间戳
        $time_stamp = \wacai\open\lib\Util::getMillisecond();
        // http-header
        $param_header = [
            'x-wac-version' => \wacai\open\config\WebConfig::X_WAC_VERSION,
            'x-wac-timestamp' => $time_stamp,
            'x-wac-app-key' => \wacai\open\config\WebConfig::APP_KEY,
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
        $curl->add_header("x-wac-app-key: " . \wacai\open\config\WebConfig::APP_KEY);
        $curl->add_header("x-wac-signature: " . $signature);
        
        // 需要调试时开启(查看request/reponse...) 默认设置：false
        $curl->set_verbose(\wacai\open\config\WebConfig::IS_DEBUG);

        // 业务请求
        $uri = \wacai\open\config\WebConfig::GW_URL . '/' . $this->api_name . '/' . $this->api_version;

        $err = $curl->http_post_json($uri, $json_data, $res, false, \wacai\open\config\WebConfig::DEFAULT_ENCODING);
        if ($err != NULL) {
            throw new Exception("请求出错" . $res);
        }
    }               
}
?>
