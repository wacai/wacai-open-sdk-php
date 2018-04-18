<?php
require_once "../entities/message.php";
require_once "../encoders/message_encoder_decoder.php";

$message = new Message();
$message->msg_key = "wacai.openplatform.guard.cache";
$message->msg_key_length = strlen($message->msg_key);
$message->msg_offset = 78342131231212312312;
$message->payload = "wacai.message.payload";
$message->payload_length = strlen($message->payload); //mb_strlen($message->payload, "UTF-8");
$bin_encode = MessageEncoder::encode($message);
//var_dump($bin_encode);
$message_decode = MessageEncoder::decode(0, $bin_encode);
print_r($message_decode);
?>
