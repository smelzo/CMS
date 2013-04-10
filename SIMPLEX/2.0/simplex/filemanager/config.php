<?php
$FM_DIR = dirname(__FILE__); // base/simplex/filemanager
$WEEXT_DIR = dirname($FM_DIR); // base/simplex
$SITE_ROOT = dirname($WEEXT_DIR); // base
$MAGICK = dirname($FM_DIR) . DIRECTORY_SEPARATOR . "imagick";
$USE_MAGICK = false;// is_dir($MAGICK);
function fm_autoload($class_name){
    global $FM_DIR;
    $file = $FM_DIR . DIRECTORY_SEPARATOR .  "classes" . DIRECTORY_SEPARATOR .  "$class_name.php";
    if(is_file($file)){
        require_once($file);
    }
}
spl_autoload_register('fm_autoload');

$CMS_SKIP_PLUGINS = 1;
$CMS_SKIP_OUTPUT = 1;

//insert parent config.php
//require_once($SITE_ROOT.DIRECTORY_SEPARATOR."config.php");
if(!defined('CMS')) define('CMS',dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'cms');
//  INCLUDE_PATH
if(!defined('INCLUDE_PATH'))define('INCLUDE_PATH',$WEEXT_DIR.DIRECTORY_SEPARATOR.'includes');
if(!defined('WEB_ROOT'))define('WEB_ROOT',$SITE_ROOT);
if(!defined('BASE_PATH'))define('BASE_PATH',WEB_ROOT);
if(!defined('DATA_PATH'))define('DATA_PATH',WEB_ROOT.DIRECTORY_SEPARATOR.'data');
if(!defined('FILES_PATH'))define('FILES_PATH',WEB_ROOT.DIRECTORY_SEPARATOR.'files');
if(!defined('IMAGES_PATH'))define('IMAGES_PATH',WEB_ROOT.DIRECTORY_SEPARATOR.'images');
require_once(INCLUDE_PATH.DIRECTORY_SEPARATOR."lib.php");

function get_accept_types(){
    $has_types = querystring(array('types','t'),false);
    $filetypes = querystring(array('types','t'),'*');
    
    if(strpos($filetypes,','!==false)){
        $filetypes = explode(',',$filetypes);
    }
    else $filetypes = array($filetypes);
    $items = array();
    foreach($filetypes as $ACCEPT_TYPES){
        switch($ACCEPT_TYPES){
            case 'images':
                $ACCEPT_TYPES='gif|jpe?g|png';
                break;
            case 'office':
                $ACCEPT_TYPES='docx?|xlsx?|pptx?|pdf|zip|7z|rar|rtf|txt|html?';
                break;
            case 'web':
                $ACCEPT_TYPES='gif|jpe?g|png|pdf|zip|7z|rar|rtf|txt|html?';
                break;
            case '*':
            case 'all':
            default:
                $ACCEPT_TYPES='.+';
        }
        $items[]=$ACCEPT_TYPES;
    }
    $custom_types = querystring(array('ctypes','ct'),false);
    if($custom_types){
        if($has_types) $items[]=$custom_types;
        else $items =array($custom_types);
    }
    return   implode('|',$items);
}

$ACCEPT_TYPES = get_accept_types();
$locator = new Locator();
$locator->app_base_url=dirname(Misc::current_page_url());

$IMAGE_MAX_WIDTH = querystring(array('mw','max-width'),1200);
$IMAGE_MAX_HEIGHT = querystring(array('mh','max-height'),900);


require_once($FM_DIR.DIRECTORY_SEPARATOR."fm_core.php");
//insert local core.php

if(!isset($GLOBALS['CMS_SKIP_AJAX'])):
# AJAX CALLS HANDLER
AjaxResponse::response();
endif;
?>