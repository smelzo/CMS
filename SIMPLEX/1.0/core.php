<?php
if(!defined('_')) define("_",DIRECTORY_SEPARATOR);
if(!defined('APPLICATION_PATH')) define("APPLICATION_PATH",dirname(__FILE__));
if(!defined('INCLUDE_PATH')) define("INCLUDE_PATH",APPLICATION_PATH . _ . 'includes');
if(!defined('EXTENSIONS_PATH')) define("EXTENSIONS_PATH",APPLICATION_PATH . _ . 'extensions');
if(!defined('BASE_PATH')) define("BASE_PATH",dirname(APPLICATION_PATH));
if(!defined('CACHE_PATH')) define("CACHE_PATH",BASE_PATH . _ . 'cache');
if(!defined('DATA_PATH')) define("DATA_PATH",BASE_PATH . _ . 'data');
if(!defined('PAGES_PATH')) define("PAGES_PATH",BASE_PATH . _ . 'pages');
if(!defined('TEMPLATES_PATH')) define("TEMPLATES_PATH",BASE_PATH . _ . 'templates');

include_once INCLUDE_PATH._.'lib.php';
mkdir_check(DATA_PATH);
mkdir_check(PAGES_PATH);
mkdir_check(TEMPLATES_PATH);
mkdir_check(EXTENSIONS_PATH);

include_once APPLICATION_PATH._.'functions.php';
spl_autoload_register('app__autoload');

//load ini configuration
include_once APPLICATION_PATH._.'config.php';

$HOOKS = new Hooks(); // from this point can register hooks
$locator = new Locator();
$locator->app_base_url=dirname(Misc::current_page_url());

?>