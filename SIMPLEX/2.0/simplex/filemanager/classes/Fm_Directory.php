<?php

class Fm_Directory extends Fm_Object {
    public $directories=array();
    public $files=array();
    public $is_root = false;
    public $has_root = false;
    public $path = '';
    public $parents = array();
    public $url = '';
    public $link = '';
    public $exists= false;
    private $parsed = false;

    function __construct($path){
        global $root,$root_dir,$root_url;
        parent::__construct($path);
		
        if(!$this->has_root) return ;
		$this->exists = is_dir($path);
        $this->is_root = strtolower($root_dir)==strtolower($path);
        //$this->url = $root_url;
        //if(!$this->is_root ) {
        //    $this->url = $root_url . '/' . trim_slash($this->relative,false);
        //    $this->url = trim_slash($this->url);
        //}
		
        $this->link = makeRequest(array('folder'=>trim_slash($this->relative)),true,true);
    }
	
	static function NewFmDirectory ($path){
		$f = new Fm_Directory($path);
		if($f->has_root && $f->exists) return $f;
		return null;
	}
	
    function rooted($p){
        $r_pos = stripos($p,$this->root);
        return ($r_pos!==false && $r_pos===0);
    }
    
    function get_parents (){
        $parents = array();
        $p = $this->path;
        $c=1;
        while ($this->rooted($p)) {
            try {
                $d = self::NewFmDirectory($p);
				if($d) $parents[]= $d;
				else break;
            } catch(Exception $ex) {
                return ;
            }
            $p = dirname($p);
            $c++;    
        } ;
        $parents = array_reverse($parents);
        $this->parents=$parents;
        return $parents;
    }
    
    function add_dir($name){
        $dir = $this->path . DIRECTORY_SEPARATOR . $name;
        if(!is_dir($dir)) mkdir($dir);
        return self::NewFmDirectory($dir);
    }
    function move_file($from_path){
        copy($from_path,$this->path . DIRECTORY_SEPARATOR . basename($from_path));
    }
	
	private function rrmdir($dir) {
		if (is_dir($dir)) {
		  $objects = scandir($dir);
		  foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir.DIRECTORY_SEPARATOR.$object) == "dir")
					$this->rrmdir($dir.DIRECTORY_SEPARATOR.$object);
				else
					unlink($dir.DIRECTORY_SEPARATOR.$object);
			}
		  }
		  reset($objects);
		  rmdir($dir);
		}
	}
 
	function delete(){
		$this->rrmdir($this->path);
	}
	function delete_subdirs($list){
		if(is_array($list)) {
			foreach($list as $name){
				$path = $this->path.DIRECTORY_SEPARATOR.$name;
				if(is_dir($path)){
					$d = new Fm_Directory($path);
					$d->delete();
				}
			}
		}
	}
    function get_dirs($filter=''){

		$dir_iterator=new DirectoryIterator($this->path);
		$dirshash=array();
		$directories=array();
		foreach($dir_iterator as $file){
			if($file->isDot())continue;
			if(!$file->isDir())continue;
			$filename=$file->getFilename();
            if($filter && !preg_match($filter,$filename)) continue;
            $path = $this->path.DIRECTORY_SEPARATOR.$filename;
			if(is_dir($path)){
				$d = self::NewFmDirectory($path);
				if($d) $directories[]=$d;
			}
		}
        $this->directories = $directories;
		return $directories;
	}
    function eval_filters($f){
		$max_width = querystring('fmaxw',0,'number');
		$min_width = querystring('fminw',0,'number');
		$max_height = querystring('fmaxh',0,'number');
		$min_height = querystring('fminh',0,'number');
		if($f->is_image) {
			if($max_width && $f->width>$max_width) return false;
			if($max_height && $f->height>$max_height) return false;
			if($min_width && $f->width<$min_width) return false;
			if($min_height && $f->height<$min_height) return false;
		}
		return true;
	}
    function get_files($filter=''){
		global $ACCEPT_TYPES;
		if(!$ACCEPT_TYPES) $ACCEPT_TYPES='.+';
		$dir_iterator=new DirectoryIterator($this->path);
		$dirshash=array();
		$files=array();
		foreach($dir_iterator as $file){
			if($file->isDot())continue;
			if($file->isDir())continue;
			$filename=$file->getFilename();
            if($filter && !preg_match($filter,$filename)) continue;
			$pattern = '/\\.('. $ACCEPT_TYPES . ')$/i';
            if(!preg_match($pattern,$filename)) continue;
            $path = $this->path.DIRECTORY_SEPARATOR.$filename;
			if(!is_dir($path)){
				
				$f= Fm_File::NewFmFile($path);
				
				if($f && $this->eval_filters($f)) $files[]=$f;
			}
		}
        $this->files = $files;
		return $files;
	}
    
    
}
?>