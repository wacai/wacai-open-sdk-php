<?php
use wacai\open\api;
require_once dirname(dirname(__DIR__)) . '/api/http_client.php';
// 调用的API接口(for Demo测试)
$api_name = "api.test.post.fixed";
// 调用的API版本(for Demo测试)
$api_version = "1.0";
// Http Client Api初始化
$client_api = new wacai\open\api\HttpClient($api_name, $api_version);
// 业务参数-json格式(for Demo测试)
$body_data = '{"uid":123,"name":"zy"}';
// Api调用(true开启debug调试,false=非debug模式)
//$res = '{"code":10011,"error":"access_token已过期"}';
$client_api->http_post_json($body_data, $res);
// 查看调动结果
var_dump($res);
?>
