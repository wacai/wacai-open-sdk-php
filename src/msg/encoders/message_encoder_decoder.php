<?php
namespace wacai\open\msg\encoders;
class MessageEncoder
{
    /**message encode
     * @param $message
     * @return string
     */
    public static function encode($message)
    {
        $bin_message = pack("n", $message->msg_key_length);
        $bin_message .= pack("a" . $message->msg_key_length, $message->msg_key);
        $bin_message .= pack("J", $message->msg_offset);
        $bin_message .= pack("N", $message->payload_length);
        $bin_message .= pack("a" . $message->payload_length, $message->payload);
        return $bin_message;
    }

    /**
     * message decode
     * @param $message_length
     * @param $message_body
     * @return Message
     */
    public static function decode($message_length, $message_body)
    {
        $message = new \wacai\open\msg\entities\Message();
        $unpack_format = "nmsg_key_length";

        // msg key length
        $arr_key_length = unpack($unpack_format, $message_body);
        $meg_key_length = $arr_key_length["msg_key_length"];
        $message->msg_key_length = $meg_key_length;

        $unpack_format .= "/a" . $meg_key_length . "msg_key";
        $arr_meg_key = unpack($unpack_format, $message_body);
        $message->msg_key = $arr_meg_key["msg_key"];

        $unpack_format .= "/Joffset/Npayload_length";
        $arr = unpack($unpack_format, $message_body);
        $message->msg_offset = $arr["offset"];

        $payload_length = $arr["payload_length"];
        $message->payload_length = $payload_length;

        $unpack_format .= "/a" . $payload_length . "payload";
        $arr = unpack($unpack_format, $message_body);
        $message->payload = $arr["payload"];
        return $message;
    }

    /**
     * 单个消息体(body)的长度
     * @param $msg_key_length
     * @param $payload_length
     * @return int
     */
    public static function get_message_length($msg_key_length, $payload_length)
    {
        $length = 2
            + $msg_key_length // message_key_length
            + 8
            + 4
            + $payload_length; // payload_length
        return $length;
    }
}

?>