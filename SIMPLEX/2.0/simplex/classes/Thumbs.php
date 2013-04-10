<?php
include_once INCLUDE_PATH. DIRECTORY_SEPARATOR .'phpThumb'. DIRECTORY_SEPARATOR .'phpthumb.class.php';
class Thumbs  {
    static function get_thumb_path ($original_filename,$width,$height){
        $path = dirname($original_filename);
        $filename = basename($original_filename);
        $name = basename_without_extension($filename);
        $extension = file_extension($filename);
        return $path. DIRECTORY_SEPARATOR ."$name.thumb.{$width}x{$height}.$extension";
    }
    
    static	function createThumb($original_filename, $width=64, $height=64){
        $thumbs_filename = self::get_thumb_path($original_filename,$width,$height);
        self::createResizedCopy($original_filename,$thumbs_filename,$width,$height);
	}
    static function createResizedCopy($original_filename,$to,$width,$height){
		set_time_limit(0);
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename($original_filename);
		$phpThumb->setParameter('w', $width);
		$phpThumb->setParameter('h', $height);
		$phpThumb->setParameter('aoe', true);
        $mimetype = Misc::mime_content_type($original_filename);
		$type = str_replace('image/','',$mimetype);
		$phpThumb->setParameter('config_document_root', BASE_PATH);
		$phpThumb->setParameter('config_cache_directory', CACHE_PATH);
		$phpThumb->setParameter('config_output_format', $type);
		if ($phpThumb->GenerateThumbnail()) {
			if(!$phpThumb->RenderToFile($to)){
				fb($phpThumb->debugmessages);
			}
		}
	}
    static function checkFileSize($filename,$max_width,$max_height){
        $imagesize = getimagesize($filename);
        if($imagesize[0]>$max_width ||
           $imagesize[1]>$max_height
           ) {
            self::createResizedCopy($filename,$filename,$max_width,$max_height);
        }
    }
} 
?>