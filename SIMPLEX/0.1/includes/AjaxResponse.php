<?php
if(!defined('AJAX_ACTION')) define('AJAX_ACTION','action');

class AjaxResponse extends ResponseManager{
	
	public static $listen_action = AJAX_ACTION;
	
	function __construct($function_name,$class_name="") {
		parent::__construct($function_name,$class_name);
	}
	
	static function response(){
		$method = ""; $data_array=array();
		$format = getRequest('format','json');
		$invoke_token = getPost(self::$listen_action,null);
		
		if(!$invoke_token){
			$invoke_token = querystring(self::$listen_action,null);
			if($invoke_token) $method = 'GET';
		}
		else {
			$method = 'POST';
		}
		if(!$invoke_token) {
			return ; //no action found
		}
		else {
			$data_array=$method=='POST'?$_POST:$_GET;
			$cls="";$fx="";
			self::parse_invoke_token($invoke_token,$fx,$cls);
			$ar = new AjaxResponse($fx,$cls);
			$result = $ar->invoke($data_array);
			switch($format){
				case "json":
					echo json_encode($result);
					break;
				default :
					echo $result;
			}
			exit;
		}
	}
}
// standard ajax response
class ServerResponse {
	public $error=false;
	public $errorId = 0;
	public $errorText = "";
	public $data=null;
	static function getError($errorId,$errorText){
		$err = new ServerResponse;
		$err->error=true;
		$err->errorId=$errorId;
		$err->errorText=$errorText;
		return $err;
	}
	static function getOk($data){
		$rr = new ServerResponse;
		$rr->data = $data;
		return $rr;
	}
}
?>