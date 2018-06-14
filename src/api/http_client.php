<?php
namespace wacai\open\api;
require_once dirname(__DIR__) . '/libs/curl.php';
require_once dirname(__DIR__) . '/libs/base64.php';
require_once dirname(__DIR__) . '/config/web_config.php';
require_once dirname(__DIR__) . '/libs/utils.php';
require_once dirname(__DIR__) . '/libs/cache/local_cache.php';
require_once dirname(__DIR__) . '/token/token_service.php';

class HttpClient
{
    private $api_name;
    private $api_version;
    private $token_service;
    private $token;
    // Token文件Cache目录
    private static $cache_folder = '/cache_folder/';
    // Token文件Cache文件名称
    private static $cache_file_name = \wacai\open\config\WebConfig::APP_KEY . '.txt';

    // Token服务
    public function __construct($api_name, $api_version)
    {
        $this->api_name = $api_name;
        $this->api_version = $api_version;
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

        // 加载获取access_token
        $access_token = $this->load_access_token();

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
            $this->check_refresh_token($res);
        }
    }

    // 检查token是否过期(过期的话重新加载)
    private function check_refresh_token($res){
      // 检查是否token过期
      if(empty($this->token_service)){
        $this->token_service = new \wacai\open\token\TokenService();
      }
      // 检查是否token过期
      $is_expire = $this->token_service->checkExpire($res);
      if($is_expire){
        $this->token_service->getToken(true);
        // 检查token是否为空
        if(is_object($this->token) && !empty($this->token)){
            // access token
            $this->save_token_to_file($this->token);
        }
      }
    }

    // Acces_token加载
    private function load_access_token(){
          // token初始化
        if(empty($this->token)){
            // 首先从文件加载token
            $this->token = $this->get_token_from_file();
            // 如果加载失败 则再次获取(调用token服务)
            if(empty($this->token)){
                $this->token_service = new \wacai\open\token\TokenService();
                $this->token = $this->token_service->getToken();
                // 检查token是否为空
                if(is_object($this->token) && !empty($this->token)){
                    // access token
                    $access_token = $this->token->getAccessToken();
                    $this->save_token_to_file($this->token);
                }
            }else{
                 $access_token = $this->token->getAccessToken();
            }
        }else{
            $access_token = $this->token->getAccessToken();
        } 

        if(empty($access_token)){
            throw new \Exception("token is null");
        } 
        return $access_token; 
    }

    private static function get_token_file_path(){
        return dirname(__DIR__) . self::$cache_folder . self::$cache_file_name;
    }

    private function get_token_from_file(){
        $cache_file_path = self::get_token_file_path();
        if(!file_exists($cache_file_path)){
            return;
        }
        // 读取
        $token = unserialize(file_get_contents($cache_file_path));
        if(!empty($token)){
            //print_r('读取token from file');
        }
        return $token;
    }

    private function save_token_to_file($token){
        if(empty($token)){
            return;
        }
        $cache_file_path = self::get_token_file_path();
        // 检测token文件是否存在 
        if(file_exists($cache_file_path)){
            unlink($cache_file_path);
        }
        // token序列化
        $token_string = serialize($token);
        // 序列化存储到文件
        $fh = fopen($cache_file_path, "w");
        fwrite($fh, $token_string);
        fclose($fh);
        //print_r('写入token to file');
    }                                   
}
?>
