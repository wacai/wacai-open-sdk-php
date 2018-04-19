<?php
require_once dirname(dirname(__DIR__)) . "/msg/entities/header.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/message.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/body.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/frame.php";
require_once dirname(dirname(__DIR__)) . "/msg/message_http_client.php";

//(for internal testing)
$topic = "middleware.guard.cache";
$messageClient = new HttpClientMessage();
print_r(">>>Start consume\r\n");
$result = $messageClient->consume($topic,"consume_message");
var_dump($result);
print_r(">>>End consume\r\n");

/**
* 业务处理函数,
* Message处理成功返回true(此时消息ack成功),否则返回false
*/
function consume_message($message_content){
	//var_dump($message_content);
	// 业务逻辑处理...
	return true;
}
?>