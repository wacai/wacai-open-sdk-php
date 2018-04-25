<?php
class  WebConfig
{
    // API网关地址
    const GW_URL = "http://open.wacaiyun.com/gw/api_entry";
    //"http://guard-boot.loan.k2.test.wacai.info/gw/api_entry";
    // API_网关_Token获取地址
    const GW_TOKEN_URL = "http://open.wacaiyun.com/token/auth";
    //"http://open-token-boot.loan.k2.test.wacai.info/token/auth";
    // 消息网关地址
    const GW_MESSAGE_URL = "open.wacaiyun.com";
    // 消息网关path
    const GW_MESSAGE_URL_PATH = "/ws";
    // "open.wacaiyun.com";
    // baige-bridge-8888.loan.k2.test.wacai.info
    // baige.ngrok.wacaiyun.com
    // 消息网关端口
    const GW_MESSAGE_URL_PORT = 80;//8888;//80;
    // App_key(for测试)
    const APP_KEY = "fsxd8a885fm8";//63ypw88wkpv9
    // App_secret(for测试)
    const APP_SECRET = "0f88a1b5ec034120bb6194119dc16359";
    //"251f1e7412464a10bc2abd939d827637";
    // WAC_Version
    const X_WAC_VERSION = "4";
    // 当前编码
    const DEFAULT_ENCODING = "UTF-8";
}

?>
