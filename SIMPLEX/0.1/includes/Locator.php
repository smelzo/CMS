<?php
#------------------locator-----------------------
# questo script serve a convertire le path in url e viceversa
# lo script per funzionare deve essere collocato in una
# directory immediatamente sotto la root es. /include
#------------------------------------------------

# header("content-type:text/plain");
/**
 * Locator class
*/
class Locator {
    public $app_base_url="";
    public $app_phisical_path="";
    public $current_relative="";

    function __construct(){
        $proto = "http" .
            ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "s" : "") . "://";
        $server = isset($_SERVER['HTTP_HOST']) ?  $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        
        $this->app_base_url = $proto . $server;
        
        $locator_file_path = $this->slash_fix(dirname(__FILE__));
        if(!defined('BASE_PATH')) $this->app_phisical_path = BASE_PATH;
        else $this->app_phisical_path =dirname($locator_file_path);
        
        #phisical script
        $self=dirname($_SERVER["PHP_SELF"]);
        $this->current_relative = self::slash_fix(dirname($_SERVER ['SCRIPT_NAME']));
        //REDIRECT_URL : /221/831/Incentivi-statali.html
       
        $script_filename = $_SERVER["SCRIPT_FILENAME"];
        $script_dir = $this->slash_fix(dirname($script_filename));
        
        $rel = str_replace($this->app_phisical_path,"", $script_dir);
        
        if($this->current_relative!="/") {
            if($rel!='/')$rel = str_replace($rel,"",$this->current_relative);
            else $rel = $this->current_relative;
        }
        $rel = self::trim_start($rel,'/');
        
        if(!defined('BASE_PATH') && $rel) {
            $this->app_base_url = self::path_combine($this->app_base_url,$rel);            
        }
        
        $isRoot= (strlen($this->current_relative)<=1);
        $this->relative_path= $this->app_base_url . $this->current_relative;
        if($isRoot) {
            $this->phisical_path = $this->app_phisical_path . $this->current_relative;
        }
        else {
            $this->phisical_path = $this->app_phisical_path ;
        }
        
    }
    function absolute_to_relative($abs_path){
        $ap = self::slash_fix($this->app_phisical_path,false);
        $rp = substr($abs_path,strlen($ap));
        $rp = self::trim_start($rp,'/');
        return $rp;
    }
    function resolve_path_phisical($relative_path){
        $relative_path = Locator::slash_fix($relative_path);
        $out = Locator::path_combine($this->phisical_path,$relative_path);
        return Locator::normalize_path($out);
    }
    function resolve_path_uri($relative_path){
        $relative_path = Locator::slash_fix($relative_path);
        $out = Locator::path_combine($this->relative_path,$relative_path);
        return Locator::normalize_path($out);
    }
    
    function resolve_app_path_phisical($relative_path){
        $relative_path = Locator::slash_fix($relative_path);
        $relative_path = Locator::normalize_path($relative_path);
        $relative_path = Locator::strip_parents($relative_path);
        return  Locator::path_combine($this->app_phisical_path, $relative_path);
    }
    function phisical($relative_path){//shortcut
        return $this->resolve_app_path_phisical($relative_path);
    }
    function resolve_app_path_uri($relative_path){
        //echo "<b>".$this->app_base_url."</b><br>";
        $relative_path = Locator::slash_fix($relative_path);
        $relative_path = Locator::normalize_path($relative_path);
        $relative_path = Locator::strip_parents($relative_path);
        
        return  Locator::path_combine($this->app_base_url, $relative_path);
    }
    function uri($relative_path){//shortcut
        return $this->resolve_app_path_uri($relative_path);
    }
    function get_info (){
        echo var_dump($this);
    }
    
    static function strip_parents ($s){
        return str_replace("../","",$s);
    }
    /**
     * elimina le path ../ intermedie risalendo
    */
    static function normalize_path ($s) {
        $parts = explode("/",$s);
        $a=array();
        $i=0;
        foreach($parts as $part){
            if($part=="..") {
                unset($a[$i]);
                $i--;
            }
            else {
                $a[$i]=$part;
                $i++;
            }
        }
        return implode("/",$a);
    }    
    /**
     * Trim characters from start of string
     * trim_start(string subject,[trimChar],[...])
    */
    static function trim_start($s){
        $args = func_get_args();
        for($i=1,$l=count($args);$i<$l;++$i){
            $s= preg_replace('/^' . preg_quote($args[$i],'/') . '+/im','',$s);
        }
        return $s;
    }    
    /**
    * Trim characters from end of string
    * trim_end(string subject,[trimChar],[...])
   */
   static function trim_end($s){
       $args = func_get_args();
       for($i=1,$l=count($args);$i<$l;++$i){
           $s= preg_replace('/' . preg_quote($args[$i],'/') . '+$/im','',$s);
       }
       return $s;
   }
   
   static function path_combine ($path1,$path2) {
       return Locator::trim_end(
                                Locator::trim_end($path1,'/') .'/'. Locator::trim_start($path2,'/'),
                                '/');
   }
    static function str_ends_with($ends,$s){
        $ends_len = strlen($ends);
        $s_len = strlen($s);
        if($s_len>=$ends_len) {
            $end = substr($s,$s_len - $ends_len - 1);
            return $end==$ends;
        }
        return false;
    }
    static function slash_fix($path,$append_end=true){
        $p = str_replace("\\","/",$path);
        if($append_end && ! Locator::str_ends_with("/",$p)) $p.="/";
        return $p;
    }
}
?>