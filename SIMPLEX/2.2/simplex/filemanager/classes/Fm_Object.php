<?php
class Fm_Object  {
    public $name = '';
    public $relative = '';
    public $root = '';
    public $path = '';
    
    function __construct($path){
        global $root,$root_dir,$root_url;
        $this->path = $path = trim_slash($path);
        $this->name = basename($path);
        $this->relative = $this->relativize_path($path);
        $this->has_root = $this->contains_root($path);
        $this->root = $root_dir;
        $this->url = $this->get_url($path);
    }
    protected function contains_root($path){
		global $root,$root_dir,$root_url;
		$r_pos = stripos($path,$root_dir);
        return ($r_pos!==false && $r_pos===0);
	}
	protected function relativize_path($path){
		global $root,$root_dir,$root_url;
		return Locator::slash_fix(trim_slash(substr($path,strlen($root_dir)),false));
	}
    
	protected function get_url($path){
		global $root,$root_dir,$root_url;
		$relative = $this->relativize_path($path);
		$url = $root_url;
        $is_root = strtolower($root_dir)==strtolower($path);
        if(!$is_root ) {
            $url = $root_url . '/' . trim_slash($relative,false);
            $url = trim_slash($url);
        }
		return $url;
	}
} 
?>