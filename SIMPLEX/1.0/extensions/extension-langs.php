<?php

//get languages from config
$langs = getGlobal('langs',array(
    'it'=>'italiano'
));

$langs_keys = array_keys($langs);
//$resources Array
$resources = array();

//determine $current_lang
$current_lang = querystring(array('l','lg','lang'),$langs_keys[0]);

function langs(){
    return getGlobal('langs');
}
function langs_keys(){
    return getGlobal('langs_keys');
}
function current_lang(){
    return getGlobal('current_lang');
}

$ViewResourceHandler = new ResourceHandler($current_lang,"resources","resources",array(DATA_PATH , APPLICATION_PATH));

//utility function _()
//viene usata nel codice come shortcut per stringhe di definizione
function _($name){
    global $ViewResourceHandler;
    return $ViewResourceHandler->get($name);
}

function langs_fragment_render($node,$lang=null,$remove=false,$hide_class="hide"){
    if(!$lang) $lang = current_lang();
    
    //elements
    $lang_elements = pq($node)->find('[lang]');
    foreach($lang_elements as $key=>$e){
        $e = pq($e);
        if($e->attr('lang')!=$lang){
            if($remove || !pq($e)->parents('body')->length) {
                $e->remove();
            }
            else {
                $e->addClass($hide_class);
            }
        }
    }
}
function langs_element_replace($doc=null){
    if(!$doc){
        Simplex::add_parser(__FUNCTION__);
        return ;
    }
    
    $remove = !admin_mode();
    langs_fragment_render($doc,current_lang(),$remove,"hide");
    
}

hook_register('after_standard_parsers','langs_element_replace');

function langs_js_resources_iniect($doc = null){
    if(!$doc){
        Simplex::add_parser(__FUNCTION__);
        return ;
    }
    global $ViewResourceHandler;
    $ViewResourceHandler->js_iniect($doc);
}
hook_register('after_standard_parsers','langs_js_resources_iniect');

if(function_exists('user')) :
    if(user()) {
        if(!isset($admin_lang)) $admin_lang = "it";
        $AdminResourceHandler = new ResourceHandler($admin_lang,"admin_resources","admin_resources",array(DATA_PATH , APPLICATION_PATH._."admin"));
        if(!function_exists('a_')) {
            function a_($name){
                global $AdminResourceHandler;
                return $AdminResourceHandler->get($name);
            }
        }
        function langs_js_admin_resources_iniect($doc = null){
            if(!$doc){
                Simplex::add_parser(__FUNCTION__);
                return ;
            }
            global $AdminResourceHandler;
            $AdminResourceHandler->js_iniect($doc);
        }
        hook_register('after_standard_parsers','langs_js_admin_resources_iniect');
    }
endif; //end auth
/**
 * Sostituizione di tutte le stringhe [:name:] con _(name)
*/
function langs_replace_resources($match){
    $def_name = array_get($match,1);
    if($def_name) {
        return _(trim($def_name));
    }
    return "";
}
function langs_buffer_parser(){
    $buffer = getGlobal('buffer');
    $buffer = preg_replace_callback('/\\[:{1}([\w\-\s]+)?:{1}\]/','langs_replace_resources',$buffer);
    setGlobal('buffer',$buffer);
}
hook_register('before_display','langs_buffer_parser');
//* END


function langs_parse_page_link($param){
    $result = array();
    $params = explode("/",$param);
    foreach($params as $i=>$item){
        switch($i){
            case 0 :
                if($item!='this')
                    $result['p']= $item;
                break;
            case 1 :
                $result['lg']= $item;
                break;
        }
    }
    return $result;
}
hook_register('parse_page_link','langs_parse_page_link');
?>