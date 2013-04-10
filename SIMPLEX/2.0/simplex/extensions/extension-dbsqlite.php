<?php
if(!function_exists('db')) : 
    define('EXTENSION_DBSQLITE',1);
    
    include_once INCLUDE_PATH. DIRECTORY_SEPARATOR ."adodb-pdo_sqlite-all.php";
    
    $dsn = 'sqlite:'.DATA_PATH. DIRECTORY_SEPARATOR .'data.sq3';
    $db = new Connector($dsn,'pdo');
    /**
     * @return Connector
    */
    function db(){
        return $GLOBALS['db'];
    }
endif;
?>