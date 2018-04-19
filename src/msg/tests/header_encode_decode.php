<?php
require_once "../entities/header.php";
require_once "../encoders/header_encoder_decoder.php";
require_once dirname(dirname(__DIR__)) . "/libs/utils.php";

$header = new MessageHeader();
$header->flag = 1;
$header->code = 0;
$header->resp_code = 0;
$header->opaque = Util::rand(100000, 999999);
$header->topic = "wacai.openplatform.ocean.guard.cache";
$header->remark = "remark";
$ext_fields = [];
$ext_fields["k1"] = "v1";
$ext_fields["k2"] = "v2";
$ext_fields["k3"] = "v3";
$header->ext_fields = $ext_fields;
//var_dump($header);
echo ">>>>>>start pack\r\n";
$bin_header = HeaderEncoder::encode($header);
var_dump($bin_header);
echo ">>>>>>end pack\r\n";
echo ">>>>>>start unpack\r\n";
$header = HeaderEncoder::decode($bin_header);
var_dump($header);
echo ">>>>>>end unpack\r\n";
?>