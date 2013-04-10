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
    if(isset($callback)){
        $buffer = call_user_func($callback,$buffer,$filename);
    }
    return $buffer;
}
function save_page($filename,$buffer){
    file_put_contents($filename,$buffer);
}
function check_page_ids($buffer,$filename){
    $doc = phpQuery::newDocumentPHP($buffer);
    $changes=0;
    
    $editables = $doc->find('[data-content=editable]');
    foreach($editables as $key=>$node){
        $id = pq($node)->attr('id');
        if(!$id) {
            $changes++;
            $id=uniqid('page');
            pq($node)->attr('id',$id);
        }
    }
    if($changes) {
        $buffer= $doc->htmlOuter();
        save_page($filename,$buffer);
    }
    $doc->find('.page')->attr('data-path',$filename);
    $buffer= $doc->htmlOuter();
    
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
        return eval_include($filename,array('callback'=>'check_page_ids'));
    }
    else {
        return $nd->tagName;
    }
}

function ajax_get_page($name){
    ob_start();
    $GLOBALS['current_page'] = $name;
    $page = Simplex::check_page_name($name);
    if($page){
       
        hook_call ('before_standard_parsers');
        //functions
        include_once dirname(__FILE__)._.'parsers.php';
        hook_call ('after_standard_parsers');
        ob_start();
        extract($GLOBALS);
        require $page;
        Simplex::display();
        $buffer = ob_get_clean();
        check_page_ids($buffer,$page);
        return $buffer ;
    }
    return "";
}

function admin_mode(){
    $admin_mode = false;
    if(function_exists('user') && user()) {
        $admin_mode = true;
    }
    return $admin_mode;
}
?>