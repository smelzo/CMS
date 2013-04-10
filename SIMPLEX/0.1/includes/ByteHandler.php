<?php
$BYTEHANDLER_BYTES=getGlobal('BYTEHANDLER_BYTES','bytes');

class ByteHandler {
	static function return_bytes($val) {
		if(is_numeric($val)) return $val;
		$val = trim($val);
		if(!$val) return 0;
		if(strlen($val)-1 < 0) return 0;
		$last = $val{strlen($val)-1};
		switch($last) {
			case 'k':
			case 'K':
				return   $val * 1024;
				break;
			case 'm':
			case 'M':
				return  $val * 1048576;
				break;
			case 'g':
			case 'G':
				return  $val * 1073741824;
				break;
			default:
				return $val;
		}
	}
	static function byte_size($bytes,$decimals=2,$dec_sep='',$thous_sep='') {
		global $BYTEHANDLER_BYTES;
		if(!$dec_sep || !$thous_sep){
			$lc=localeconv();
			if(!$dec_sep ) $dec_sep =$lc['decimal_point'];
			if(!$thous_sep ) $thous_sep =$lc['thousands_sep'];
		}
		if($bytes<1024) return  "$bytes $BYTEHANDLER_BYTES";
		$size = $bytes / 1024;
		if($size < 1024){
			$size = number_format($size, $decimals,$dec_sep,$thous_sep);
			$size .= " Kb";
		} 
		else if($size / 1024 < 1024) {
				$size = number_format($size / 1024, $decimals,$dec_sep,$thous_sep);
				$size .= " Mb";
		} 
		else if ($size / 1024 / 1024 < 1024)  {
				$size = number_format($size / 1024 / 1024, $decimals,$dec_sep,$thous_sep);
				$size .= " Gb";
		}
		else if ($size / 1024 / 1024 / 1024 < 1024)  {
				$size = number_format($size / 1024 / 1024, $decimals,$dec_sep,$thous_sep);
				$size .= " Tb";
		}
		return $size;
	}


}
?>