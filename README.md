# wacai-open-sdk-php
The client sdk of php for wacai open platform

## 使用说明
### 接口协议
- 使用HTTPS协议作为目前的交互协议
- 客户端统一使用POST方式向网关入口提交数据
- 请求报文、响应报文格式都是JSON，content_type为application/json
- 交互的编码格式统一为UTF-8
- HTTP正常响应的http code都是200，非正常返回400

### 使用配置
- 申请app_key/app_secret
- app_key/app_secret替换,替换为步骤1申请的(在web_config.php中修改)
- 修改地址(生产环境),系统上线时，需要修改网关地址和token获取地址(在web_config.php中修改)

### 使用实例
```php
<?php
require_once './http_client.php';
// 调用的API接口(for Demo测试)
$api_name = "api.test.post.fixed";
// 调用的API版本(for Demo测试)
$api_version = "1.0";
// Http Client Api初始化
$client_api = new HttpClient($api_name, $api_version);
// 业务参数-json格式(for Demo测试)
$body_data = '{"uid":123,"name":"zy"}';
// Api调用(true开启debug调试,false=非debug模式)
$client_api->http_post_json($body_data, false, $res);
// 查看调动结果
var_dump($res);
?>
```
