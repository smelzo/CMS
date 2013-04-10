<?php
include dirname(__FILE__).DIRECTORY_SEPARATOR.'phpThumb'.DIRECTORY_SEPARATOR.'phpthumb.class.php';
class Slides {
    static function build_site_gallery($reset=false){
        global $thumb_src, $thumb_gallery, $full_size,$thumb_size;
        $src_base = BASE_PATH."/$thumb_src";
        $store_file = BASE_PATH."/$thumb_gallery/gallery.dat";
        if(is_file($store_file) && !$reset) {
            return  unserialize( file_get_contents($store_file) );
        }
        $result = array();
        $i=0;
        $_full_params = array(
            'ar'=>true
        );
        foreach(glob($src_base."/*",GLOB_ONLYDIR) as $d){
            $item = new stdClass()   ;
            $item->title = basename($d);
            $item->images = array();
            foreach(glob("$d/*.{jpg,JPG,jpeg,JPEG}",GLOB_BRACE) as $f){
                $full_dir = mkdir_check(BASE_PATH."/$thumb_gallery/$i");
                $full_dir = mkdir_check("$full_dir/_full");
                $thumb_dir = mkdir_check(BASE_PATH."/$thumb_gallery/$i");
                $thumb_dir = mkdir_check("$thumb_dir/_thumb");
                $full_file = $full_dir . "/" . basename($f);
                if(!is_file($full_file)|| $reset){
                    self::_resize($f,$full_file,$full_size,$_full_params,true);
                }
                $thumb_file = $thumb_dir . "/" . basename($f);
                if(!is_file($thumb_file)|| $reset){
                    self::_resize($f,$thumb_file,$thumb_size);
                }
                $item->images[] = array(
                    'full' => "$thumb_gallery/$i/_full/". basename($f),
                    'thumb' => "$thumb_gallery/$i/_thumb/". basename($f)
                );
            }
            $result[]=$item;
            //echo basename($d),"\n";
            $i++;
        }
        file_put_contents($store_file,serialize($result));
        return $result;
    }
    
    static function _resize($or,$dest,$size,$params = null,$detect_rotation=false){
        if(!is_array($size) || !isset($size['width']) || !isset($size['height'])){
            throw new Exception("function Slides _resize fail: pass \$size as array[width|height]");
        }
        $resize_width = $size['width'];
        $resize_height =  $size['height'];
        if($detect_rotation) {
            $imsz= getimagesize($or);
            if($imsz[0]<$imsz[1]){
                //rotate
                    $resize_width = $size['height'];
                    $resize_height =  $size['width'];
            }
        }
        $phpThumb = new phpThumb();
        $phpThumb->setSourceFilename($or);
        $phpThumb->setParameter('w', $resize_width);
        $phpThumb->setParameter('h', $resize_height);
        $phpThumb->setParameter('aoe', true);
        $phpThumb->setParameter('q', 100);
        $phpThumb->setParameter('zc', true);
        if(is_array($params)){
            foreach($params as $key=>$item){
                $phpThumb->setParameter($key, $item);
            }
        }
        if ($phpThumb->GenerateThumbnail()) {
            //header("DEBUG:$imgcache_file");
            if(!$phpThumb->RenderToFile($dest)) {
                return 0;
            }
        }
        return 1;
    }
    
    static function prepare($src_dir,$full_size=null,$thumb_size=null,$reset=false){
        $result = array();
        $full_dir = mkdir_check($src_dir."/_full");
        $thumb_dir = mkdir_check($src_dir."/_thumbs");
        foreach(glob($src_dir."/*.{jpg,JPG,jpeg,JPEG}",GLOB_BRACE) as $f){
            $item  = array('description'=>null);
            $descr_file =  $full_dir . "/" . basename_without_extension($f).".txt";
            if(is_file($descr_file)){
                $item['description'] = file_get_contents($descr_file);
            }
            $full_file = $full_dir . "/" . basename($f);
            if(!is_file($full_file)|| $reset){
                self::_resize($f,$full_file,$full_size);
            }
            
            $thumb_file = $thumb_dir . "/" . basename($f);
            if(!is_file($thumb_file)|| $reset){
                self::_resize($f,$thumb_file,$thumb_size);
            }
            $item['file'] = basename($f);
            $result[]=$item;
        }
        return $result;
    }
}
?>