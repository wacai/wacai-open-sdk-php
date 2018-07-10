<?php
namespace wacai\open\config;
class  WebConfig
{
    // API网关地址
    const GW_URL = "http://open.wacaiyun.com/gw/api_entry";
    // 消息网关地址
    const GW_MESSAGE_URL = "open.wacaiyun.com";
    // 消息网关path
    const GW_MESSAGE_URL_PATH = "/ws";
    // 消息网关端口
    const GW_MESSAGE_URL_PORT = 80;
    // App_key(for测试 不能用于生产)
    const APP_KEY = "fsxd8a885fm8";
    // App_secret(for测试 不能用于生产)
    const APP_SECRET = "0f88a1b5ec034120bb6194119dc16359";
    // WAC_Version
    const X_WAC_VERSION = "4";
    // 当前编码
    const DEFAULT_ENCODING = "UTF-8";
    // 是否调试模式(默认:false)
    const IS_DEBUG = false;
}

?>
