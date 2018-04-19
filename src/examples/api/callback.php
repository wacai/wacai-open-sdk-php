<?php
function fnCallBack($msg1,$msg2)
{
    echo 'msg1:'.$msg1;
    echo "<br />\n";
    echo 'msg2:'.$msg2;
    return true;
}

$fnName = "fnCallBack"; //方法名
$params = array( 'hello' , 'world' );//传给参数的值
$result = call_user_func_array( $fnName , $params );
var_dump($result);
?>