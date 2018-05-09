# wacai-open-sdk-php
The client sdk of php for wacai open platform

## API网关
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

## 消息网关
### 接口协议
- 使用HTTP/Web socket协议作为目前的交互协议
- 异步交互使用Swoole开源通信框架
- 交互的编码格式统一为UTF-8

### 使用配置
- 申请app_key/app_secret(和API网关一致)
- app_key/app_secret替换,替换为步骤1申请的(在web_config.php中修改)
- 修改地址(生产环境),系统上线时，需要修改消息网关地址和端口(在web_config.php中修改)

### 使用实例
#### 同步调用一(拉取消息和消息确认分开2个接口)
详细见下面的代码：
```php
<?php
require_once dirname(dirname(__DIR__)) . "/msg/entities/header.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/message.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/body.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/frame.php";
require_once dirname(dirname(__DIR__)) . "/msg/message_http_client.php";
// for demo
$topic = "middleware.guard.cache";
$messageClient = new HttpClientMessage();
print_r(">>>Start pull message\r\n");
// 目前，仅支持每次pull一条
$message = $messageClient->pull($topic);
var_dump($message);
print_r(">>>End pull message\r\n");

$offset = $message->msg_offset;
print_r(">>>Start ack\r\n");
$resp_header = $messageClient->ack($topic, $offset);
print_r("Ack result:");
var_dump($resp_header);
print_r(">>>End ack\r\n");
?>
```
消息的拉取和确认-调用结果(for-demo)
```json
>>>Start pull
object(Message)#16 (5) {
  ["msg_key_length"]=>
  int(5)
  ["msg_key"]=>
  string(5) "dummy"
  ["msg_offset"]=>
  int(9030)
  ["payload_length"]=>
  int(119)
  ["payload"]=>
  string(119) "{"category":"apiInfo","eventType":"U","properties":{"apiName":"wacai.withhold.bind.card.confirm","apiVersion":"1.1.1"}}"
}
>>>End pull
>>>Start ack
Ack result:object(AckResult)#15 (2) {
  ["is_ok"]=>
  string(4) "true"
  ["error_message"]=>
  string(0) ""
}
>>>End ack
```


#### 同步调用二(消费消息,支持回调函数(返回true/false))
回调函数由消费方提供，回调函数须返回true/false(true:消费成功,false:消费失败)，详细见下面的代码：
```php
<?php
require_once dirname(dirname(__DIR__)) . "/msg/entities/header.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/message.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/body.php";
require_once dirname(dirname(__DIR__)) . "/msg/entities/frame.php";
require_once dirname(dirname(__DIR__)) . "/msg/message_http_client.php";

//(for internal testing)
$topic = "middleware.guard.cache";
$messageClient = new HttpClientMessage();
print_r(">>>Start consume\r\n");
$result = $messageClient->consume($topic,"consume_message");
var_dump($result);
print_r(">>>End consume\r\n");

/**
* 业务处理函数,
* Message处理成功返回true(此时消息ack成功),否则返回false
*/
function consume_message($message_content){
	var_dump($message_content);
	// 业务逻辑处理...
	return true;
}
?>
```
消息消费-调用结果图例(for-demo)
```json
>>>Start consume
object(AckResult)#15 (2) {
  ["is_ok"]=>
  string(4) "true"
  ["error_message"]=>
  string(0) ""
}
>>>End consume
```

### 注意事项
调用API接口需要鉴权，这个过程中会使用到Token, 根据app_key/app_secret获取访问access_token, 
接下来访问时，会自动带上access_token, 如果access_token 过期或失效，会再次请求进行token获取置换，在此过程中，需要注意两点：
- 为了提高性能，获取到token尽量放在分布式缓存(memcache/redis)中，考虑到各个接入方技术栈不同，难以强制统一，本demo中access_token放在服务器内存中；
- 如果access_token 过期或失效，SDK会自动进行token获取置换，但不会自动发起请求重试操作，请求重试，由调用方发起；