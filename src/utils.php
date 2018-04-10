<?php

class Util
{
    // 生成(当前时间戳，以毫秒为单位)
    public static function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}

?>
