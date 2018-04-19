<?php

/**
 * Ack result
 */
class AckResult
{
    public function __construct()
    {
    }

    /**
     * 是否成功(true=成功,false=失败)
     */
    public $is_ok = false;
    /**
     * 错误消息(if $is_ok<>false)
     */
    public $error_message = "";
}

?>