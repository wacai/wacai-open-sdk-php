<?php
namespace wacai\open\msg\entities;
/**
 * 消息集合
 * Class Body
 */
class Body
{
    public function __construct()
    {
    }

    /**
     * Message count 消息条数
     */
    public $message_count = 0;

    /**
     * 消息集合(详细属性见message类)
     */
    public $arr_message = [];
}

?>