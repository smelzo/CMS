<?php
class ResourceHandler  {
    private $resources = array();
    private $global_varname = "resources";

    function __construct($lang="",$filebasename="resources",$global_varname="resources",$search_paths = array()){
        $VN = $global_varname;
        $this->global_varname=$VN;
        $filenames = array();
        $filenames[] = $filebasename;
        if($lang) $filenames[] = "$filebasename.$lang";
        if(!$search_paths) $search_paths = array(DATA_PATH , APPLICATION_PATH);
        //load resource files
        //resource files put definition into a $resources variable
        foreach($filenames as $name){
            foreach($search_paths as $path){
                $file_path = $path . _ . "$name.php";
                if(is_file($file_path)){
                    include_once($file_path);
                }
            }
        }
        //retrieve $resources  variable
        if(isset($resources) && $resources){
            $this->resources = $resources;
            $GLOBALS[$VN] = $resources;
        }
    }
    
    function get($name){
        return array_get($this->resources,$name,$name);
    }
    
    function js_iniect($doc){
        $head = $doc->find('head');
        $content = self::js_string();
        pq($head)->append($content);
    }
    function js_string(){
        return  '<script type="text/javascript">'. "\n" .
           $this->global_varname . '=' . json_encode($this->resources) . ";\n" .
        '</script>';
    }
} 
?>