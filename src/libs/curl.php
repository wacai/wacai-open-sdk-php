<?php
namespace wacai\open\lib;
/**
 * curl封装
 */
class Curl {

    /**
     * curl句柄
     */
    private $curl;

    /**
     * 全局超时，默认30s
     */
    private $global_timeout = 30000;

    /**
     * 连接超时，默认10s
     */
    private $conn_timeout = 10000;

    /**
     * 持久连接超时，单位秒，0禁用
     */
    private $keep_alive_timeout = 300;

    /**
     * http请求头
     */
    private $header = array(
        'Accept: */*'
    );

    /**
     * 自定义请求方法，有HEAD、DELETE、PUT等
     */
    private $custom_method = NULL;

    /**
     * 是否启用调试模式
     */
    private $enable_verbose = FALSE;

    /**
     * post请求时的分界线
     */
    private static $boundary = '----------------------------8223117e5cec';

    /**
     * cookie数组
     */
    private $cookies = array();

    /**
     * 代理agent
     */
    private $user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322)';

    /**
     * 是否需要证书
     */
    private $need_cert = FALSE;

    /**
     * ssl相关配置
     */
    private $ssl_port = 443;
    private $ssl_cert_path = '';
    private $ssl_cert_passwd = '';
    private $ssl_cert_type = 'pem';


    public function __construct($config = array()) {
        if (!empty($config)) {
            foreach ($config as $key => $val) {
                $this->$key = $val;
            }
        }

        $this->curl = curl_init();
        curl_setopt_array($this->curl, array(
            //返回文本
            CURLOPT_RETURNTRANSFER => TRUE,
            //允许重定向
            CURLOPT_FOLLOWLOCATION => TRUE,
            //最大重定向次数
            CURLOPT_MAXREDIRS => 10,
            //设置毫秒超时必须启用
            CURLOPT_NOSIGNAL => 1,
            //连接超时
            CURLOPT_CONNECTTIMEOUT_MS => $this->conn_timeout,
            //curl执行超时
            CURLOPT_TIMEOUT_MS => $this->global_timeout,
            //验证对方证书
            CURLOPT_SSL_VERIFYPEER => FALSE,
            //验证对方证书的host
            CURLOPT_SSL_VERIFYHOST => FALSE,
            //不支持session cookie
            CURLOPT_COOKIESESSION => TRUE,
            //ua
            CURLOPT_USERAGENT => $this->user_agent,
            //启用调试
            CURLOPT_VERBOSE => $this->enable_verbose,
        ));
    }

    public function set_opt($key, $val) {
        curl_setopt($this->curl, $key, $val);
    }

    public function set_custom_method($custom_method) {
        $this->custom_method = $custom_method;
    }

    public function set_verbose($enable) {
        $this->enable_verbose = $enable;
        curl_setopt($this->curl, CURLOPT_VERBOSE, $enable);
    }

    public function add_header($header) {
        $this->header[] = $header;
    }

    public function set_cookies(array $cookies) {
        $this->cookies = $cookies;
    }

    public function get_cookies() {
        return $this->cookies;
    }

    public function set_user_agent($user_agent) {
        $this->user_agent = $user_agent;
    }

    private function refresh_cookies() {
        if (empty($this->cookies)) {
            return;
        }
        $cookie_str = '';
        foreach ($this->cookies as $key => $val) {
            $cookie_str .= $key . '=' . $val . ';';
        }
        curl_setopt($this->curl, CURLOPT_COOKIE, $cookie_str);
    }

    public function __destruct() {
        is_resource($this->curl) && curl_close($this->curl);
    }

    public function close() {
        is_resource($this->curl) && curl_close($this->curl);
    }

    public function http_get($url, &$response, $charset = NULL) {
        return $this->http_exec($url, $response, $charset);
    }

    public function http_head($url, &$response) {
        curl_setopt($this->curl, CURLOPT_FILETIME, TRUE);
        curl_setopt($this->curl, CURLOPT_NOBODY, TRUE);
        curl_setopt($this->curl, CURLOPT_HEADER, TRUE);
        return $this->http_exec($url, $response, NULL);
    }

    public function http_post($url, $request, &$response, $is_multi_part = FALSE, $charset = NULL) {
        if (!$is_multi_part && is_array($request)) {
            $request = http_build_query($request);
            return $this->http_exec($url, $response, $charset, $request);
        }
        if ($is_multi_part && !is_array($request)) {
            $this->add_header('Content-Type: multipart/form-data; boundary=' . self::$boundary);
            $this->add_header('Content-Length: ' . strlen($request));
            $this->add_header('Expect: ');
        }
        return $this->http_exec($url, $response, $charset, $request);
    }

    public function http_post_json($url, $request, &$response, $charset = NULL) {
        if (is_array($request) || is_object($request)) {
            $request = json_encode($request);
        }
        $this->add_header('Content-type: application/json');
        curl_setopt($this->curl, CURLOPT_ENCODING, 'gzip,deflate');
        return $this->http_post($url, $request, $response, FALSE, $charset);
    }

    private function http_exec($url, &$response, $charset, $request = NULL) {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->keep_alive_timeout > 0) {
            $this->header[] = 'Keep-Alive: ' . $this->keep_alive_timeout;
            $this->header[] = 'Connection: keep-alive';
        } else {
            $this->header[] = 'Connection: close';
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->header);

        //如果定义了自定义方法，则使用自定义的方法
        if (is_string($this->custom_method)) {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->custom_method);
        }

        if (!empty($request)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $request);
        }

        //如果需要证书
        if ($this->need_cert) {
            if (!empty($this->ssl_port)) {
                curl_setopt($this->curl, CURLOPT_PORT, $this->ssl_port);
            }
            curl_setopt($this->curl, CURLOPT_SSLCERT, $this->ssl_cert_path);
            curl_setopt($this->curl, CURLOPT_SSLCERTPASSWD, $this->ssl_cert_passwd);
            curl_setopt($this->curl, CURLOPT_SSLCERTTYPE, $this->ssl_cert_type);
        }

        //刷新cookies
        $this->refresh_cookies();
        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'header_callback'));

        $response = curl_exec($this->curl);
        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, 'self::fake_header_callback');
        if ($response === FALSE) {
            return $this->get_error_msg();
        }
        if ($charset) {
            $response = iconv($charset, 'UTF-8//IGNORE', $response);
        }
        return FALSE;
    }

    /**
     * 返回出错提示
     */
    public function get_error_msg() {
        return 'errorno=' . curl_errno($this->curl) . ',errmsg=' . curl_error($this->curl);
    }

    public function get_transfer_info() {
        return curl_getinfo($this->curl);
    }

    /**
     * 返回http响应码
     */
    public function get_http_resp_code() {
        return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    }

    /**
     * 返回HTTP响应Header中的Location的值(重定向URL)。如果对方服务器未设置值，则默认返回请求的URL
     * 需要首先设置一下跟踪重定向：$this->curl->set_opt(CURLOPT_FOLLOWLOCATION, 1);
     */
    public function get_resp_header_location() {
        return curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);
    }

    /**
     * header回调函数，提取需要的header
     */
    private function header_callback($curl, $header) {
        //处理cookie
        if (!strncmp($header, "Set-Cookie:", 11)) {
            $str = trim(substr($header, 11));
            $pos = strpos($str, ';');
            if ($pos != FALSE) {
                $str = substr($str, 0, $pos);
            }
            $pos = strpos($str, '=');
            if ($pos != FALSE) {
                $name = substr($str, 0, $pos);
                $val = substr($str, $pos + 1);
                $this->cookies[$name] = $val;
            }
        }
        return strlen($header);
    }

    private static function fake_header_callback($curl, $header) {

    }
}
