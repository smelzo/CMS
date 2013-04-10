<?php
    include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'core.php';
    $ADMIN_MODE = Misc::current_pagename()=='admin.php';
    $ADMIN_MODE = Misc::current_pagename()=='manager.php';
    //extensions
    $extensions_loaded = array();
    # STANDARD EXTENSION
    $extensions = Simplex::get_files(EXTENSIONS_PATH,'/extension\\-(\\w+)\\.php$/i');
    foreach($extensions as $filename){
        $extension_name = str_ireplace('extension-','',basename_without_extension($filename));
        if(!in_array($extension_name,$extensions_loaded)) {
            include EXTENSIONS_PATH . DIRECTORY_SEPARATOR . $filename;
            $extensions_loaded[]=$extension_name;
        }
    }
    # EXTENSIONS DEFINED FOR SPECIFIC SITE
    $custom_ext_path = BASE_PATH . DIRECTORY_SEPARATOR . 'extensions';
    if(is_dir($custom_ext_path)) {
        $extensions = Simplex::get_files($custom_ext_path,'/extension\\-(\\w+)\\.php$/i');
        foreach($extensions as $filename){
           $extension_name = str_ireplace('extension-','',basename_without_extension($filename));
           if(!in_array($extension_name,$extensions_loaded)) {
               include $custom_ext_path . DIRECTORY_SEPARATOR . $filename;
               $extensions_loaded[]=$extension_name;
           }
       }       
    }

    hook_call ('extensions_loaded');
    
    if(hook_call ('ajax_response')!==-1) {
        AjaxResponse::response();
    }
    if(!defined('SKIP_DISPLAY')) {
        header('Content-Type:text/html; charset=UTF-8');
        hook_call ('before_standard_parsers');
        //functions
        include_once dirname(__FILE__). DIRECTORY_SEPARATOR .'parsers.php';
        hook_call ('after_standard_parsers');
        $current_page = Simplex::get_pagename();
        ob_start();
        register_shutdown_function(array('Simplex','display'));
    }
    
    
?>