<?php
//测试报文为空的情况
require_once dirname(dirname(__DIR__)) . '/api/http_client.php';
// 调用的API接口(for Demo测试)
$api_name = "api.test.post.fixed";
// 调用的API版本(for Demo测试)
$api_version = "1.0";
// Http Client Api初始化
$client_api = new HttpClient($api_name, $api_version);
$client_api->http_post_json("", false, $res);
// 查看调动结果
var_dump($res);
?>
