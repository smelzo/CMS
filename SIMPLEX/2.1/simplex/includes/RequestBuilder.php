<?php
class RequestBuilder {
	public $params;
	public $sep="&";//separator of query
	public $fullurl=true;//output a full url
	public $merge=false; //merge existing values with news
	public $globals_keys=array();//array of var names to report in every request
	public $script_name = '';//relative path for script, default current url
	function __construct($params){
		$this->parse_parameters($params);
		$this->params=$params;
		$this->script_name=$_SERVER['SCRIPT_NAME'];

		//GLOBAL_PARAMETERS can be setted in config
		if(isset($GLOBALS['GLOBAL_PARAMETERS']) &&
		   is_array($GLOBALS['GLOBAL_PARAMETERS']) &&
		   $GLOBALS['GLOBAL_PARAMETERS']){
			$this->globals_keys=$GLOBALS['GLOBAL_PARAMETERS'];
		}
	}
	private function parse_parameters(&$params){
		if(is_array($params)) return ;
		if(is_string($params)){
			$params=Arrays::strtoarray($params,'|','=');
		}
		else {
			$params=array();//fail parsing
		}
	}

	
	function build(){
		$a=$this->params;
		if($this->merge) {
			$a = array_merge($_GET,$a);
		}
		$gc = $this->globals_keys;
		if($gc){
			foreach($gc as $name){
				if(isset($GLOBALS[$name]) &&
				   $GLOBALS[$name] &&
				   !isset($a[$name]))
				$a[$name]= $GLOBALS[$name];
			}
		}
		$parts = array();
		foreach($a as $key=>$value){
			if($value=='') continue;
			$value=urlencode($value);
			$parts[]="$key=$value";
		}
		$uri = $this->script_name;
		
		if($this->fullurl) {
			global $locator;
			$uri=$locator->uri(basename($this->script_name));
		}
		if($parts) {
			$uri.="?".implode($this->sep,$parts);
		}
		
		return $uri;
	}
}
?>