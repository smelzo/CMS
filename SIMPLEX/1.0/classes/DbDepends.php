<?php
abstract class DbDepends  {
    function __construct($tables){
        if(!function_exists('db')) {
            throw new Exception("Database layer not exists");
        }
        DbCommon::table_check($tables);
    }
} 
?>