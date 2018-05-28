<?php
namespace wacai\open\config;
class  WebConfig
{
    // API网关地址
    const GW_URL = "http://open.wacai.com/gw/api_entry";
    // API_网关_Token获取地址
    const GW_TOKEN_URL = "http://open.wacai.com/token/auth";
    // 消息网关地址
    const GW_MESSAGE_URL = "open.wacai.com";
    // 消息网关path
    const GW_MESSAGE_URL_PATH = "/mq";
    // 消息网关端口
    const GW_MESSAGE_URL_PORT = 80;
    // App_key(待分配)
    const APP_KEY = "";
    // App_secret(待分配)
    const APP_SECRET = "";
    // WAC_Version
    const X_WAC_VERSION = "4";
    // 当前编码
    const DEFAULT_ENCODING = "UTF-8";
}
?>