<?php
require_once "../entities/body.php";
require_once "../entities/message.php";
require_once "../encoders/body_encoder_decoder.php";

$body = new Body();
$body->message_count = 3;

$arr_message = [];
$message1 = new Message();
$message1->msg_key = "wacai.message.payload.test挖挖财1";
$message1->msg_key_length = strlen($message1->msg_key);
$message1->msg_offset = 781;
$message1->payload = "wacai.message.payload.test1";
$message1->payload_length = strlen($message1->payload);
array_push($arr_message, $message1);

$message2 = new Message();
$message2->msg_key = "wacai.openplatform.guard.cache基金.基金2";
$message2->msg_key_length = strlen($message2->msg_key);
$message2->msg_offset = 782;
$message2->payload = "wacai.message.payload2";
$message2->payload_length = strlen($message2->payload);
array_push($arr_message, $message2);

$message3 = new Message();
$message3->msg_key = "wacai.openplatform.guard.cache重宝3";
$message3->msg_key_length = strlen($message3->msg_key);
$message3->msg_offset = 783;
$message3->payload = "wacai.message.payload3";
$message3->payload_length = strlen($message3->payload);
array_push($arr_message, $message3);
$body->arr_message = $arr_message;

echo ">>>>>>start pack\r\n";
$total_length = 0;
$bin_body = BodyEncoder::encode($body, $total_length);
var_dump($bin_body);
echo ">>>>>>end pack\r\n";

echo ">>>>>>start unpack\r\n";
$body_unpack = BodyEncoder::decode($bin_body);
var_dump($body_unpack);
echo ">>>>>>end unpack\r\n";
?>