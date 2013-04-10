<?php
class Misc {
        static $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
			'docx' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
			'xlsx' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
			'pptx' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );	
	static function stripslashes_deep($value){
        $value = is_array($value) ?
                    array_map(array('Misc','stripslashes_deep'), $value) :
                    stripslashes($value);

        return $value;
    }
	static function fix_magic_quotes(){
	    $_POST = array_map(array('Misc','stripslashes_deep'), $_POST);
		$_GET = array_map(array('Misc','stripslashes_deep'), $_GET);
		$_COOKIE = array_map(array('Misc','stripslashes_deep'), $_COOKIE);
		$_REQUEST = array_map(array('Misc','stripslashes_deep'), $_REQUEST);
	}
	static function isHttps(){
		return isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == "on");
	}
	static function current_domain () {
		if ($_SERVER["SERVER_PORT"] != "80") {
				return  $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
		} else {
				return  $_SERVER["SERVER_NAME"];
		}
	}
	
	static function current_page_url() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		$pageURL .= self::current_domain() .$_SERVER["REQUEST_URI"];
		if(strripos($pageURL,'/') == strlen($pageURL)-1){
				$pageURL.="index.php";
		}
		return $pageURL;
	}
	
	static function server_url(){  
		$proto = "http" .
			((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "s" : "") . "://";
		$server = isset($_SERVER['HTTP_HOST']) ?
			$_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		return $proto . $server;
	}
	static function current_pagename(){
		$sn=$_SERVER['SCRIPT_NAME'];
		return array_pop (explode("/",$sn));
	}
	static function redirect ($url=null){
		global $locator;
		if($url) {	
			$location =$url;
			if(!preg_match('/^http{1}s*:{1}\\/{2}/m', $url)) {
				$location = $locator->resolve_app_path_uri($url);
			}
		}
		else $location = self::current_page_url();
		if(headers_sent()){
		echo '
		<script language="javascript" type="text/javascript">
			window.location="'.$location.'";
		</script>
		';
		}
		else header("Location: $location");
		die;
	}
    static function mime_content_type($filename) {
		$mime_types=self::$mime_types;
		if (function_exists('finfo_open') && is_file($filename)) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
		$ext = file_extension($filename);
        //$ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        else return 'application/octet-stream';
    }
static function utf8_encode_all($dat) {// -- It returns $dat encoded to UTF8
  if (is_string($dat)) {
		if(!mb_check_encoding($dat,'UTF-8')) $dat = utf8_encode($dat);
		return $dat;
  }
  if (!is_array($dat)) return $dat;
  $ret = array();
  foreach($dat as $i=>$d) $ret[$i] = self::utf8_encode_all($d);
  return $ret;
}

static function utf8_decode_all($dat){ // -- It returns $dat decoded from UTF8
  if (is_string($dat)) return utf8_decode($dat);
  if (!is_array($dat)) return $dat;
  $ret = array();
  foreach($dat as $i=>$d) $ret[$i] = self::utf8_decode_all($d);
  return $ret;
} 
}
?>