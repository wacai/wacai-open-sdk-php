<?php
/*
demo for 如何获取token
*/
require_once dirname(dirname(__DIR__)) . '/token/token_service.php';
// token初始化
$token_service = new TokenService();
$token = $token_service->getToken();
var_dump($token);
?>