<?php
require_once dirname(dirname(__DIR__)) . '/api/http_client.php';
// 调用的API接口(for Demo测试)
$api_name = "usercenter.avoid.secret.landing";
// 调用的API版本(for Demo测试)
$api_version = "1.0";
// Http Client Api初始化
$client_api = new \wacai\open\api\HttpClient($api_name, $api_version);
// 业务参数-json格式(for Demo测试)
$body_data = '{"idNo":"123","merchantNo":"123","mob":"18357482673","openId":"123","reqDeviceid":"1222","reqIp":"122","sourceAf":"111","sourceMc":"2222"}';
$client_api->http_post_json($body_data, $res);
// 查看调动结果
var_dump($res);
?>