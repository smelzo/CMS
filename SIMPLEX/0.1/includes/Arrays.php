<?php
class Arrays {
	static function keys_exists($a,$keys,$check_values = true){
		foreach($keys as $key){
			if(!isset($a[$key])) return false;
			if($check_values && self::array_named($a,$key,null)===null){
				return false;
			}
		}
		return  true;
	}
	static function implode_assoc($glue,$a,$concatOp='=',$prefix='"',$suffix='"'){
		$out = array();
		foreach($a as $key=>$value){
			$out[]="$key$concatOp$prefix$value$suffix";
		}
		return implode($glue,$out);
	}
	static function array_add(&$a,$add){
		if(is_string($add)) {
			$add=self::string_to_array($add);
		}
		if(!is_array($a)) $a=array();
		if(!is_array($add)) return $a;
		$a = array_merge($a,$add);
		return $a;
		
	}
	static function is_array_assoc($var){
		 return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
	}
	static function implode_ex($separator,$array,$prefix='',$suffix=''){
		$narray=array();
		if(!$suffix) $suffix=$prefix;
		foreach($array as $value) $narray[]="$prefix$value$suffix";
		return implode($separator,$narray);
	}

	static private $true_vals_array=array('yes','ok','true','t','y');
	static private function bool_value($v=null){
		if($v===null) return false;
		if(is_string($v) && !is_numeric($v) ){
			return in_array( strtolower($v),self::$true_vals_array);
		}
		elseif(is_numeric($v)){
			return $v;
		}
		return false;
	}
	static function array_set(&$a,$name,$value,$overwrite=false){
		if(!self::array_named($a,$name,null)||$overwrite){
			$a[$name]=$value;
		}
		else $value = self::array_named($a,$name,null);
		return $value;
	}
	static function array_named(&$a,$name,$def="",$type="",$value_range=null,$unset=false){
		if(!is_array($a)) {
			if(is_object($a)){
				$ao = get_object_vars($a);
				return self::array_named($ao,$name,$def,$type,$value_range,$unset);
			}
			return $def;
		}
		if(is_array($name)) {
			foreach($name as $nm){
				if(isset($a[$nm])) {
					return array_named($a,$nm,$def,$type,$value_range,$unset);
				}
			}
		}
		elseif(isset($a[$name])) {
			$v = $a[$name];
			if($value_range && is_array($value_range)){
				if(!in_array($v,$value_range)){
					$v=$def;
				}
			}
			if($type) {
				switch($type) {
					case 'string':
						if(!is_string($v)) return $def;
						break;
					case 'json':
						if(!$v)$v=$def;
						$v = json_decode($v,true);
						break;
					case 'number':
						if(!is_numeric($v)) return $def;
						break;
					case 'boolean':
					case 'bool':
						$vl = self::bool_value($v);
						if(!$vl && $def===true) $vl=$def;
						return (bool)$vl;
						break;
					case 'array':
						if(!is_array($v)) return $def;
						break;
					case 'object':
						if(!is_object($v)) return $def;
						break;
				}
			}
			if($unset) {				
				unset($a[$name]);
			}
			return $v;
		}
		return $def;
	}
	
	static function array_search_child($a,$key,$value){
		foreach($a as $child){
			if(is_object($child)){
				if($child->$key==$value) return $child;
			}
			elseif(is_array($child)){
				if($child[$key]==$value) return $child;
			}
			else continue;
		}
		return null;		
	}
	static function array_to_assoc(&$a,$key){
		$na = array();
		foreach($a as $child){
			if(is_object($child)){
				$k = $child->$key;
			}
			elseif(is_array($child)){
				$k=$child[$key];
			}
			else continue;
			$na[$k]=$child;
		}
		$a=$na;
		return $a;	
	}
	static function array_group($array,$keygroup){
		if(!is_array($array)) return $array;
		$grouped = array();
		foreach($array as $item){
			$groupname = (isset($item[$keygroup]) &&
						  $item[$keygroup] &&
						  is_string($item[$keygroup]))?$item[$keygroup]:'ungrouped';
			if(!isset($grouped[$groupname])){
				$grouped[$groupname]=array();
			}
			$grouped[$groupname][]=$item;
		}
		return $grouped;
	}
	static function string_to_array($s,$itemSeparator=";",$assignOperator=":"){
		$result=array();
		if($s){
			$pp=explode($itemSeparator,$s);
			foreach($pp as $p){
				$kv=explode($assignOperator,$p,2);
				if(count($kv)==1){
					$result[]= trim($kv[0]);
				}
				else {
					$result[trim($kv[0])]=trim($kv[1]);
				}
			}
		}
		return $result;
	}
	//alias
	static function strtoarray($s,$itemSeparator=";",$assignOperator=":"){
		return self::string_to_array($s,$itemSeparator,$assignOperator);
	}
	
}
?>