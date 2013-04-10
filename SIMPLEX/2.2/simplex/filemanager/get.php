<?php
$CMS_SKIP_AJAX=true;
$CMS_SKIP_DB=true;
require "config.php";
$folder = current_folder();
$name = querystring('name');
$file = Fm_File::NewFmFile($folder->path . DIRECTORY_SEPARATOR . $name);

function out_image_existent($imgcache_file){
    $cacheOff=(querystring('cache','')=='off');
    if($cacheOff && is_file($imgcache_file)) unlink($imgcache_file);
    header('Cache-Control: public');
    // this resource expires one month from now.
    header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+1 month')).' GMT');
    if(is_file($imgcache_file)){
        //il file esiste nella cache
        $mtime = filemtime($imgcache_file);//data del file cache
        $etag = md5($mtime.$imgcache_file);
        // send a unique 'strong' identifier. This is always the same for this 
        // particular file while the file itself remains the same.
        header('ETag: "'.$etag.'"');
        // Create a HTTP conformant date, example 'Mon, 22 Dec 2003 14:16:16 GMT'
        $gmt_mtime = gmdate('D, d M Y H:i:s', $mtime).' GMT';
        if(!$cacheOff){
            if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){	
                if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime){
                    header('HTTP/1.1 304 Not Modified');
                    exit();
                }
            }
            // check if the Etag sent by the client is the same as the Etag of the
            // requested file. If so, return 304 header and exit.
            if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
                if (str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $etag){
                    header("HTTP/1.1 304 Not Modified");
                    // abort processing and exit
                    exit();
                }
            }
        }
        // output last modified header using the last modified date of the file.
        header('Last-Modified: '.$gmt_mtime);
    
        //read cached
        $info=getimagesize($imgcache_file);
        /*
        debug :
        $c = file_get_contents($imgcache_file);
        echo $c;exit;
        */
        
        header("Content-type: ".$info['mime']);
        $i=@readfile($imgcache_file);
        exit;
    }    
}
function outfile($path){
    $i=@readfile($path);
    exit;
}
    if(!$file) {
        header("HTTP/1.0 404 Not Found");
        die;
    }
    //header('Cache-Control: max-age = 2592000');
    //header('Expires-Active: On');
    //header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
    //header('Pragma:');
    //header('Content-Length: '.(string)($file->size));
    set_time_limit(0);
    if($file && $file->is_image){
        //@var Fm_Image
        $image = $file->get_image();
        $width = querystring(array('w','width'),0);
        $height = querystring(array('h','height'),0);
        $hue = querystring('hue',0);
        $saturation = querystring('sat',0);
        $lightness = querystring('light',0);
    
        if($width && $height) {
            if($width<$image->width && $height<$image->height) {
                
                $mimetype = 'image/jpeg';
                $thumb_format = 'jpg';
                if($image->extension=='gif' || $image->extension=='png') {
                    $mimetype = 'image/png';
                    $thumb_format = 'png';
                }
                $thumb_name = $image->get_thumb_name($width,$height,$thumb_format);
                
                if(is_file($thumb_name) && querystring('cache')!='off') {
                    out_image_existent($thumb_name);
                    die;
                }
                $outfile = $image->createThumb($width,$height, $hue,$saturation,$lightness,$thumb_format);
                if(is_file($outfile) && filesize($outfile)){
                    
                    header('Content-Type: '.$mimetype);        
                    outfile($outfile);
                }
                else {
                    header('Content-Type: text/plain');
                    echo $outfile;
                }
            }
        }
    }
    if(querystring(array('d','download'))) {
        header('Content-Type: force/download');
        header('Content-Disposition: attachment; filename="'.$file->name.'"');
    }
    else {
        header('Content-Type: '.$file->mimetype);
    }
    header('Content-Transfer-Encoding: binary');
    outfile($file->fullpath);
?>