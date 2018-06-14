<?php
// 测试快贷业务接口 loan.open.precredit.order.create
require_once dirname(dirname(__DIR__)) . '/api/http_client.php';
// 调用的API接口(for Demo测试)
$api_name = "loan.open.precredit.order.create";
// 调用的API版本(for Demo测试)
$api_version = "1.0.0";
// Http Client Api初始化
$client_api = new wacai\open\api\HttpClient($api_name, $api_version);
// 业务参数-json格式(for Demo测试)
$body_data = '{
  "order_info": {
    "order_no": "20180417174917270279",
    "id_card": "412827199006209511",
    "real_name": "张三",
    "phone": "15823839901"
  },
  "ext_info": {
    "xhj_phone_info": {
      "sku_info": {
        "brand": "苹果",
        "model": "iPhone 8 Plus",
        "color": "深空灰",
        "memory_size": "64G",
        "supported_operators": "全网通",
        "degree_new_old": "全新"
      },
      "phone_price": 10000,
      "deposit_amount": 1,
      "insurance_amount": 8888,
      "rent_amount": 1,
      "channel": 1,
      "lease": 12,
      "rent_monthly": 3111,
      "buyout_price": 3,
      "estimate_salvage_value": 6555
    }
  }
}';
// Api调用(true开启debug调试,false=非debug模式)
$client_api->http_post_json($body_data, false, $res);
// 查看调动结果
var_dump($res);
?>