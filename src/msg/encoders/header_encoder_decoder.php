<?php
namespace wacai\open\msg\encoders;
/**
 * Class HeaderEncoder
 */
class HeaderEncoder
{
    /**
     *  Decode header
     * @param $header
     * @return MessageHeader
     */
    public static function decode($header)
    {
        $msg_header = new \wacai\open\msg\entities\MessageHeader();
        $unpack_format = "nflag/ncode/nresp_code/Nopaque/ntopic_length";
        $arr_unpack = unpack($unpack_format, $header);
        $msg_header->flag = $arr_unpack["flag"];
        $msg_header->code = $arr_unpack["code"];
        $msg_header->resp_code = $arr_unpack["resp_code"];
        $msg_header->opaque = $arr_unpack["opaque"];
        $topic_length = $arr_unpack["topic_length"];

        $unpack_format .= "/a" . $topic_length . "topic";
        $arr_unpack = unpack($unpack_format, $header);
        $msg_header->topic = $arr_unpack["topic"];

        $unpack_format .= "/n" . "remark_length";
        $arr_unpack = unpack($unpack_format, $header);
        $remark_length = $arr_unpack["remark_length"];

        $unpack_format .= "/a" . $remark_length . "remark";
        $arr_unpack = unpack($unpack_format, $header);
        $msg_header->remark = $arr_unpack["remark"];

        $unpack_format .= "/n" . "ext_fields_length";
        $arr_unpack = unpack($unpack_format, $header);
        $ext_fields_length = $arr_unpack["ext_fields_length"];

        if ($ext_fields_length > 0) {
            $unpack_format .= "/a" . $ext_fields_length . "ext_fields";
            $arr_unpack = unpack($unpack_format, $header);
            $ext_fields = $arr_unpack["ext_fields"];

            $ext_item = [];
            $item_kv_length = 0;
            $total_kv_length = 0;
            $unpack_format_ef = "nkey_size";

            while ($total_kv_length < $ext_fields_length) {
                $arr_key_size = unpack($unpack_format_ef, $ext_fields);
                $key_size = $arr_key_size["key_size"];

                $unpack_format_ef .= "/a" . $key_size . "key";
                $arr_key = unpack($unpack_format_ef, $ext_fields);
                $key = $arr_key["key"];

                $unpack_format_ef .= "/nvalue_size";
                $arr_value_size = unpack($unpack_format_ef, $ext_fields);
                $value_size = $arr_value_size["value_size"];

                $unpack_format_ef .= "/a" . $value_size . "value";
                $arr_value = unpack($unpack_format_ef, $ext_fields);
                $value = $arr_value["value"];

                // set ext_field
                $ext_item[$key] = $value;

                $item_kv_length = self::get_single_ext_field_length($key_size, $value_size);
                $total_kv_length += $item_kv_length;

                // read next key/value
                $unpack_format_ef .= "/nkey_size";
            }

            $msg_header->ext_fields = $ext_item;
        }

        return $msg_header;
    }

    /**
     * @param $messageHeader
     * @return string
     */
    public static function encode($messageHeader)
    {
        // topic length
        $topic_length = self::get_topic_length($messageHeader);
        // remark length
        $remark_length = self::get_remark_length($messageHeader);
        // ext_fields length
        $ext_fields_length = self::get_ext_fields_length($messageHeader->ext_fields);

        // encoding
        $bin_header = '';
        $bin_header .= pack("n", $messageHeader->flag);
        $bin_header .= pack("n", $messageHeader->code);
        $bin_header .= pack("n", $messageHeader->resp_code);
        $bin_header .= pack("N", $messageHeader->opaque);
        // topic
        if (isset($messageHeader->topic) && !empty($messageHeader->topic)) {
            $bin_header .= pack("n", $topic_length);
            $bin_header .= pack("a" . $topic_length, $messageHeader->topic);
        } else {
            $bin_header .= pack("n", 0);
        }

        // remark
        if (isset($messageHeader->remark) && !empty($messageHeader->remark)) {
            $bin_header .= pack("n", $remark_length);
            $bin_header .= pack("a" . $remark_length, $messageHeader->remark);
        } else {
            $bin_header .= pack("n", 0);
        }

        // ext_fields
        $bin_header_txt_fields = '';
        $bin_header_txt_field = '';
        if ($ext_fields_length > 0) {
            foreach ($messageHeader->ext_fields as $key => $val) {
                $key_length = strlen($key);//mb_strlen($key, "utf-8");
                $val_length = strlen($val);//mb_strlen($val, "utf-8");
                $bin_header_txt_field = '';
                // key and size
                $bin_header_txt_field .= pack("n", $key_length);
                $bin_header_txt_field .= pack("a" . $key_length, $key);
                // value and size
                $bin_header_txt_field .= pack("n", $val_length);
                $bin_header_txt_field .= pack("a" . $val_length, $val);

                $bin_header_txt_fields .= $bin_header_txt_field;
            }

            $bin_header .= pack("n", $ext_fields_length);
            $bin_header .= $bin_header_txt_fields;
        } else {
            $bin_header .= pack("n", 0);
        }

        return $bin_header;
    }

    public static function get_header_total_length($messageHeader)
    {
        // topic length
        $topic_length = self::get_topic_length($messageHeader);
        // remark length
        $remark_length = self::get_remark_length($messageHeader);
        // ext_fields length
        $ext_fields_length = self::get_ext_fields_length($messageHeader->ext_fields);

        // header total length
        $total_length = self::get_header_length($topic_length, $remark_length, $ext_fields_length);

        return $total_length;
    }

    /** 获取单个ext_field(key/value)的长度
     * @param $key_length
     * @param $value_length
     * @return int
     */
    private static function get_single_ext_field_length($key_length, $value_length)
    {
        $length = 2
            + $key_length // key length
            + 2
            + $value_length; // value length
        return $length;
    }

    private static function get_header_length($topic_length, $remark_length, $ext_fields_length)
    {
        $length = 2 // flag
            + 2 // code
            + 2 // resp_code
            + 4 // opaque
            + 2 // topic length
            + $topic_length
            + 2 // remark length
            + $remark_length
            + 2 // ext_fields length
            + $ext_fields_length;
        return $length;
    }

    private static function get_topic_length($messageHeader)
    {
        $topic_length = 0;
        if (isset($messageHeader->topic) && !empty($messageHeader->topic)) {
            //mb_strlen($messageHeader->topic, "utf-8");
            $topic_length = strlen($messageHeader->topic);
        }
        return $topic_length;
    }

    private static function get_remark_length($messageHeader)
    {
        $remark_length = 0;
        if (isset($messageHeader->remark) && !empty($messageHeader->remark)) {
            //mb_strlen($messageHeader->remark, "utf-8");
            $remark_length = strlen($messageHeader->remark);
        }
        return $remark_length;
    }

    private static function get_ext_fields_length($ext_fields = [])
    {
        $ext_fields_length = 0;
        $kv_length = 0;
        $ext_fields_copy = $ext_fields;
        if (!empty($ext_fields_copy) && count($ext_fields_copy) > 0) {
            foreach ($ext_fields_copy as $key => $val) {
                $kv_length = self::get_single_ext_field_length(strlen($key), strlen($val));
                $ext_fields_length += $kv_length;
            }
        }
        return $ext_fields_length;
    }
}

?>