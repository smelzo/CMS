<?php
function _lib_internal_autoload($class_name){
	//names to search
	$test_paths=array(
			"$class_name.php"
	);
	//directory dove cercare
	$base_dir = dirname(__FILE__);
	foreach($test_paths as $file){
		$file = "$base_dir/$file";
		if(is_file($file))  {
			require_once($file)	;
			return ;
		}
	}
	//die("class $class_name not found");
	return false;
}
spl_autoload_register('_lib_internal_autoload');

if(!isset($DEBUG)||$DEBUG)FB::enable(); //enable fb if global $DEBUG non exists or is TRUE
if (get_magic_quotes_gpc()) Misc::fix_magic_quotes();

function implode_ex($separator,$array,$prefix='',$suffix=''){
	return Arrays::implode_ex($separator,$array,$prefix,$suffix);
}

function isHttps(){
	return Misc::isHttps();
}

function server_url(){  
    return Misc::server_url();
}
function current_pagename(){  
    return Misc::current_pagename();
}
function is_true_or_not_set($var){
	return (!isset($GLOBALS['var']) || $GLOBALS['var']);
}
function is_array_assoc($var){
   return Arrays::is_array_assoc($var);
}
function array_add(&$a,$add){
	return Arrays::array_add($a,$add);
}
/**
 * estrae un valore da un array
 * $a=array,
 * $name=key (array per tentativi multipli)
 * $def=default
 * $type = 'string','array','number','boolean/bool','object'
*/
function array_named(&$a,$name,$def="",$type="",$value_range=null,$unset=false){
	return Arrays::array_named($a,$name,$def,$type,$value_range,$unset);
}
function array_named_unset(&$a,$name,$def="",$type="",$value_range=null){
	return  array_named($a,$name,$def,$type,$value_range,true);	
}
function array_set(&$a,$name,$value,$overwrite=false){
	return Arrays::array_set($a,$name,$value,$overwrite);
}
//alias for array_named
function array_get(&$a,$name,$def="",$type="",$value_range=null,$unset=false){
	return array_named($a,$name,$def,$type,$value_range,$unset);
}
//alias for array_named_unset
function array_get_unset(&$a,$name,$def="",$type="",$value_range=null){
	return  array_named_unset($a,$name,$def,$type,$value_range,true);	
}
/**
 * search in array of array or in array of object
 * return child array or child object
 * @param array $a main array
 * @param string $key name of key or property in child
 * @param mixed $value value to search
 * @return mixed child array or object
*/
function array_search_child ($a,$key,$value){
	return Arrays::array_search_child($a,$key,$value);
}
/**
 * transform a simple array in assoc array using
 * a key of child object or array
 * @param array $a Array
 * @param string $key Key
 * @return array
*/
function array_to_assoc(&$a,$key){
	return Arrays::array_to_assoc($a,$key);
}

function array_to_object($array = array(),$convert_inner_arrays=false) {
    if (!empty($array)) {
        $data = false;

        foreach ($array as $akey => $aval) {
			if($convert_inner_arrays && is_array($aval) && !array_key_exists(0,$aval)) {
				$data -> {$akey} = array_to_object($aval);
			}
            else $data -> {$akey} = $aval;
        }

        return $data;
    }

    return false;
}
/**
 * Groups an array of objects by child key
*/
function array_group($array,$keygroup){
	return Arrays::array_group($array,$keygroup);
}

function querystring ($name,$def='',$type=''){
	return array_named($_GET,$name,$def,$type);
}

function querystring_unset ($name,$def='',$type=''){
	return array_get_unset($_GET,$name,$def,$type);
}

function getGlobal($name,$def='',$type='',$set=false){
	$isset = isset($GLOBALS[$name]);
	$value=array_named($GLOBALS,$name,$def,$type);
	if(!$isset && $set)$GLOBALS[$name]=$value;
	return $value;
}

function setGlobal($name,$value){
	$GLOBALS[$name]=$value;
}

function getHeader($name,$def=''){
	return array_named($_SERVER,"HTTP_$name",$def);
}
//alias for querystring
function getGet ($name,$def='',$type=''){
	return querystring($name,$def,$type);
}
//alias for querystring_unset
function getGetUnset ($name,$def='',$type=''){
	return querystring_unset($name,$def,$type);
}
/**
 * ritorna variabile post
*/
function getPost ($name,$def='',$type=''){
	return array_named($_POST,$name,$def,$type);
}
/**
 * ritorna variabile post e la cancella dall'insieme
*/
function getPostUnset ($name,$def='',$type=''){
	return array_get_unset($_POST,$name,$def,$type);
}

function getRequest ($name,$def='',$type=''){
	return array_named($_REQUEST,$name,$def,$type);
}
function getRequestUnset ($name,$def='',$type=''){
	return array_get_unset($_REQUEST,$name,$def,$type);
}
//shorthand for $locator->phisical
function phisical($path,$test_exists=false){
	$f=$GLOBALS['locator']->phisical($path);
	if($test_exists && !is_file($f)) return false;
	return $f;
}

//shorthand for $locator->uri
function uri($path){
	global $locator;
	return $locator->uri($path);
}
/**
 * read var from
 * 1. GET (e la mette nei cookie)
 * 2. Cookie + request
 * @param bool globalize put var in GLOBAL namespace
*/
function get_qc_var($varname,$default=null,$constrains = null,$globalize=true){
	$value=querystring_unset($varname,null); //*GLOBALS $value
	if($value){
		//set $varname in cookie
		setcookie($varname,$value);
	}
	else $value = getRequest($varname,$default);
	if(is_array($constrains) && $value!=null && !in_array($value,$constrains)){
		$value =$default;
	}
	return $value;
}

function destroy_cookie($name){
	setcookie($name,'',time()-60);
}
/**
 * getSession retrieve value from SESSION
 * @param $name string var name
 * @param $def mixed default value or callback
 * @param $createIfNull bool set session value with default or callback return value
 * @param $callback_args mixed if is array are parameters for callback
 * 
*/
function getSession ($name,$def='',$createIfNull=false,$callback_args=null){
	if(!isset($_SESSION)) session_start();
	if(isset($_SESSION[$name])) {
		$v =&$_SESSION[$name];		
	}
	else {
		if(is_callable($def))	{
			$createIfNull=true;
			if(!$callback_args)$callback_args=array();
			$v=call_user_func_array($def,$callback_args);//callback
		}
		else $v = $def;
		if($createIfNull) setSession($name,$v);
	}
	return $v;
}

function setSession ($name,$value){
	if(!isset($_SESSION)) session_start();
	$_SESSION[$name]=$value;
	return $value;
}

function unsetSession ($name){
	if(isset($_SESSION) && isset($_SESSION[$name])){
		unset($_SESSION[$name]);
	}
	$_SESSION[$name]=null;
}
function get_filenames($path){
	$match = array();
	foreach(glob($path) as $f){
		$match[]= basename($f);
	}
	return $match;
}

function camelize($s,$firstUpper=true){
	if(!$s||!is_string($s)) return $s;
	$s= preg_replace('~(_+)(\w{1})([^_]+)~se',"strtoupper('\\2').strtolower('\\3')",$s);
	if($firstUpper){
		$s = preg_replace('~(\w{1})(\w+)~e',"strtoupper('\\1').'\\2'",$s);
	}
	return $s;
}

//paginazione risultati
function get_paging($page,$pagesize,$count){
	$page = min_num(1,$page);
	$pagesize = min_num(1,$pagesize);
	$count = min_num(0,$count);
    $pages = max(ceil($count/$pagesize),1);
	if($pages && $pages<$page) $page = $pages;
	
    $first = ($page * $pagesize) - $pagesize + 1;
    $last = $first + $pagesize - 1;
    $nextpage = ($page==$pages)?0:$page+1;
    $prevpage = $page-1;
    $paging = new stdClass();
    $paging->pages=$pages;
	$paging->count=$count;
	$paging->pagesize=$pagesize;
    $paging->page=$page;
    $paging->first=$first;
    $paging->last=$last;
    $paging->next=$nextpage;
    $paging->prev=$prevpage;
	$paging->limit = "\r\nLIMIT " . (($page - 1) * $pagesize) . "," . $pagesize;
    return $paging;
}

//array in forma key:value;key2:value2
//oppure anche value1;value2
function strtoarray($s,$itemSeparator=";",$assignOperator=":"){
	return Arrays::string_to_array($s,$itemSeparator,$assignOperator);
}

function query_implode($get=null,$sep='&'){
   if(!$get) $get=$_GET;
   $parts = array();
   foreach($get as $key=>$value){
	   if($value=='') continue;
	   $value=urlencode($value);
	   $parts[]="$key=$value";
   }
   return implode($sep,$parts);
}
/**
 * build request querystring
 *
 * @param mixed $sa key/value list can be an associative array or a string like key=val|key2=val2
 * @param boolean $merge (optional default:false) if true the params list is merged with existing GETS params 
*/
function makeRequest ($sa, $merge=false, $fullurl=false,$url_path=""){
	$urlConstructor = getGlobal('urlConstructor','RequestBuilder','string',true); //<-extensible
	$rb = new $urlConstructor($sa);
	$rb->merge=$merge;
	$rb->fullurl=$fullurl;
	if($url_path){
		$rb->script_name = $url_path;
	}
	return $rb->build();
}

/**
 *redirect response
 * @param string $relative_url the url
*/
function redirect ($url=null,$params_merge=true){
	if(is_array($url)) {
		//parameters :
		$url = makeRequest($url,$params_merge,true);
	}
	Misc::redirect($url);
}

function createInstance($class,$args=null){
	$obj = new ReflectionClass($class);
	return $obj->newInstanceArgs((!is_array($args))?array():$args);
}
/**
 * Makes directory, returns TRUE if exists or made
 *
 * @param string $pathname The directory path.
 * @return boolean returns TRUE if exists or made or FALSE on failure.
 */

function mkdir_recursive($pathname, $mode=0777){
    is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
    return is_dir($pathname) || @mkdir($pathname, $mode);
}

function mkdir_check($pathname, $mode=0777){
	if(!is_dir($pathname) && $pathname) mkdir_recursive($pathname,$mode);
	return $pathname;
}

function check_dirs($base_dir,$paths){
	if(!is_array($paths)) $paths = array($paths);
	foreach($paths as $path){
		mkdir_check ("$base_dir/$path");
	}
}
/**
 * remove not empty dirs
 */
function rrmdir($dir) {
  if (is_dir($dir)) {
	$objects = scandir($dir);
	foreach ($objects as $object) {
	  if ($object != "." && $object != "..") {
		if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
	  }
	}
	reset($objects);
	rmdir($dir);
  }
} 
/**
 * restituisce come minimo un numero 
*/
function min_num ($n,$min){
    if(!is_numeric($n) || $n<$min) $n=$min;
    return $n;
}
/**
 * restituisce come massimo un numero 
*/
function max_num($n,$max){
	if(!is_numeric($n) || $n>$min) $n=$max;
    return $n;
}

# trasforma un valore letterale in bytes
function return_bytes($val) {
	return ByteHandler::return_bytes($val);
}

/**
 * formatta un numero come bytes
*/
function byte_size($bytes,$decimals=2,$dec_sep='',$thous_sep='') {
	return ByteHandler::byte_size($bytes,$decimals,$dec_sep,$thous_sep);
}

/**
 * prende l'ultima parte di una stringa dopo un separatore
 * es.
 * $s = "image.jpg"
 * $ext = str_last($s,".");
 * risultato : 'jpg'
*/
function str_last($s, $sep){
	if(!$s || strpos($s,$sep)===false) return '';
	if(!is_string($s)) return $s;
	$part = strrchr($s, $sep);
	if(!$part || !$sep) return $s;
	return substr($part,strlen($sep));
}

/**
 * estensione di un file
*/
function file_extension($filename){
	return str_last($filename,'.');
}
function add_extension($filename,$ext){
	$d = dirname($filename);
	if($d=='.')$d='';
	if($d) $d .= "/";
	if(strpos($ext,'.')===0) $ext = substr($ext,1);
	return $d.basename_without_extension("$filename").".$ext";
}
function basename_without_extension($s){
	if(!$s || !is_string($s)) return $s;
	$s = basename($s);
	$part = strrchr($s, '.');
	if($part) $s=substr($s,0,strlen($s)-strlen($part));
	return $s;
}

if(!function_exists('mime_content_type')) {
    function mime_content_type($filename) {
        return Misc::mime_content_type($filename);
    }
}
function dir_has_child_dirs($d){
	$result = false;
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if($file=='.'||$file=='..') continue;
				if(is_dir($file)) {
					$result = true;
					break;
				}
			}
			closedir($dh);
		}
	}
	return $result;
}
#controlla se un file è minore di post_max_size
function check_uploadsize ($upload,&$post_max_size="") {
	$post_max_size = ini_get('post_max_size');
	   if($upload['size']>return_bytes($post_max_size)){
		 return false;
	   }
	   return true;
}
function check_uploadextension($upload,$extensions) {
	if(is_array($extensions)){
		$name = $upload['name'];
		if($name) {
			$ext= file_extension($name);
			if($ext) return in_array(strtolower($ext),$extensions);
		}
	}
	return false;
}
/**
 * @return timestamp
*/
function str_to_date_time ($sdate,$ypos,$mpos,$dpos,$sep='/',$tsep=':'){
	return Dates::str_to_date_time($sdate,$ypos,$mpos,$dpos,$sep,$tsep);
}
/**
 * @return timestamp
*/
function str_to_date ($sdate,$ypos,$mpos,$dpos,$sep='/'){
	return Dates::str_to_date($sdate,$ypos,$mpos,$dpos,$sep);
}
/**
 * @return timestamp
*/
function str_to_date_it($sdate,$sep='/'){
	return str_to_date ($sdate,2,1,0,$sep);
}

function ms_escape_string($data) {
        if ( !isset($data) or empty($data) ) return '';
        if ( is_numeric($data) ) return $data;

        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );
        foreach ( $non_displayables as $regex )
            $data = preg_replace( $regex, '', $data );
        $data = str_replace("'", "''", $data );
        return $data;
}
//funzione escape string per DB MS SQL alias di ms_escape_string
function dbe($data){
	return ms_escape_string($data);
}

/**
 * @return timestamp
*/
function mysql_date_parse($date){
	return Dates::mysql_date_parse($date);
}
function mysql_date_rfc822($date){
	return date("r",mysql_date_parse($date));
}
function mysql_date_format ($sdate,$format='d/m/Y'){
	if(!$sdate) return '';
	$ss = explode(' ',$sdate);
	$partdate = $ss[0];
	$dt = str_to_date($partdate,0,1,2,'-');
	return date($format,$dt);
}
function mssql_date_format ($sdate,$format='d/m/Y'){
	if(!$sdate) return '';
	$ss = explode(' ',$sdate);
	$partdate = $ss[0];
	$dt = str_to_date($partdate,2,1,0,'-');
	return date($format,$dt);
}
function datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
	return Dates::datediff($interval, $datefrom, $dateto, $using_timestamps);
}


function dateCompare ($dt1,$dt2){
	return Dates::dateCompare($dt1,$dt2);
}

function dateAdd($interval, $number, $date) {
	return Dates::dateAdd($interval, $number, $date);
}


function str_truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false){
    if ($length == 0)
        return '';
    if (strlen($string) > $length) {
        $length -= min($length, strlen($etc));
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
        }
        if(!$middle) {
            return substr($string, 0, $length) . $etc;
        } else {
            return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
        }
    } else {
        return $string;
    }
}

function currency_format ($num){
	if(is_numeric($num)) return number_format($num,2,',','.');
	return '';
}
function currency_deformat ($snum){
	if($snum!=''){
//		echo $snum,'<br>';
		$snum = str_replace ('.','',$snum);
//		echo $snum,'<br>';
		$snum = str_replace (',','.',$snum);
//		echo $snum,'<br>';
	}
	return $snum;
}
function explode_part($separator,$str,$part=0){
	if($str) {
		$parts = explode($separator,$str);
		if($part=='last') return $parts[count($parts) - 1];
		if(isset($parts[$part])) return $parts[$part];
	}
	return "";
}


/**
 *arrayColumnSort
	usage:
  $test["pete"]['points']=1;
  $test["pete"]['name']='Peter';

  $test["ab"]['points']=2;
  $test["ab"]['name']='John Ab';

  $sorted = arrayColumnSort("points", SORT_DESC, SORT_NUMERIC, "name", SORT_ASC, SORT_STRING, $test);
*/
function arrayColumnSort() {
    $n = func_num_args();
    $ar = func_get_arg($n-1);
    if(!is_array($ar))
      return false;

    for($i = 0; $i < $n-1; $i++)
      $col[$i] = func_get_arg($i);

    foreach($ar as $key => $val)
      foreach($col as $kkey => $vval)
        if(is_string($vval))
          ${"subar$kkey"}[$key] = $val[$vval];

    $arv = array();
    foreach($col as $key => $val)
      $arv[] = (is_string($val) ? ${"subar$key"} : $val);
    $arv[] = $ar;

    call_user_func_array("array_multisort", $arv);
    return $ar;
  }
  

/**
 * prende il primo argomento valido (true)
*/
function first_valid(){
	$args = func_get_args();
	foreach($args as $arg){
		if($arg) return $arg;
	}
	return null;
}

function get_resource_string($s,$default='',$sprintfArgs = ''){
	if(!$default) {
		$default=$s;
	}
	if(!$s) $s = 'undefined';
	if(!is_array($sprintfArgs)) $sprintfArgs = array($sprintfArgs);
	$d= RES_get_resource_string($s);
	return vsprintf($d ,$sprintfArgs);
	//return LocaleLoader::get($s,$default,$sprintfArgs);
}

//shorthand for get_resource_string
function GRS($s,$default='',$sprintfArgs= ''){
	return get_resource_string($s,$default,$sprintfArgs);
}
function system_message($msg='',$title='Simplex message'){
	setGlobal('system_message',$msg);
	setGlobal('system_title',$title);
	include dirname(__FILE__) . DIRECTORY_SEPARATOR . "system.message.inc.php";
	die;
}
?>