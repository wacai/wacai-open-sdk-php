<?php

/**
 * Local cache
 * Class LocalCache
 */
class LocalCache
{
    static private $_instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$_instance instanceof LocalCache) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }


    const MAX_POOL_SIZE = 500;

    private $cache = [];

    /**
     * 生成缓存key
     * @param $object
     * @param $method
     * @param $args
     * @return string
     */
    private function get_cache_key($object, $method, $args)
    {
        return get_class($object) . '->' . $method . '(' . md5(json_encode($args)) . ')';
    }

    /**
     * @param $object
     * @param $method
     * @param $args
     * @param bool $is_force_expire 是否强制过期(默认情况下)
     * @return mixed
     */
    public function get($object, $method, $args, $is_force_expire = FALSE)
    {
        $cache_key = $this->get_cache_key($object, $method, $args);
        if ($is_force_expire) {
            $this->cache[$cache_key] = call_user_func_array([$object, $method], $args);
        } else {
            if (!array_key_exists($cache_key, $this->cache)) {
                $this->check_and_clear_cache();
                $this->cache[$cache_key] = call_user_func_array([$object, $method], $args);
            }
        }
        return $this->cache[$cache_key];
    }

    /**
     * 检查缓存大小并清空
     */
    private function check_and_clear_cache()
    {
        if ($this->get_size() > self::MAX_POOL_SIZE) {
            $this->clear_all();
        }
    }

    /**
     * 获取缓存大小
     * @return int
     */
    public function get_size()
    {
        return sizeof($this->cache);
    }

    /**
     * 清空缓存
     */
    public function clear_all()
    {
        unset($this->cache);
        $this->cache = [];
    }
}

?>
