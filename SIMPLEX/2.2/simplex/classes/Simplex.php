<?php
require_once(INCLUDE_PATH."/phpQuery/phpQuery/phpQuery.php");
define('PAGETYPE_FROM_NAME',0);
define('PAGETYPE_FROM_QUERYSTRING',1);

class Simplex extends PathUtils{
    static $parsers = array(); //lista dei parser
    static $is_home = false;
    private static $_stored_pagename = null;
    private static $pass_pagename = null;
    
    static function is_home(){
        self::get_pagename();
        return self::$is_home;
    }
    
    static function remove_parser ($parser_func_name){
        foreach(self::$parsers as $i=>$item){
            if($parser_func_name==$item) {
                unset(self::$parsers[$i]);
            }
        }
    }
    
    static function add_parser ($parser_func_name){
        self::$parsers[] = $parser_func_name;
    }

/**
 * valuta se esiste un template alternativo per una determinata pagina
*/    static function eval_alt_template(&$buffer){
        $current_page = Simplex::get_pagename();
        
        $alt_template = TEMPLATES_PATH. DIRECTORY_SEPARATOR ."page-$current_page.php";
        if(file_exists($alt_template)){
            ob_start();
            extract($GLOBALS,EXTR_REFS);
            require($alt_template);
            $buffer = ob_get_clean();
        }
    }
    static function display(){
        $buffer = ob_get_clean();
        self::eval_alt_template($buffer);
        
        $doc = phpQuery::newDocument($buffer);
        foreach(self::$parsers as $parser){
            
            call_user_func($parser,$doc);
        }
        $buffer = $doc->htmlOuter();
        //echo $buffer; die;
        setGlobal('buffer',$buffer);
        //$GLOBALS['buffer']=$buffer; //share Global buffer variable
        hook_call('before_display');
        echo getGlobal('buffer');
        hook_call('after_display');
        if(querystring('hooks')){
            file_put_contents(BASE_PATH. DIRECTORY_SEPARATOR .'called_hooks.txt',implode("\n",$GLOBALS['HOOKS']->get_called_hooks()));
        }
    }
    static function get_page($pagename){
        self::$pass_pagename = $pagename;
        hook_call ('before_standard_parsers');
        //functions
        include_once APPLICATION_PATH .'/parsers.php';
        hook_call ('after_standard_parsers');
        $current_page = Simplex::get_pagename();
        ob_start();
        ob_start();
        echo '<content></content>';
        self::display();
        $buffer = ob_get_clean();
        self::$pass_pagename = null;
        return $buffer ;
    }

    static function check_page_name($name = "",$BASE_PATH = ""){
        $extensions = array('php','html','htm');
        $BASE_PATH = hook_call('check_page_name_path',$name);
        if(!$BASE_PATH) $BASE_PATH = PAGES_PATH;
        foreach($extensions as $ext){
            $file = $BASE_PATH. DIRECTORY_SEPARATOR ."$name.$ext";
            if(is_file($file)){
                return $file;
            }
        }
    }    
    /**
     * pagename
    */
    static function get_pagename(&$type=0){
        if(self::$pass_pagename) return self::$pass_pagename;
        if(self::$_stored_pagename) return self::$_stored_pagename;
        $default_pagename = basename_without_extension(Misc::current_pagename());
        if(isset($GLOBALS['default_pagename'])) $default_pagename  = $GLOBALS['default_pagename'];
        // 1 . desume it by php document name
        $type = PAGETYPE_FROM_NAME;
        $result = $default_pagename ;
        // 2. GET var
        $qs_result = querystring(array('p','page'),null);
        if($qs_result){
            $result = $qs_result;
            $type = PAGETYPE_FROM_QUERYSTRING;
        }
        self::$is_home=($result==$default_pagename);
        //HOOK before_pagename CALL
        $before_pagename_result = hook_call('before_pagename',$result);
        if($before_pagename_result) $result = $before_pagename_result;
        
        // checks
        if(!self::check_page_name($result)){
            $missing_pagename_result = hook_call('handle_missing_pagename',$result);
            if($missing_pagename_result) $result = $missing_pagename_result;
            else {
                if($result==$default_pagename){
                    //create default page
                    if(!@file_put_contents($p,"default page: edit content")){
                        system_message( '<b>/'. basename(PAGES_PATH). "/$default_pagename.php". '</b> file does not exists and cannot be created. Create it manually');
                    }
                }
                else {
                    $result = $default_pagename ;
                }
            }
        }
        self::$_stored_pagename = $result;
        return $result;
    }
    
    static function get_page_empty(){
        $current_pagename = self::get_pagename();
        $file = PAGES_PATH. DIRECTORY_SEPARATOR ."empty.php";
        if(!is_file($file)){
            if(!@file_put_contents($file,"")){
                system_message( '<b>/'. basename(PAGES_PATH). "/$file.php". '</b> file does not exists and cannot be created. Create it manually');
            }
        }
        return $file;
    }
    
    static function evalued_include($filename = "", $params=array()){
        
        if(!is_file($filename)) {
            setGlobal('not_found',$filename);
            return APPLICATION_PATH . DIRECTORY_SEPARATOR . "not_found_include.php";
        }
        return $filename;
    }

    static function page_include($name = "", $params=array()){
        $extensions = array('php','html','htm');
        foreach($extensions as $ext){
            $file = PAGES_PATH. DIRECTORY_SEPARATOR ."$name.$ext";
            if(is_file($file)){
                return self::evalued_include($file);
            }
        }
        return self::evalued_include($file);
    }
    
    static function template_include($name = "", $params=array()){
        $file = TEMPLATES_PATH. DIRECTORY_SEPARATOR ."$name.php";
        return self::evalued_include($file);
    }
    
    /**
     * get filename for include based on current page name
     * can pass params as querystring for included files
    */
    static function context_include($name = "",$params=array()){
        $current_pagename = self::get_pagename();
        
        $file = self::check_page_name($current_pagename);

        //if(!$name){
        //    $file = PAGES_PATH. DIRECTORY_SEPARATOR ."$current_pagename.php";
        //}
        //else {
        //    $file = PAGES_PATH. DIRECTORY_SEPARATOR ."$current_pagename.$name.php";
        //}
        if(!is_file($file)){
            $file = self::get_page_empty();
        }
        if(is_array($params) && $params){
            foreach($params as $key=>$item){
                $_GET[$key]=$item;
            }
        }
        return $file;
    }
    /**
     * @param $script_path url of scripts dir default /scripts
     * @param $script_name name of script default $current_pagename
     * @param $script_prefix prefix of script
     * @param $script_suffix suffix of script
    */
    static function context_script($script_path=null,$script_name=null,$script_prefix=null,$script_suffix=null){
        $current_pagename = self::get_pagename();
        if(!$script_name) $script_name = $current_pagename;
        $script_name = self::fix_name($script_name);
        if(!$script_path) {
            $script_path= "";
            foreach(array('scripts','js') as $item){
                if(is_dir(BASE_PATH . DIRECTORY_SEPARATOR . $item))
                $script_path= $item;
            }
        }
        if($script_prefix) $script_name = "$script_prefix.$script_name";
        if($script_suffix) $script_name = "$script_name.$script_suffix";
        $script_name = "$script_name.js";
        $script_path.= "/$script_name";
        if(is_file(BASE_PATH . DIRECTORY_SEPARATOR .$script_path)){
            ?>
            <script type="text/javascript" src="<?php echo $script_path?>"></script>
            <?php 
        }
        else {
            ?>
            <script type="text/javascript">
                console.log("missing script: <?php echo $script_path?>")
            </script>
            <?php 
        }
    }
    static function fix_name($name){
        return str_replace(' ','-',$name);
    }
    /**
     * @param $css_path url of css dir default /css
     * @param $css_name name of css default $current_pagename
     * @param $css_prefix prefix of css
     * @param $css_suffix suffix of css
    */
    static function context_css($css_path=null,$css_name=null,$css_prefix=null,$css_suffix=null){
        $current_pagename = self::get_pagename();
        if(!$css_name) $css_name = $current_pagename;
        $css_name = self::fix_name($css_name);
        if(!$css_path) {
            $css_path= "";
            foreach(array('css','styles') as $item){
                if(is_dir(BASE_PATH . DIRECTORY_SEPARATOR . $item))
                $css_path= $item;
            }
        }
        if($css_prefix) $css_name = "$css_prefix.$css_name";
        if($css_suffix) $css_name = "$css_name.$css_suffix";
        $css_name = "$css_name.css";
        $css_path.= "/$css_name";
        if(is_file(BASE_PATH . DIRECTORY_SEPARATOR .$css_path)){
            ?>
            <link rel="stylesheet" type="text/css" href="<?php echo $css_path?>"/>
            <?php 
        }
        else {
            ?>
            <script type="text/javascript">
                console.log("missing css : <?php echo $css_path?>")
            </script>
            <?php 
        }
    }
#   static function double_slash($path)
#   function trim_slash($path,$end=true)
#   static function fix_slash($path)   
#   static function get_files($path,$pattern=null,$callback=null)



}
?>