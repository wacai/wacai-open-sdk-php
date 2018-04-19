<?php
define("WEBPATH", str_replace("\\", "/", __DIR__));
require_once dirname(__DIR__) . "/libs/lib_config.php";
require_once dirname(__DIR__) . "/libs/utils.php";
require_once dirname(__DIR__) . "/libs/base64.php";
require_once dirname(__DIR__) . "/config/web_config.php";
require_once "entities/ack_result.php";
require_once "encoders/body_encoder_decoder.php";
require_once "encoders/frame_encoder_decoder.php";

/**
 * Class HttpClientMessage
 */
class HttpClientMessage
{
    // web socket client
    private $client;

    public function __construct()
    {
        // Initialization
        $this->init();
    }

    /**
     * Pull message(仅支持pull一条)
     * @param $topic
     * @return Message(详细属性见Message)
     */
    public function pull($topic)
    {
        if (empty($topic)) {
            die("topic is nul(pull)");
        }

        // Request构建
        $header = new MessageHeader();
        $header->flag = 0;
        $header->code = 1;
        // 异步处理时需要替换
        $header->opaque = Util::rand(100000, 999999);
        $header->topic = $topic;

        $message = null;
        $frame = $this->sync($header, null, "Pull");
        if (!empty($frame)) {
            $message = $frame->message_list[0];
        }

        return $message;
    }

    /**
     * 发送确认(reply ack)
     * @param $topic
     * @param $offset
     * @return Frame|null
     */
    public function ack($topic, $offset)
    {
        if (empty($topic)) {
            die("topic is nul(ack)");
        }
        if (empty($offset) || !is_numeric($offset)) {
            die("offset is invalid(ack)");
        }

        // 构建请求header
        $header = new MessageHeader();
        $header->flag = 0;
        $header->code = 5;
        // 异步处理时需要替换
        $header->opaque = Util::rand(100000, 999999);
        $header->topic = $topic;
        $ext_fields = [];
        $ext_fields["offset"] = $offset;
        $header->ext_fields = $ext_fields;

        $ack_result = new AckResult();
        $frame = $this->sync($header, null, "Ack");
        if (!empty($frame)) {
            $resp_header = $frame->header;
            $ext_fields = $resp_header->ext_fields;
            if (!empty($ext_fields) && count($ext_fields) > 0) {
                $ack_result->is_ok = $ext_fields["ackSuccess"];
                if (!$ack_result->is_ok) {
                    $ack_result->error_message = $ext_fields["ackFailReason"];
                }
            }
        }

        return $ack_result;
    }

    /**
     * 推送消息(push message)
     * @param $topic
     * @param array $messages
     * @return Frame|null
     */
    public function push($topic, $messages = [])
    {
        if (empty($topic)) {
            die("topic is nul(push)");
        }
        if (empty($messages) || count($messages) == 0) {
            die("message is nul(push)");
        }

        $header = new MessageHeader();
        $header->flag = 0;
        $header->code = 7;
        // 异步处理时需要替换
        $header->opaque = Util::rand(100000, 999999);
        $header->topic = $topic;

        $body = new Body();
        $body->message_count = count($messages);
        $body->arr_message = $messages;

        $frame = null;
        // request
        $bin_request = FrameEncoder::encode($header, $body);
        while (true) {
            // 请求(二进制通信)
            $this->client->send($bin_request, "bin");

            // 响应
            $bin_response = $this->client->recv();
            if ($bin_response === false) {
                die('response is null(push)');
                break;
            }

            // 解析响应
            $frame = FrameEncoder::decode($bin_response);
            // 判定退出
            if ($frame != null && $frame->header_length > 0) {
                break;
            }
        }
        return $frame;
    }

    public function consume($topic,$function){
         if (empty($topic)) {
            die("Topic is nul(Consume)");
        }
        
        $ack_result = null;
        $message = $this->pull($topic);
        if(!empty($message)){
            $payload = $message->payload;
            $offset = $message->msg_offset;
            // invoke user function
            if(!empty($payload)){
                $params = array($payload);//传给参数的值
                $callback_result = call_user_func_array($function,$params); 
            }
            if($callback_result===true){
                $ack_result = $this->ack($topic,$offset);
            }else{
                $ack_result = new AckResult();
                $ack_result->error_message = "Invoke call_user_func error";
            }
        }

        return $ack_result;
    }

    private function sync($header, $body, $flag = null)
    {
        $frame = null;
        // request
        $bin_request = FrameEncoder::encode($header, $body);

        while (true) {
            // 请求(二进制通信)
            $this->client->send($bin_request, "bin");

            // 响应
            $bin_response = $this->client->recv();
            if ($bin_response === false) {
                die("Response is null," . $flag);
                break;
            }

            // 解析响应
            $frame = FrameEncoder::decode($bin_response);
            // 获取到服务端响应,解码退出
            if ($frame != null && $frame->header_length > 0) {
                break;
            }
        }

        if (!empty($frame) && $frame->message_list > 0) {
            $res_header = $frame->header;
            // 同步时，比较resp_code==header->code是否相同来区分操作(pull/ack/push)
            // 如果相同，则继续处理 否则就服务端处理错误
            if ($res_header->resp_code != $header->code) {
                echo "Resp_code<>request-code,MQ Server 处理错误";
                $frame = null;
            }
        }

        return $frame;
    }

    /**
     * 初始化函数
     */
    private function init()
    {
        $auth_header = $this->get_auth_header();
        $this->client = new Swoole\Client\WebSocket(WebConfig::GW_MESSAGE_URL
            , WebConfig::GW_MESSAGE_URL_PORT
            , '/ws'
            , $auth_header);

        if (!$this->client->connect()) {
            echo "Connect to MQ server failed.\n";
            exit;
        }
    }

    /**
     * 获取Authen header
     * @return string
     */
    private function get_auth_header()
    {
        // Timestamp
        $time_stamp = Util::getMillisecond();
        // 后面替换为服务端ip地址
        $server_ip = Util::get_server_ip();
        if (empty($server_ip)) {
            $server_ip = "127.0.0.1";
        }
        // process_id
        $process_id = getmypid();

        // text_plain(for signature)
        $text_plain = WebConfig::APP_KEY . $server_ip . $process_id . $time_stamp;
        // sign
        $signature = Base64::base64url_encode(hash_hmac('sha256', $text_plain, WebConfig::APP_SECRET, true));
        $arr_x_wac = array('appkey' => WebConfig::APP_KEY, 'text' => $text_plain, 'sign' => $signature);
        $x_wac_json = json_encode($arr_x_wac);

        // Auth headers
        $headers = '';
        $x_wac_mq_auth = "x-wac-mq-auth-info: " . $x_wac_json . "\r\n";
        $server_id = "server-id: " . $server_ip . "\r\n";
        $headers .= $x_wac_mq_auth;
        $headers .= $server_id;

        return $headers;
    }
}

?>
