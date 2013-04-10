<?php
abstract class ResponseManager {
	protected $fx,$cls;
	
	function __construct($function_name,$class_name=""){
		$this->fx=$function_name;
		$this->cls = $class_name;
	}
	
	/**
	* invoke_method
	*/
	protected function invoke_method($rf,$data_array,$obj=null){
		$params = $rf->getParameters();
		if(!is_array($data_array)) $data_array=array();
		$args=array();
		foreach($params as $param){
			$key=$param->getName();
			$def=null;
			if($param->isDefaultValueAvailable()) {
				$def = $param->getDefaultValue();				
			}
			$value=array_named($data_array,$key,null);
			$args[]=$value;
		}
		if(!$obj) return $rf->invokeArgs($args);
		else return $rf->invokeArgs($obj,$args);
	}
	/**
	* invoke_function
	*/
	protected function invoke_function($data_array){
		$rf = new ReflectionFunction($this->fx);
		return $this->invoke_method($rf,$data_array);
	}
	/**
	* invoke_class_method
	*/
	protected function invoke_class_method($data_array){
		
		if( isset($GLOBALS[$this->cls]) && is_object($GLOBALS[$this->cls])) {
			//ReflectionObject
			$obj = $GLOBALS[$this->cls];
			$rc = new ReflectionObject($obj);
		}
		else $obj = $rc = new ReflectionClass($this->cls);
		$rf = $rc->getMethod($this->fx);
		return $this->invoke_method($rf,$data_array,$obj);
	}
	/**
	* invoke
	*/
	function invoke($data_array){
		if($this->cls){
			return $this->invoke_class_method($data_array);
		}
		else {
			return $this->invoke_function($data_array);
		}
	}
	/**
	* parse_invoke_token
	*/
	protected static function parse_invoke_token($invoke_token,&$fx,&$cls){
			$parts = explode(".",$invoke_token);			
			if(count($parts)>1){
				$cls=$parts[0];//nome della classe o nome oggetto
				$fx =$parts[1];//nome metodo
			}
			else {
				$fx =$parts[0]; //nome funzione
			}
	}
}
?>