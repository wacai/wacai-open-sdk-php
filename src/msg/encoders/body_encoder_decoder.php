<?php
require_once "message_encoder_decoder.php";

class BodyEncoder
{
    public static function encode($body = null, &$body_total_length = 0)
    {
        if (!isset($body) || empty($body)) {
            return;
        }

        // Message count
        $bin_body = pack("n", $body->message_count);

        // Message list total length
        $body_total_length = 2;//message count

        $body_single_length = 0;
        // Message list
        $arr_message = $body->arr_message;
        if (isset($arr_message) && is_array($arr_message) && count($arr_message) > 0) {
            foreach ($arr_message as $message) {
                $key_length = strlen($message->msg_key);
                $payload_length = strlen($message->payload);
                $message_length = MessageEncoder::get_message_length($key_length, $payload_length);

                $bin_message_length = pack("N", $message_length);
                $bin_message = MessageEncoder::encode($message);
                $bin_body .= $bin_message_length . $bin_message;

                $body_single_length = self::get_single_body_length($message_length);
                $body_total_length += $body_single_length;
            }
        }

        return $bin_body;
    }

    /**
     * @param $body
     */
    public static function decode($body)
    {
        $arr_body = [];
        $message_format = "nmessage_count";

        // message count
        $arr_message_count = unpack($message_format, $body);
        $message_count = $arr_message_count["message_count"];

        $message_length_format = '';
        $message_body_format = '';
        if ($message_count >= 1) {
            // first message length
            $message_length_format = $message_format . "/Nmessage_length";
            for ($i = 0; $i <= ($message_count - 1); $i++) {
                // read each message length
                $arr_message_length = unpack($message_length_format, $body);
                // read each message body
                $message_body_format = $message_length_format . "/a" . $arr_message_length["message_length"] . "message";
                $arr_body_message = unpack($message_body_format, $body);
                $message = $arr_body_message["message"];
                // parse each message
                $arr_body[$i] = MessageEncoder::decode(0, $message);
                // loop next message
                $message_length_format = $message_body_format . "/Nmessage_length";
            }
        }

        return $arr_body;
    }

    /**
     * 单个消息体(body)的长度
     * @param $msg_length
     * @return int
     */
    private static function get_single_body_length($msg_length)
    {
        $length = 4       // message length(4 byte)
            + $msg_length; // message_body_length
        return $length;
    }

    /**
     * 消息列表总长度(body length)
     * @param array $message_list
     * @return int
     */
    private static function get_body_total_length($body)
    {
        $total_length = 2;
        $arr_message = $body->arr_message;
        if (isset($arr_message) && is_array($arr_message) && count($arr_message) > 0) {
            foreach ($arr_message as $message) {
                $key_length = strlen($message->msg_key);
                $payload_length = strlen($message->payload);
                $message_length = MessageEncoder::get_message_length($key_length, $payload_length);

                $body_single_length = self::get_single_body_length($message_length);
                $total_length += $body_single_length;
            }
        }
        return $total_length;
    }
}

?>