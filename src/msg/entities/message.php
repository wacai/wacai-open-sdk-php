<?php

/**
 * 消息(单个消息)
 * Class Message
 */
class Message
{
    public function __construct()
    {
    }

    /**
     * Message key 长度
     */
    public $msg_key_length = 0;
    /**
     * Message key
     */
    public $msg_key = "";
    /**
     * 消息消费位点offset
     */
    public $msg_offset = 0;
    /**
     * Message payload 长度
     */
    public $payload_length = 0;
    /**
     * Message payload
     */
    public $payload = "";
}

?>
