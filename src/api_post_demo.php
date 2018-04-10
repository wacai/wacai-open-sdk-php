<?php
require_once './curl.php';
require_once './base64.php';
require_once './web_config.php';
require_once './utils.php';
require_once './local_cache.php';
require_once './token_service.php';

// Token服务
$token_service = new TokenService();
$token = $token_service->getToken();

// 调用的API接口
$api_name = "api.test.post.fixed";
// 调用的API版本
$api_version = "1.0";

// 业务参数(json)--demo
$body_data = '{"uid":123,"name":"zy"}';
// 业务数据md
$body_md5 = base64_encode(md5($body_data, true));

// 时间戳
$time_stamp = Util::getMillisecond();

// http-header
$param_header = [
    'x-wac-version' => WebConfig::X_WAC_VERSION,
    'x-wac-timestamp' => $time_stamp,
    'x-wac-access-token' => $token->getAccessToken(),
];
//排序(计算的header按照headerName的字母表升序排列)
ksort($param_header);
// 组装head-string
foreach ($param_header as $key => $val) {
    $headerString .= "$key=$val" . '&';
}
$headerString = substr($headerString, 0, strlen($headerString) - 1);
// 待签名的数据
$strToSign = $api_name . '|' . $api_version . '|' . $headerString . '|' . $body_md5;
// 签名(signature)
$signature = Base64::base64url_encode(hash_hmac('sha256', $strToSign, WebConfig::APP_SECRET, true));


$curl = new \Curl();
// 设置请求的header
$curl->add_header("x-wac-version: " . WebConfig::X_WAC_VERSION);
$curl->add_header("x-wac-timestamp: " . $time_stamp);
$curl->add_header("x-wac-access-token: " . $token->getAccessToken());
$curl->add_header("x-wac-signature: " . $signature);
// 需要调试时开启(查看request/reponse...) 不需要时设置为：false
$curl->set_verbose(true);
// 业务请求
$uri = WebConfig::GW_URL . '/' . $api_name . '/' . $api_version;
$err = $curl->http_post_json($uri, $body_data, $res, false, "UTF-8");
if ($err != NULL) {
    echo "请求出错!";
} else {
    // 检查是否token过期
    $isExpireToken = $token_service->checkExpire($res);
    if ($isExpireToken) {
        $token_service->getToken(true);
    }
}
var_dump($res);
?>
