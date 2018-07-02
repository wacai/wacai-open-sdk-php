<?php
namespace wacai\open\lib;
require_once dirname(__DIR__) . "/config/web_config.php";
class DebugUtil{
	public static function print_debug($debug_info){
		if(\wacai\open\config\WebConfig::IS_DEBUG===true){
			print_r($debug_info); 
		}     
    }
}