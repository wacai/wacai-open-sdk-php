<?php
namespace wacai\open\msg\entities;
/**
 * Header
 * Class MessageHeader
 */
class MessageHeader
{
    public function __construct()
    {
    }

    /**
     * 通信层标志位
     */
    public $flag = 0;
    /**
     * 请求操作码， 请求接收 方根据此操作码做不同 的操作
     */
    public $code = 1;
    /**
     * 此字段在发送请求时填零。
     */
    public $resp_code = 0;
    /**
     * 请求发起方在同一连接上的不同请求标识代码，多线程连接复用使用。
     */
    public $opaque;
    /**
     * 消息主题
     */
    public $topic = null;
    /**
     * 自定义文本信息(错误详细描述信息)
     */
    public $remark = null;
    /**
     * 请求/应答自定义字段
     */
    public $ext_fields = [];
}

?>