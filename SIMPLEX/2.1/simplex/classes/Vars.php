<?php
class Vars extends DbCommon {
    /**
     * @return Connector
    */
    private static function getDb(){
        return parent::table_check('vars');
    }
    private static function _name($name,$lang=""){
        $parts =  array(
            Simplex::get_pagename(), $name
        );
        if($lang) $parts[]=$lang;
        return implode('_',$parts);
    }
    static function get($name,$lang="",$echo=true){
        $name = self::_name($name,$lang);
        $val = self::getDb()->queryScalar("SELECT val FROM vars WHERE name='$name'");
        if($echo) echo $val;
        return $val ;
    }
    static function set($name,$val,$lang=""){
        $name = self::_name($name,$lang);
        self::getDb()->InsertUpdate('vars',array('name'=>$name,'val'=>$val),true,'name');
    }
}
?>