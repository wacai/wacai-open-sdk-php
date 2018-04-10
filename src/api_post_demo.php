<?php
require_once './client_api.php';
// 调用的API接口
$api_name = "api.test.post.fixed";
// 调用的API版本
$api_version = "1.0";
// Api服务初始化
$client_api = new ClientApi($api_name, $api_version);
// 业务参数(json格式)
$body_data = '{"uid":123,"name":"zy"}';
// Api调用
$client_api->http_post($body_data, false, $res);
// 查看调动结果
var_dump($res);
?>
