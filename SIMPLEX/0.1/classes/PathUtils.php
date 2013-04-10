<?php

class PathUtils  {
    static function fix_slash($path){
        if($path) {
            $path = str_replace("/",DIRECTORY_SEPARATOR,$path);
        }
        return $path;
    }
    static function double_slash($path){
        if($path) {
            $path = str_replace("\\","\\\\",$path);
        }
        return $path;
    }
    static function trim_slash($path,$end=true){
        if($end){
            $last_char = substr($path,-1,1);
            
            if($last_char==DIRECTORY_SEPARATOR || $last_char=='/'){
                $path = substr($path,0,-1);
            }
        }
        else {
            $first_char = substr($path,0,1);
            if($first_char==DIRECTORY_SEPARATOR || $first_char=='/'){
                $path = substr($path,1);
            }
        }
        return $path;
    }
    static function file_change_prefix($file,$old="",$new="") {
        $dir = dirname($file);
        $filename = basename($file);
        $pos = stripos($file,$old);
        if($pos!==false && $pos==0){
            return $dir._.$new. substr($file,strlen($old));
        }
        $name = str_ireplace('extension-','admin-',basename($file));
        return $dir._.$name;
    }
    
    static function get_admin_extension_filename($file,$check_exists=true){
        $file = self::file_change_prefix($file,'extension-','admin-');
        if($check_exists && !is_file($file)) return false;
        return $file;
    }
    static function get_dirs($path,$pattern=null,$callback=null){
        $result=array();
        $dir_iterator=new DirectoryIterator($path);
        foreach($dir_iterator as $file){
            if($file->isDot())continue;
            if($file->isFile())continue;
            $filename=$file->getFilename();
            if($pattern){
                $a = array();
                if(!preg_match($pattern,$filename,$a)) continue;
                if($callback){
                    //callback signature:
                    //function fx(&$result,$filename,$match)
                    call_user_func($callback,$result,$filename,$a);
                }
                else $result[]=$filename;
            }
            else $result[]=$filename;
        }
        return $result; 
    }
    static function get_files($path,$pattern=null,$callback=null){
        $result=array();
        $dir_iterator=new DirectoryIterator($path);
        foreach($dir_iterator as $file){
            if($file->isDot())continue;
            if($file->isDir())continue;
            $filename=$file->getFilename();
            if($pattern){
                $a = array();
                if(!preg_match($pattern,$filename,$a)) continue;
                if($callback){
                    //callback signature:
                    //function fx(&$result,$filename,$match)
                    call_user_func($callback,$result,$filename,$a);
                }
                else $result[]=$filename;
            }
            else $result[]=$filename;
        }
        return $result;
    }
} 
?>