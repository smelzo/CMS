<?php
require_once ('smarty/libs/Smarty.class.php');

	
	#resources
	function smarty_resource_internal_source($rsrc_name, &$source, &$smarty) {
		$vv = $smarty->get_template_vars($rsrc_name);
		if(!$vv) {
				$capture = $smarty->_smarty_vars['capture'];
				if($capture && array_key_exists($rsrc_name,$capture)) {
						$vv=$capture[$rsrc_name];
				}
		}
		$source=$vv;
		return true;
	}
	
	function smarty_resource_db_source($rsrc_name, &$source, &$smarty) {
		global $db;
		// intera query path
		//tipo : db:mail_messages|lang:it[en]|code:reguser_mail
		//le pipes sono divisori principali
		$parts = explode('|',$rsrc_name);
		//il primo elemento è il nome tabella
		$tablename = array_shift($parts);
		//gli altri sono i select
		$path=$rsrc_name;
		$sql_principal = array(); //query principale
		$sql_default = array (); //query secondaria
		foreach($parts as $part){
			$elements = explode(':',$part);
			if(!isset($elements[1])) continue; //skip errore nel pair
			$field = $elements[0]; //nome del campo
			$raw_value = $elements[1];
			$alt_match = array();
			if(preg_match('/\[+(.+?)\]+/',$raw_value,$alt_match)) {
				$alt_value = $alt_match[1];
				$value = str_replace($alt_match[0],'',$raw_value);
			}
			else {
				$alt_value = $value = $raw_value;
			}
			$sql_principal[]="$field=". $db->parseValue($value);
			$sql_default[]="$field=". $db->parseValue($alt_value);
		}
		//il campo che contiene il template si chiama sempre 'template'
		$sql_p = $sql_alt = "SELECT template FROM $tablename ";
		if(count($sql_principal)) {
			$sql_p.= "WHERE " . implode(' AND ',$sql_principal);
			$sql_alt.= "WHERE " . implode(' AND ',$sql_default);
		}
			//estrae il risultato 
			$result = $db->querySingle($sql_p);
			if(!$result && ($sql_alt!=$sql_p)) {
				$result = $db->querySingle($sql_alt);
			}
			if($result) {
				$source = $result['template'];				
			}
			else {
				$source = '';
			}		     
		return true;
	}
	
	function smarty_resource_timestamp($rsrc_name, &$timestamp, &$smarty){
		$timestamp= strtotime ("now");
		return true;
	}
	function smarty_resource_secure($rsrc_name, &$smarty) {
		return true;
	}
	
	function smarty_resource_trusted($rsrc_name, &$smarty){
		return true;
	}
	
	   
	class TemplateManager extends Smarty {
		public $write_once_blocks=array();
		//var	$header_contents=array();
		public $header_css=array();
		public $header_js=array();
		public $header_styles=array();
		
		function register_header_content($path,$type,$is_path_relative=true){
				if($is_path_relative) {
						$path = $GLOBALS['locator']->resolve_app_path_uri($path);
				}
				
				switch($type) {
						case "javascript":
						$content ="<script type=\"text/javascript\" src=\"$path\"></script>\r\n";
						if(!in_array($content,$this->header_js)) {
							$this->header_js[]=$content;
						}
						break;
						case "css":
						$content = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$path\"/>\r\n";
						if(!in_array($content,$this->header_css)) {
							$this->header_css[]=$content;
						}
						break;						
				}
		}
		
		function smarty_json($params){
			$value = array_named ($params,array('value','from'),'');
			$varname = array_named ($params,array('varname','var'),'');
			$result = json_encode($value);
			if(!$varname) return $result;
			else $this->assign($varname,$result);
		}
	
		
		function smarty_function_explode($params){
			$value = array_named ($params,'value','');
			$varname = array_named ($params,array('varname','var'),'');
			$separator =array_named ($params,'separator',',');
			$namevalue_sep=array_named ($params,'nvseparator','');
			$result = explode($separator,$value);
			if($namevalue_sep) {
				$assoc = array();
				foreach($result as $item){
					$nv=explode($namevalue_sep,$item,2);
					if(is_array($nv) && count($nv)==2) {
						$assoc[$nv[0]]=$nv[1];
					}
				}
				$result=$assoc;
			}
			if(!$varname) return $result;
			else $this->assign($varname,$result);
		}
		
		function smarty_function_splitparts($params,&$smarty){
			$from =array_named ($params,'from',null); # variabile array
			$parts =array_named ($params,'parts',2);# numero parti
			$pre =array_named ($params,'pre','');# stringa prima ogni serie
			$post =array_named ($params,'post','');# stringa dopo ogni serie
			$min =array_named ($params,'min',2);# numero minimo di items
			$result = array();
			if($from && is_array($from)) {
				$c = count($from);
				$size = ceil($c/$parts);
				$size = max($size,$min);
				$pos=0;
				for($i=0,$l=$c;$i<$l;++$i){
					if(!$pos) $result[]=$pre;
					$pos++;
					$result[]=$from[$i];
					if($pos==$size || $i==($l-1)) {
						$result[]=$post;
						$pos=0;
					}
				}
			}
			return implode("\r\n",$result);
		}		
		function smarty_function_clear_assign($params,&$smarty) {
			$varname =array_named ($params,array('varname','var'),'');
			if($varname) $smarty->clear_assign($varname);
		}
	
		function smarty_function_find_file($params,&$smarty) {
			$varname =array_named ($params,array('varname','var'),'');
			$service =array_named ($params,'service','');
			$file = array_named ($params,'file','');
			
			$locations = array();
			$locations []= Locator::path_combine($smarty->template_dir,$file);
			if($service) {
				$locations [] =	Locator::path_combine($service->phis_basedir,$file);
				$locations [] =	Locator::path_combine($service->phis_services_path,$file);
				$locations [] =	Locator::path_combine($service->phis_services_path,'templates/'.$file);
				$locations [] =	Locator::path_combine($service->phis_commondir,$file);
				$locations [] =	Locator::path_combine($service->phis_commondir,'templates/'.$file);
				
			}
			$found ="";
			foreach($locations as $location){
				if(is_file($location)) {
					$found =$location;
					break;
				}
			}
			if($varname) $smarty->assign($varname,$found);
			else return $found;
		}
		/**
		* {captures name(|capture)='name'}
		* restituisce l'output di un capture dato il suo nome
		*/
		function smarty_function_captures($params,&$smarty) {
			$name =array_named ($params,array('name','capture'),'');
			if($name) {
				$capture = $smarty->_smarty_vars['capture'];
				if($capture && array_key_exists($name,$capture)) {
							return $capture[$name];
					}
			}
		}
		function smarty_function_select_options($params) {
			$options = array_get($params,'options',array());
			$value_field = array_get($params,'value_field','id');
			$display_field = array_get($params,'display_field',$value_field);
			$selected = array_get($params,'selected');
			$opts = array();
			foreach($options as $value){
				$k = array_get ($value,$value_field);
				$v = array_get ($value,$display_field);
				$s = ($k==$selected)?' selected="selected"':'';
				$opts [] = "<option value=\"$k\"$s>$v</option>";
			}
			return implode('',$opts);
		}
		/**
		* @param path
		* @param varname
		* @param merge
		* @param full
	   */
	   function smarty_function_makeRequest($params){
		   $path = array_named ($params,'path',"");
		   $varname = array_named ($params,array('var','varname'),'');
		   $secure = array_named ($params,'secure','');
		   $url_path = array_named ($params,'url_path','');
		   
		   $merge = (isset($params['merge']))?(bool)$params['merge'] :false;
		   $full = (isset($params['full']))?(bool)$params['full'] :false;
		   if($secure) $full=true;
		   $result = makeRequest($path,$merge,$full,$url_path);
		   if($secure) {
			$result = str_ireplace("http://","https://",$result);
		   }
		   if(!$varname) return $result;
		   $this->assign($varname, $result);
	   }
	   function arrayFromString($s,$separator=','){
			return explode($separator,$s);
	   }
	   /**
		* {head_js} or {script}
		* ex. {script src='scripts/Core.js' relative=true}
		* ex. {script src='http://othersite.com/scripts/Core.js' relative=false}
	   */
	   	function smarty_function_register_head_js($params) {
				$path = array_named ($params,array('path','src'),'');
				$is_relative = array_named ($params,array('relative','is_relative'),true);
			
				if(!$path) {
					$this->trigger_error("missing parameter:'path' (or 'src') in function head_js",E_USER_WARNING);
					return "";
				}
				if(!is_array($path))$path=$this->arrayFromString($path);
				foreach($path as $p){
					$this->register_header_content($p,'javascript',$is_relative);
				}
			}
		/**
		 * {head_css} or {css}
		*/
		function smarty_function_register_head_css($params) {
				$path = array_named ($params,array('path','src','href'),'');
				$is_relative = array_named ($params,array('relative','is_relative'),true);
				if(!$path) {
					$this->trigger_error("missing parameter:'path' (or 'src' or 'href') in function head_css",E_USER_WARNING);
					return "";
				}
				if(!is_array($path))$path=$this->arrayFromString($path);
				foreach($path as $p){
					$this->register_header_content($p,'css',$is_relative);
				}
		}
		
		/**
		 * function_call
		 * {function_call funcname="funcname" varname="varname" par1="x" [par2= ...]}
		*/
		function smarty_function_call($params){
			$funcname = array_named ($params,array('funcname','function'),'');
			$varname = array_named ($params,array('varname','var'),'');
			$result ="";
			if(function_exists($funcname)) {
				$args = array_slice(array_values($params),2);
				$result = call_user_func_array($funcname,$args);
			}
			if(!$varname) return $result;
			else $this->assign($varname,$result);
		}
		/**
		 * method_call
		 * {method_call object="object" method="method" varname="varname" par1="x" [par2= ...]}
		*/
		function smarty_method_call($params, &$smarty){
			$object = array_named ($params,array('object'),'');
			$method = array_named ($params,array('method'),'');
			$varname = array_named ($params,array('varname','var'),'');
			$result ="";
			$args = array_slice(array_values($params),3);
			$result = call_user_func_array(array(&$object,$method),$args);
			if(!$varname) return $result;
			else $smarty->assign($varname,$result);
		}
		
		function smarty_concat($params, &$smarty){
			$separator = array_named ($params,array('separator','sep'),'');
			$varname = array_named ($params,array('varname','var'),'');
			$values = array();
			$reserved = array('separator','sep','varname','var');
			foreach($params as $key=>$value){
				if(!in_array($key,$reserved)) $values[]=$value;
			}
			$result =implode($separator,$values);
			if(!$varname) return $result;
			else $smarty->assign($varname,$result);
		}
	# set di estensioni
	
		function smarty_querystring($params) {
			   $varname = array_named ($params,array('varname','var'),'');
			   $name = @$params['name'];
			   $def = (isset($params['def']))?$params['def'] :"";
			   $result = $def;
			   if($name && isset($_GET[$name])) $result = $_GET[$name];
			   if(!$varname) return $result;
			   $this->assign($varname,$result);
		}
		function smarty_range($params) {
			$varname = array_named ($params,array('varname','var'),'range');
			$from = array_named ($params,array('from','start'),1);
			$format = array_named ($params,'format','');
			$to = array_named ($params,array('to','end'),10);
			$result=array();
			if(is_numeric($from) && is_numeric($to)){
				$st= min($from,$to);
				$ed= max($from,$to);
				for($i=$st;$i<=$ed;++$i){
					if($format) $i=sprintf($format,$i);
					$result[]=$i;
				}
			}
			$this->assign($varname,$result);
		}
		function smarty_in_array($params) {
			   $varname = array_named ($params,array('varname','var'),'tmp');
			   $array = (isset($params['array']))?$params['array'] :array();
			   if(!is_array($array)) $array = array();
			   $value = @$params['value'];
			   $result = false;
			   if($value) $result = in_array($value,$array);
			   $this->assign($varname,$result);
		}
		
		/**
		 * {array var='arr' item1='abc' item2='def' itemN=?}
		*/
		function smarty_make_array($params) {
			$varname = array_named ($params,array('varname','var'),'');
			$result=array();
			foreach($params as $key=>$value){
				if($key=='varname'||$key=='var') continue;
				$result[]=$value;
			}
			if(!$varname) return $result;
		    else $this->assign($varname,$result);
		}
		
		function smarty_print_r($params) {
			$var = array_named ($params,array('var'),@$params[0]);
			return print_r($var,true);
		}
		function smarty_global($params){
		   $varname = array_named ($params,array('varname','var'),'');
		   $name = @$params['name'];
		   $def = (isset($params['def']))?$params['def'] :"";
		   $result = (isset($GLOBALS[$name]))?$GLOBALS[$name] :$def;
		   if(!$varname) return $result;
		   else $this->assign($varname,$result);
		}
		function smarty_count($params){
		   $varname = array_named ($params,array('varname','var'),'');
		   $array = array_named ($params,array('array'),'');
		   $result=0;
		   if(is_array($array)) $result = count($array);
		   if(!$varname) return $result;
		   else $this->assign($varname,$result);
		}
		
		#importa un file
		function smarty_function_import($params) {
			$varname = array_named ($params,array('varname','var'),'');
			$path = array_named ($params,array('path','file'),'');
			$resolve_path= array_named ($params,array('resolvepath'),true);
			if(!dirname($path) || $resolve_path){
				$path=Locator::path_combine($this->template_dir,$path);
			}
			$result= (is_file($path))?file_get_contents($path):"$path not found";
			if($varname) $this->assign($varname,$result);
			else return $result;
		}
		
		#output filters
		#output filter per il tag HEAD
		function smarty_head_output_filter($output){
			if($this->header_js || $this->header_css || $this->header_styles) {
				$insert = implode("\n",$this->header_js) . "\n". implode("\n",$this->header_css);
				if($this->header_styles) {
					$style = "<style type=\"text/css\">\n". implode("\n",$this->header_styles)."</style>";
					$insert.="\n$style\n";
				}
				$markupSymbol='<!--[smarty_head_output]-->';
				if (strpos($output,$markupSymbol)!==false) {
					return str_replace($markupSymbol,$insert,$output);
				}
			}
			else {
				return $output; //nessun contenuto
			}
			$strdelim = '</head>';
			$pos = strpos($output,$strdelim);
			if($pos) {
				$outpart = substr($output,0,$pos);
				$scpos = strpos($outpart,"<script");
				if($scpos) {
					$pos = $scpos;
					$strdelim="<script";
				}
			}
			//$insert = implode("",$this->header_contents);
			if($pos!==false) {
					$firstpart = substr($output,0,$pos);
					//$secondpart = substr($output,$pos + strlen($strdelim) + 1);
					$secondpart = substr($output,$pos);
					return "$firstpart\r\n$insert\r\n$secondpart";
			}
			else {
					return $pos;
			}
		}
		
/*modifiers*/
	/**
	 * modifica l'url scritto senza http:// che inizia per www.
	*/
	function smarty_modifier_fix_www($url){
		$result = $url;
		if($result){
			$result= preg_replace('/^w{3}\\.{1}/im', "http://www.", $url);
		}
		return $result;
	}
	/**
	 * trova il target corretto, se l'indirizzo è esterno http(s)
	 * indirizza su _blank
	*/
	function smarty_modifier_targetize($url){
		$url = smarty_modifier_fix_www($url);
		if(preg_match('/^http{1}s*:{1}\\/{2}/im', $url)) return '_blank';
		return '_self';
	}
	function smarty_modifier_nativelang($code){
		$fx = 'iso_native';
		if($code && is_callable($fx)){
			return $fx($code);
		}
		return $code;
	}
	function smarty_modifier_trim($s){
		if($s) return trim($s);
		return "";
	}
	function smarty_modifier_enlang($code){
		$fx = 'iso_en';
		if($code && is_callable($fx)){
			return $fx($code);
		}
		return $code;
	}
	function smarty_modifier_fullurl($s,$prefix="",$suffix="") {
		$s = $prefix.$s.$suffix;
		$locator=$GLOBALS['locator'];
		return Locator::path_combine($locator->app_base_url,$s);
	}
	function smarty_modifier_number_format($s,$decimals=2 ,$dec_point=",", $thousands_sep="."){
		if(is_numeric($s)) {
			$s = number_format($s,$decimals ,$dec_point, $thousands_sep);
		}
		return $s;
	}
	function smarty_modifier_printf($s){
		$args=func_get_args();
		return call_user_func_array('sprintf',$args);
	}
	function smarty_modifier_json($s){
		return json_encode($s);
	}
/*blocks*/
	#capture array block
	#appende un valore a una variabile
	function smarty_block_append($params, $content, &$smarty, &$repeat){
		$var = array_named($params,'var','capturearray');
		$this->append($var,$content);
	}
	function smarty_block_style($params, $content, &$smarty, &$repeat){
		$this->header_styles[]=$content;
	}
	/**
	* {enclose before='text|var' after='text|var' [var='varname']}
	* content
	* {/enclose}
	* racchiude il testo tra due stringhe
	*/
	function smarty_block_enclose($params, $content, &$smarty, &$repeat){
		$before = array_named($params,'before','');
		$after = array_named($params,'after','');
		$varname = array_named ($params,array('varname','var'),'');
		$separator =array_named ($params,'separator',',');
		$result = $before . $content . $after;
		if(!$varname) return $result;
		else $this->assign($varname,$result);
	}
	
	
	/**
	* {write_once blockid='name'}
	* content
	* {/write_once}
	* inserisce il testo solo una volta
	*/
	function smarty_block_write_once($params, $content, &$smarty, &$repeat){
		if (isset($content)){
		$blockid= array_named($params,'blockid','smarty_block_write_once_id');
		if(in_array($blockid, $this->write_once_blocks)) {
				return '';
		}
		else {
				$this->write_once_blocks[]=$blockid;
				
		}
		return $content;
		}
	}
	
	function set_from_global($property){
		if(func_num_args()>1) $property = func_get_args();
		if(is_array($property)) {
			foreach($property as $p){
				$this->set_from_global($p);
			}
		}
		else {
			$v = getGlobal($property,'');
			if($v) $this->$property = $v;
		}
	}
		function __construct(){
			parent::__construct();
			
			$this->register_function('querystring',array(&$this,'smarty_querystring'));
			$this->register_function('in_array',array(&$this,'smarty_in_array'));
			$this->register_function('array',array(&$this,'smarty_make_array'));
			
			$this->register_function('global',array(&$this,'smarty_global'));
			$this->register_function('function_call',array(&$this,'smarty_function_call'));
			$this->register_function('method_call',array(&$this,'smarty_method_call'));
			$this->register_function('select_options',array(&$this,'smarty_function_select_options'));
			
			$this->register_function('concat',array(&$this,'smarty_concat'));
			$this->register_function('makeRequest', array(&$this,'smarty_function_makeRequest'));
			$this->register_function('json',array(&$this,'smarty_json'));
			$this->register_function('explode',array(&$this,'smarty_function_explode'));
			$this->register_function('splitparts',array(&$this,'smarty_function_splitparts'));
			$this->register_function('clear_assign',array(&$this,'smarty_function_clear_assign'));
			$this->register_function('find_file',array(&$this,'smarty_function_find_file'));
			$this->register_function('captures',array(&$this,'smarty_function_captures'));
			
			$this->register_function('head_js',array(&$this,'smarty_function_register_head_js'));
			$this->register_function('script',array(&$this,'smarty_function_register_head_js'));
			
			$this->register_function('head_css',array(&$this,'smarty_function_register_head_css'));
			$this->register_function('css',array(&$this,'smarty_function_register_head_css'));
			
			$this->register_function('count',array(&$this,'smarty_count'));
			$this->register_function('range',array(&$this,'smarty_range'));
			
			$this->register_function('print_r',array(&$this,'smarty_print_r'));
			$this->register_function('import',array(&$this,'smarty_function_import'));
			
			$this->register_modifier('fix_www',array(&$this,'smarty_modifier_fix_www'));
			$this->register_modifier('targetize',array(&$this,'smarty_modifier_targetize'));
			$this->register_modifier('nativelang',array(&$this,'smarty_modifier_nativelang'));
			$this->register_modifier('enlang',array(&$this,'smarty_modifier_enlang'));
			$this->register_modifier('trim',array(&$this,'smarty_modifier_trim'));
			
			$this->register_modifier('fullurl',array(&$this,'smarty_modifier_fullurl'));
			$this->register_modifier('number_format',array(&$this,'smarty_modifier_number_format'));
			$this->register_modifier('printf',array(&$this,'smarty_modifier_printf'));
			$this->register_modifier('json',array(&$this,'smarty_modifier_json'));
			$this->assign_by_ref('globals',$GLOBALS);
			
			$this->register_resource("db", array("smarty_resource_db_source","smarty_resource_timestamp","smarty_resource_secure","smarty_resource_trusted"));
			
			$this->register_resource("internal", array("smarty_resource_internal_source","smarty_resource_timestamp","smarty_resource_secure","smarty_resource_trusted"));
			
			$this->register_block('style',array(&$this,'smarty_block_style'));
			$this->register_block('append',array(&$this,'smarty_block_append'));
			$this->register_block('enclose',array(&$this,'smarty_block_enclose'));
			$this->register_block('write_once',array(&$this,'smarty_block_write_once'));
			$this->register_outputfilter(array(&$this,'smarty_head_output_filter'));
			$this->register_globals('smarty_functions','register_function');
			$this->register_globals('smarty_blocks','register_block');
			$this->register_globals('smarty_modifiers','register_modifier');
		}
		private function register_globals($global_name,$register_method='register_function'){
			if(isset($GLOBALS[$global_name]) && is_array($GLOBALS[$global_name])){
				foreach($GLOBALS[$global_name] as $key=>$value){
					$this->$register_method($key,$value);
				}
			}
		}
}


?>