<?php
//$cms_engine = new CMS_Engine();
//FUNCTIONS
function fm_change_path(&$node){
    $server_url = Misc::server_url() ;
    if(pq($node)->is('script')){
        $src = pq($node)->attr('src');
        if(strpos($src,'http')===false){
            pq($node)->attr('src',"$server_url/$src");
        }
    }
    if(pq($node)->is('link')){
        $href = pq($node)->attr('href');
        if(strpos($href,'http')===false){
            pq($node)->attr('href',"$server_url/$href");
        }
    }
}
function fm_relativize_file($file){
    extract($GLOBALS);
    ob_start();
    require($file);
    $c = ob_get_clean();
    $doc = phpQuery::newDocumentHTML($c,'UTF-8');
    $doc->find('script[src],link[rel=stylesheet]')->each('fm_change_path');
    return $doc->htmlOuter();
    
}

function fix_slash($path){
	if($path) {
		$path = str_replace("/",DIRECTORY_SEPARATOR,$path);
	}
	return $path;
}
function double_slash($path){
	if($path) {
		$path = str_replace("\\","\\\\",$path);
	}
	return $path;
}
function trim_slash($path,$end=true){
    if($end){
        $last_char = substr($path,-1,1);
        
        if($last_char==DIRECTORY_SEPARATOR || $last_char=='/'){
            $path = substr($path,0,-1);
        }
    }
    else {
        $first_char = substr($path,0,1);
        if($first_char==DIRECTORY_SEPARATOR || $first_char=='/'){
            $path = substr($path,1);
        }
    }
    return $path;
}
function get_filemanager_resources(){
	global $FM_DIR;
	include_once "$FM_DIR/filemanager.resources.php";
	return $GLOBALS['FILEMANAGER_RESOURCES'];
	//return get_resources('filemanager.resources','FILEMANAGER_RESOURCES','ADMIN_LANG');
}
function fm_($name,$def=""){
    if(!$def) $def = $name;
    $AR = get_filemanager_resources();
    return array_get($AR,$name,$def);
}
function current_folder(){
    global $folder_dir;
    $d = Fm_Directory::NewFmDirectory($folder_dir);
	if(!$d) return null;
    $d->get_dirs();
    $d->get_parents();
    $d->get_files();
    return $d;
}
function add_folder($name){
	global $folder_dir;
	//@var Fm_Directory
	$d = Fm_Directory::NewFmDirectory($folder_dir);
	if(!$d) return null;
	return $d->add_dir($name);
}
function delete_folders($selected){
	global $folder_dir;
	$d = Fm_Directory::NewFmDirectory($folder_dir);
	if(!$d) return ;
	if(is_string($selected)) $selected=json_decode($selected,true);
	if($selected && is_array($selected)){
		$d->delete_subdirs($selected);
	}
}
function get_icons_list(){
	$path = dirname(__FILE__).DIRECTORY_SEPARATOR."icons".DIRECTORY_SEPARATOR."64";
	$dir_iterator=new DirectoryIterator($path);
	$ret = array();
	foreach($dir_iterator as $file){
		if($file->isDot() || $file->isDir())continue;
		$filename=$file->getFilename();
		if(file_extension($filename)!='png') continue;
		$ret[]=basename_without_extension($filename);
	}
	return $ret;
}

//ROOT 
$root = querystring(array('root','mode','r'),'images');
$root_dir = mkdir_check($SITE_ROOT. DIRECTORY_SEPARATOR .$root);
$root_url = Misc::server_url() . "/$root";

//start folder
$folder = trim_slash(querystring('folder',''),false);
$folder_dir = trim_slash($root_dir. DIRECTORY_SEPARATOR .$folder);
$folder_url = "$root_url/$folder";


?>