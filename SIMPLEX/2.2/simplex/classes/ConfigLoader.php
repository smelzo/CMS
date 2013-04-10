<?php
class ConfigLoader  extends IniLoader {
    private static $config = array();

    static function load_config($file,$pattern="/.\\.ini$/i"){
        $config = array();
        if(is_file($file)) {
            $config = self::parse_file($file);
        }
        elseif(is_dir($file)) {
            $config = self::parse_dir($file,true,$pattern);
        }
        if($config){
            foreach($config as $key=>$item){
                $GLOBALS[$key]=$item;
            }
        }
        self::$config = array_merge(self::$config, $config);
    }
    
    static function load_default_config(){
        self::load_config(APPLICATION_PATH. DIRECTORY_SEPARATOR ."default.config.ini");
    }
    
    static function load_custom_config(){
        self::load_config(DATA_PATH. DIRECTORY_SEPARATOR ."config.ini");
    }
    
    static function get_config($name,$def=null){
        return array_get(self::$config,$name,$def);
    }
    static function set_config($name,$value){
        self::$config[$name]=$value;
        $GLOBALS[$name]=$value;
        self::put(DATA_PATH. DIRECTORY_SEPARATOR ."config.ini",self::$config);
    }
    static function get_all(){
        return self::$config;
    }
} 
?>