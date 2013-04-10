<?php
class Fm_File extends Fm_Object {
	public $ctime        = 0;
	public $mtime        = 0;
	public $directory    = null;
	public $exists       = 0;
	public $mimetype     = '';
	public $name         = '';
	public $path         = '';
	public $fullpath         = '';
	public $url = '';
	public $size         = 0;
    public $extension = '';
	public $writable     = false;
	public $type;
    public $is_image = false;
    public $link_get = "";
	
    function __construct($path){
		global $root,$root_dir,$root_url;
        parent::__construct($path);
		$this->fullpath = $path;
        $this->path = dirname($path);
        $this->exists = is_file($path);
        if(!$this->exists) return ;
        $this->size = filesize($path);
        $this->mimetype = Misc::mime_content_type($path);
        $this->extension = file_extension($this->name);
        $this->ctime = filectime($path);
        $this->mtime = filemtime($path);
        $this->writable= is_writable($path);
		$this->is_image = in_array($this->extension,array('gif','jpg','jpeg','png'));
		//$this->url = $this->directory->url . '/' . urlencode( $this->name );
		$link_get = makeRequest(array('name'=>$this->name),true, true,'get.php');
		$url = parse_url($link_get);
		$link_get = $url['path'].'?'.$url['query'];
		$this->link_get = $link_get;
		if($this->is_image){
			Fm_Image::decore_file($this);
		}
    }
	
	function get_image(){
		if($this->is_image){
			return Fm_Image::NewFmImage($this);
		}
		return null;
	}
	protected function get_url($path){
		$url =  parent::get_url(dirname($path));
		$url .= '/' . urlencode( $this->name );
		return $url;
	}
	protected function relativize_path($path){
		return trim_slash(parent::relativize_path($path));
	}
    /**
	 * Function that returns the extension of the file.
	 * if a parameter is given, the extension of that parameters is returned
	 * returns false on error.
	 */
	function getExtension(){
        return file_extension($this->name);
	}
	static function NewFmFile($path){
		$f = new Fm_File($path);
		if($f->has_root && $f->exists) return $f;
		return null;
	}
	static function delete_file($file){
		if(is_string($file)){
			if(!is_file($file)){
				$file=json_decode($file,true);
				foreach($file as $f){
					if(is_file($f)) @unlink($f);
				}
				return 1;
			}
			if(is_file($file)) return unlink($file);
		}
		return 0;
	}
}
?>