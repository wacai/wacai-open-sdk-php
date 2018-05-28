<?php
namespace wacai\open\msg\encoders;
require_once dirname(__DIR__) . "/entities/body.php";
require_once "header_encoder_decoder.php";
require_once "body_encoder_decoder.php";

/**
 * 帧编码和解码类(web socket/frame一个的请求和响应报文)
 * Class FrameEncoder
 */
class FrameEncoder
{
    /**
     * * steps:
     * 1: unpack total_length and header_length
     * 2: unpack header and body
     * @param $bin_message
     */
    public static function decode($bin_message)
    {
        $frame = new Frame();
        $unpack_format = "Ntotal_length/nheader_length";
        // unpack total_length and header_length
        $arr_package = unpack($unpack_format, $bin_message);
        // total_length
        $total_length = $arr_package["total_length"];
        $frame->length = $total_length;
        // header_length
        $header_length = $arr_package["header_length"];
        $frame->header_length = $header_length;

        // body length
        $body_length = $total_length - $header_length - 2;

        // unpack header and body
        $unpack_format .= "/a" . $header_length . "header/a" . $body_length . "body";
        $arr_header_body = unpack($unpack_format, $bin_message);
        // header
        $header = HeaderEncoder::decode($arr_header_body["header"]);
        $frame->header = $header;

        // body
        if ($body_length > 0) {
            $body = BodyEncoder::decode($arr_header_body["body"]);
            $frame->message_list = $body;
        }
        return $frame;
    }

    public static function encode($messageHeader, $body = null)
    {
        $bin_package = '';
        $header_length = HeaderEncoder::get_header_total_length($messageHeader);

        $bin_body = '';
        $body_length = 0;
        if (isset($body) && !empty($body)) {
            $bin_body = BodyEncoder::encode($body, $body_length);
        }

        $total_length = $header_length + $body_length + 2;//header length
        // packet total length
        $bin_package .= pack("N", $total_length);
        // header_length
        $bin_package .= pack("n", $header_length);
        // header data
        $bin_package .= HeaderEncoder::encode($messageHeader);
        // body data
        if (!empty($bin_body)) {
            $bin_package .= $bin_body;
        }

        return $bin_package;
    }
}

?>