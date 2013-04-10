<?php
class DbCommon {
    //execute statements ; separed
    static function split_execute ($sql,$splitter=';'){
        $statements = explode($splitter,$sql);
        foreach($statements as $statement){
            $statement = trim($statement);
            if($statement) {
                db()->execute($statement);
            }
        } 
    }    
    static function table_check($name){
        if(is_array($name)){
            foreach($name as $tb){
                self::table_check($tb);
            }
            return ;
        }
        if(!db()->tableExists($name)){
            $sql_template_file = APPLICATION_PATH. DIRECTORY_SEPARATOR ."sql". DIRECTORY_SEPARATOR ."$name.sql";
            if(!is_file($sql_template_file)){
                throw new Exception("$sql_template_file not exists!");
            }
            $create_sql = file_get_contents($sql_template_file);
            $create_sql = str_replace('[TABLE]',$name, $create_sql);
            self::split_execute($create_sql);
        }
        return db(); //chain
    }
}
?>