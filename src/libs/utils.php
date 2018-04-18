<?php

class Util
{
    // 生成(当前时间戳，以毫秒为单位)
    public static function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 随机数
     * @param $min
     * @param $max
     * @return int
     */
    public static function rand($min, $max)
    {
        return rand($min, $max);
    }
    
    /**
     * 获取服务器端IP地址
     * @return string
     */
    function get_server_ip()
    {
        if (isset($_SERVER)) {
            if ($_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }
}

?>
