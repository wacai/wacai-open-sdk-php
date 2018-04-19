<?php
require_once dirname(dirname(__DIR__)) . "/msg/entities/header.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/message.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/body.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/frame.php";
require_once dirname(dirname(__DIR__)) . "/msg/message_http_client.php";

//(for internal testing)
$topic = "middleware.guard.cache";
$messageClient = new HttpClientMessage();
print_r(">>>Start pull\r\n");
$message = $messageClient->pull($topic);
var_dump($message);
print_r(">>>End pull\r\n");

// 目前，仅支持pull一条
$offset = $message->msg_offset;
print_r(">>>Start ack\r\n");
$resp_header = $messageClient->ack($topic, $offset);
print_r("Ack result:");
var_dump($resp_header);
print_r(">>>End ack\r\n");
?>