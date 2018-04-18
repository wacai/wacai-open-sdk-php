<?php
require_once "../entities/header.php";
require_once "../entities/message.php";
require_once "../entities/frame.php";
require_once "../encoders/frame_encoder_decoder.php";
require_once dirname(dirname(__DIR__)) . "/framework/libs/utils.php";

$header = get_header();
$body = create_body();

echo ">>>>>>start pack\r\n";
$bin_response = FrameEncoder::encode($header, $body);
var_dump($bin_response);
echo ">>>>>>end pack\r\n";

echo ">>>>>>start unpack\r\n";
$frame = FrameEncoder::decode($bin_response);
var_dump($frame);
echo ">>>>>>end unpack\r\n";


function get_header()
{
    $header = new MessageHeader();
    $header->flag = 1;
    $header->code = 0;
    $header->resp_code = 0;
    $header->opaque = Util::rand(100000, 999999);
    $header->topic = "wacai.openplatform.ocean.guard.cache";
    $header->remark = "remark";
    $ext_fields = [];
    $ext_fields["key1"] = "value1";
    $ext_fields["key2"] = "value2";
    $ext_fields["key3"] = "value3";
    $header->ext_fields = $ext_fields;

    return $header;
}

function create_body()
{
    $body = new Body();
    $body->message_count = 3;

    $arr_message = [];
    $message1 = new Message();
    $message1->msg_key = "wacai.message.payload.test挖财1";
    $message1->msg_key_length = strlen($message1->msg_key);
    $message1->msg_offset = 781;
    $message1->payload = "wacai.message.payload1";
    $message1->payload_length = strlen($message1->payload);
    array_push($arr_message, $message1);

    $message2 = new Message();
    $message2->msg_key = "wacai.message.payload.test挖财2";
    $message2->msg_key_length = strlen($message2->msg_key);
    $message2->msg_offset = 782;
    $message2->payload = "wacai.message.payload2";
    $message2->payload_length = strlen($message2->payload);
    array_push($arr_message, $message2);

    $message3 = new Message();
    $message3->msg_key = "wacai.message.payload.test挖财3";
    $message3->msg_key_length = strlen($message3->msg_key);
    $message3->msg_offset = 783;
    $message3->payload = "wacai.message.payload3";
    $message3->payload_length = strlen($message3->payload);
    array_push($arr_message, $message3);

    $body->arr_message = $arr_message;

    return $body;
}

?>