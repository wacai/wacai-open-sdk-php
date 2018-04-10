<?php
require_once './client_api.php';
// 调用的API接口
$api_name = "api.test.post.fixed";
// 调用的API版本
$api_version = "1.0";
// Api服务
$client_api = new ClientApi($api_name, $api_version);
// 业务参数(json)--demo
$body_data = '{"uid":123,"name":"zy"}';

$client_api->http_post($body_data, false, $res);
var_dump($res);
?>
