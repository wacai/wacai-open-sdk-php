<?php

/**
 * Web socket(frame帧-包体)
 * Class Frame
 */
class Frame
{
    public function __construct()
    {
    }

    /**
     * Frame帧(整个包长度)
     */
    public $length;
    /**
     * Header长度
     */
    public $header_length;
    /**
     * Header响应 header
     */
    public $header;
    /**
     * 消息列表(详细属性见message)
     */
    public $message_list = [];
}

?>