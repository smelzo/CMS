<?php
class Fm_Image  extends Fm_File{
    public $type;
    public $width;
    public $height;
	
	static function decore_file($file){
		foreach(getimagesize($file->fullpath) as $key=>$item){
                if(is_numeric($key)){
                    switch($key){
                        case 0;
                            $file->width = $item;
                            break;
                        case 1;
                            $file->height = $item;
                            break;
                        default:
                        continue;
                    }
                }
                else  $file->$key=$item;
        }
	}
    function __construct($file){
        if(!is_a($file,'Fm_File')){
            parent::__construct($file->fullpath);
        }
        else {
            $a=(array) $file;
            foreach($a as $key=>$item){
                $this->$key=$item;
            }
        }
        foreach(getimagesize($this->fullpath) as $key=>$item){
                if(is_numeric($key)){
                    switch($key){
                        case 0;
                            $this->width = $item;
                            break;
                        case 1;
                            $this->height = $item;
                            break;
                        default:
                        continue;
                    }
                }
                else  $this->$key=$item;
        }
        $this->type=str_replace('image/','',$this->mimetype);
    }
    
    function get_thumb_dir(){
        global $SITE_ROOT;
		$cache_dir = mkdir_check("$SITE_ROOT/cache");
		return mkdir_check("$cache_dir/images/thumbs");
    }
    
    function get_thumb_name($width=64,$height=64,$thumb_format='jpg'){
        $thumbs_dir = $this->get_thumb_dir();
        $hname =md5($this->name);
		
		return $thumbs_dir.DIRECTORY_SEPARATOR.$hname.$this->mtime.$width.'x'.$height.".$thumb_format";
    }
    
	function createThumb($width=64,$height=64,$hue=0,$saturation=0,$lightness=0,$thumb_format='jpg'){
		global $MAGICK,$USE_MAGICK;
        $thumbs_dir = $this->get_thumb_dir();
        
		$ratio=min($width/$this->width,$height/$this->height);
		$thumb_width=(int)($this->width*$ratio);
		$thumb_height=(int)($this->height*$ratio);
		
		$hsl='';
		$hslparam='';
		if($hue||$saturation||$lightness){
			$hsl=' -modulate '.($lightness+100).','.($saturation+100).','.(100+(int)($hue/1.8)).' ';
			$hslparam=',h'.$hue.',s'.$saturation.',l'.$lightness;
		}
		$file= $this->get_thumb_name($width,$height,$thumb_format);
        //if(is_file($file) && !querystring('no_cache')) return $file;
        $this->createResizedCopy($file,$thumb_width,$thumb_height);
//		if(!$USE_MAGICK || !$this->useImageMagick($this->fullpath,'resize '.$thumb_width.'x'.$thumb_height.$hsl,$file)){
//            $this->createResizedCopy($file,$thumb_width,$thumb_height);
//        }
		return $file;
	}
    function resize_on_place($width,$height){
		$ratio=min($width/$this->width,$height/$this->height);
		$thumb_width=(int)($this->width*$ratio);
		$thumb_height=(int)($this->height*$ratio);
		$tmp_name = $this->path.DIRECTORY_SEPARATOR.basename_without_extension($this->name).'.tmp.'.$this->extension;
		$this->createResizedCopy($tmp_name,$thumb_width,$thumb_height);
		unlink($this->fullpath);
		copy($tmp_name,$this->fullpath);
		unlink($tmp_name);
	}
    function createResizedCopy($to,$width,$height){
		$load='imagecreatefrom'.$this->type;
		$save='image'.$this->type;
		if(!function_exists($load)||!function_exists($save)) die( 'server cannot handle image of type "'.$this->type.'"');
		$im=$load($this->fullpath);
		$imresized=imagecreatetruecolor($width,$height);
		imagealphablending($imresized,false);
		imagecopyresampled($imresized,$im,0,0,0,0,$width,$height,$this->width,$this->height);
		imagesavealpha($imresized,true);
		$save($imresized,$to,($this->type=='jpeg'?100:9));
		imagedestroy($imresized);
		imagedestroy($im);
	}
    
    function useImageMagick($from,$action,$to){
        global $MAGICK;
        $IMAGEMAGICK_PATH = $MAGICK.DIRECTORY_SEPARATOR.'convert';
        
        if(file_exists("$IMAGEMAGICK_PATH.exe"))$IMAGEMAGICK_PATH = "$IMAGEMAGICK_PATH.exe";
		if(!file_exists($IMAGEMAGICK_PATH) ) {
            return false;
        }
		$retval=true;
		$arr=array();
        $from = fix_slash($from);
        $to = fix_slash($to);
        $cmd  = $IMAGEMAGICK_PATH.' "'.$from.'" -'.$action.' "'.$to.'"';
		exec($cmd,$arr,$retval);
		return $retval;
	}
    
    static function NewFmImage($path){
		$f = new Fm_Image($path);
		return $f;
	}
} 
?>