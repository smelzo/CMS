<?php
function app__autoload($class_name){
    //names to search
    $test_paths=array(
            "classes/$class_name.php",
            "$class_name.php",
    );
    //directory dove cercare
    $search_base_dirs = array ( INCLUDE_PATH, APPLICATION_PATH);
    foreach($search_base_dirs as $base_dir){
        foreach($test_paths as $file){
            $file = "$base_dir/$file";
            if(is_file($file))  {
                require_once($file)	;
                return ;
            }
        }
    }
    //fb("class $class_name not found");
    return false;
}
function eval_include ($filename,$vars=null){
    if(!is_array($vars)) $vars = array();
    $vars = array_merge($GLOBALS,$vars);
    extract($vars,EXTR_SKIP);
    if(!is_file($filename)) return "";
    ob_start();
    require $filename;
    $buffer = ob_get_clean();
    return $buffer;
}

#################################
# parse <template name="name">
# parse <content>
function template_eval($vars,$nd){
    $name = array_get($vars,'name');
    if($nd->tagName=='template') {
        $filename = Simplex::template_include($name,$vars) ;
        return eval_include($filename,$vars);
    }
    elseif($nd->tagName=='content') {
        $filename = Simplex::context_include('');
        return eval_include($filename,array());
    }
    else {
        return $nd->tagName;
    }
}
function admin_mode(){
    $admin_mode = false;
    if(function_exists('user') && user()) {
        $admin_mode = true;
    }
    return $admin_mode;
}
?>