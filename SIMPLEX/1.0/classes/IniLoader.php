<?php
if(!function_exists('parse_ini_string')){
     function parse_ini_string($str, $ProcessSections=false){
         $lines  = explode("\n", $str);
         $return = Array();
         $inSect = false;
         foreach($lines as $line){
             $line = trim($line);
             if(!$line || $line[0] == "#" || $line[0] == ";")
                 continue;
             if($line[0] == "[" && $endIdx = strpos($line, "]")){
                 $inSect = substr($line, 1, $endIdx-1);
                 continue;
             }
             if(!strpos($line, '=')) // (We don't use "=== false" because value 0 is not valid as well)
                 continue;
             
            $tmp = explode("=", $line, 2);
             if($ProcessSections && $inSect)
                 $return[$inSect][trim($tmp[0])] = ltrim($tmp[1]);
             else
                 $return[trim($tmp[0])] = ltrim($tmp[1]);
         }
         return $return;
     }
 }
if(!function_exists('write_ini_file')){
    function write_ini_file($assoc_arr, $path, $has_sections=true) { 
       $content = ""; 
       if ($has_sections) { 
           foreach ($assoc_arr as $key=>$elem) {
                if(!is_array($elem)) {
                    if($elem==="") $content .= $key." = \n";
                    elseif($elem===null) $content .= $key." = null\n"; 
                    elseif(is_bool($elem)) $content .= $key." = " . ($elem?'true':'false') . "\n"; 
                    elseif(is_numeric($elem)) $content .= $key." = " . $elem . "\n";
                    else $content .= $key." = \"" . $elem . "\"\n"; 
                    continue;
                }
               $content .= "[".$key."]\n";
               foreach ($elem as $key2=>$elem2) {
                   if(is_array($elem2)) { 
                       for($i=0;$i<count($elem2);$i++) 
                       { 
                           $content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
                       } 
                   } 
                   else {
                        if($elem2==="") $content .= $key2." = \n";
                        elseif($elem2===null) $content .= $key2." = null\n"; 
                        elseif(is_bool($elem2)) $content .= $key2." = " . ($elem2?'true':'false') . "\n"; 
                        elseif(is_numeric($elem2)) $content .= $key2." = " . $elem2 . "\n";
                        else $content .= $key2." = \"" . $elem2 . "\"\n"; 
                   }
               } 
           } 
       } 
       else { 
           foreach ($assoc_arr as $key=>$elem) {
                   if(is_array($elem)) {
                       for($i=0;$i<count($elem);$i++) { 
                           $content .= $key."[] = \"".$elem[$i]."\"\n"; 
                       } 
                   } 
                   else {
                        if($elem==="") $content .= $key." = \n";
                        elseif($elem===null) $content .= $key." = null\n"; 
                        elseif(is_bool($elem)) $content .= $key." = " . ($elem?'true':'false') . "\n"; 
                        elseif(is_numeric($elem)) $content .= $key." = " . $elem . "\n";
                        else $content .= $key." = \"" . $elem . "\"\n"; 
                   }            
               
           } 
       } 
    
       if (!$handle = fopen($path, 'w')) { 
           return false; 
       } 
       if (!fwrite($handle, $content)) { 
           return false; 
       } 
       fclose($handle); 
       return true; 
    }
}

class IniLoader  extends PathUtils {
    static $parse_dir_path='';
    static $parse_dir_result=array();
    static $process_sections_pref=true;
    
    static function put($config,$file,$process_sections =true){
        if(file_exists($file)){
           $config = array_merge(self::parse_file($file,$process_sections),$config);
        }
        write_ini_file($config,$file,$process_sections);
    }
    
    static function parse_file($file,$process_sections =true){
        if(file_exists($file) &&
           $result = parse_ini_file( $file,$process_sections ) ) {
            return $result;
        }
        return array();
    }
    
    static function parse_dir_file(&$list,$file,$a){
        $process_sections = self::$process_sections_pref;
        $file = self::$parse_dir_path . DIRECTORY_SEPARATOR. $file;
        $extract = self::parse_file($file ,$process_sections);
        if($extract){
            self::$parse_dir_result = array_merge(self::$parse_dir_result,$extract);
        }
    }
    
    static function parse_dir ($dir,$process_sections =true,$pattern = "/.\\.ini$/i"){
        self::$parse_dir_result = array();
        self::$parse_dir_path = $dir;
        self::get_files($dir,$pattern,array('IniLoader','parse_dir_file'));
        return self::$parse_dir_result;
    }
    
    static function parse_string ($str_ini,$filesave="",$process_sections =true){
        $result = parse_ini_string($str_ini,$process_sections);
        self::$process_sections_pref = $process_sections;
        if($result && $filesave) {
            file_put_contents($str_ini,$filesave);
        }
        return $result ;
    }
} 
?>