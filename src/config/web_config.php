<?php
namespace wacai\open\config;
class  WebConfig
{
    // API网关地址
    const GW_URL = "http://open.wacaiyun.com/gw/api_entry";
    // API_网关_Token获取地址
    const GW_TOKEN_URL = "http://open.wacaiyun.com/token/auth";
    // 消息网关地址
    const GW_MESSAGE_URL = "open.wacaiyun.com";
    // 消息网关path
    const GW_MESSAGE_URL_PATH = "/ws";
    // 消息网关端口
    const GW_MESSAGE_URL_PORT = 80;
    // App_key(for测试 不能用于生产)
    const APP_KEY = "mnpxpsh9ycme";
    // App_secret(for测试 不能用于生产)
    const APP_SECRET = "2cad0d588ad444078a10ecfa87f079a9";
    // WAC_Version
    const X_WAC_VERSION = "4";
    // 当前编码
    const DEFAULT_ENCODING = "UTF-8";
}

?>
