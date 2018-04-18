<?php
require_once dirname(__DIR__) . '/libs/curl.php';
require_once dirname(__DIR__) . '/libs/base64.php';
require_once dirname(__DIR__) . '/config/web_config.php';
require_once dirname(__DIR__) . '/libs/utils.php';
require_once dirname(__DIR__) . '/libs/cache/local_cache.php';
require_once dirname(__FILE__) . '/token.php';

class TokenService
{
    const URL_FETCH = '/token';
    const URL_REFRESH = '/refresh';

    public function getToken($is_force_expire = FALSE)
    {
        return LocalCache::getInstance()->get($this, 'fetch', ['token'], $is_force_expire);
    }

    /**检查token是否过期或无效
     * @param $res
     * @return bool
     */
    public function checkExpire($res)
    {
        /*
        INVALID_REFRESH_TOKEN(10010, "非法的refresh_token"),
        ACCESS_TOKEN_EXPIRED(10011, "access_token已过期"),
        INVALID_ACCESS_TOKEN(10012, "access_token非法"),
        REFRESH_TOKEN_EXPIRED(10013, "refresh_token已过期"),;
        */
        $error = json_decode($res);
        $code = $error->{'code'};
        $error_message = $error->{'error'};
        // token过期
        if ($code == '10011' || $code == '10013') {
            return true;
        }
        return false;
    }

    /**
     * 获取token
     * @param $flag
     * @return Token
     */
    public function fetch($flag)
    {
        $param = [
            'app_key' => WebConfig::APP_KEY,
            'grant_type' => 'client_credentials',
            'timestamp' => Util::getMillisecond(),
        ];
        // 签名参数组装
        $strToSign = '';
        foreach ($param as $key => $val) {
            $strToSign .= $val;
        }
        // 签名
        $param['sign'] = Base64::base64url_encode(hash_hmac('sha256', $strToSign, WebConfig::APP_SECRET, true));
        $curl = new \Curl();
        // 获取token的url
        $token_url = WebConfig::GW_TOKEN_URL . self::URL_FETCH;
        // 发起请求(获取token)
        $err = $curl->http_post($token_url, $param, $res);

        if ($err != NULL) {
            echo "获取token请求出错!";
        } else {
            // token字符串解析
            return $this->parse($res);
        }
    }

    public function refresh($refresh_token)
    {
        // 待签名的参数
        $param = [
            'app_key' => WebConfig::APP_KEY,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'timestamp' => Util::getMillisecond(),
        ];

        ksort($param);

        $strToSign = '';
        foreach ($param as $key => $val) {
            $strToSign .= $val;
        }

        $param['sign'] = Base64::base64url_encode(hash_hmac('sha256', $strToSign, WebConfig::APP_SECRET, true));

        $curl = new \Curl();
        // 获取token的url
        $refresh_token_url = WebConfig::GW_TOKEN_URL . self::URL_REFRESH;
        $err = $curl->http_post($refresh_token_url, $param, $res);

        if ($err != NULL) {
            echo "请求出错!";
        }else {
            // token字符串解析
            return $this->parse($res);
        }
    }

    private function parse($json_res){
        // token字符串解析
        $json_token = json_decode($json_res);
        $access_token = $json_token->{'access_token'};
        $refresh_token = $json_token->{'refresh_token'};
        $expires_in = $json_token->{'expires_in'};
        $token = new Token($access_token, $refresh_token, $expires_in);
        return $token;
    }
}

?>
