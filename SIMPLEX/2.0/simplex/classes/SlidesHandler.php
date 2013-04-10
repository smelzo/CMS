<?php
class SlidesHandler extends PathUtils {
    /**
     * get_slide_images
    */
    public static function get_slide_images($doc){
       
        $pagename = Simplex::get_pagename();
        $pattern = '/'. $pagename .'\\-slide\\-(\\d+)\\.jpe?g$/i';
        $page_set = self::get_files(BASE_PATH. DIRECTORY_SEPARATOR ."images",$pattern);

        if(!is_array($page_set)) $page_set = array();
        if($doc && $custom = self::parse_doc($doc)) {
            $page_set = array_merge($page_set,$custom);
        }
        if(!$page_set) {
            //general set
            $pattern = '/top\\-slide\\-(\\d+)\\.jpe?g$/i';
            $page_set = self::get_files(BASE_PATH. DIRECTORY_SEPARATOR ."images",$pattern);
        }        
        return $page_set;
    }
    /**
     * docs
    */
    static function parse_doc($doc){
        $top = $doc->find('top');
        if(!$top->length) return null;
        $base =   pq($top)->attr('base');
        $base = self::trim_slash($base,false);
        $base = self::trim_slash($base,true);
        $attr_base =$base;
        $base = BASE_PATH. DIRECTORY_SEPARATOR ."images". DIRECTORY_SEPARATOR .$base;
        $base = self::fix_slash($base);
        $items = pq($top)->find('item');
        $result = array();
        if($items->length){
            foreach($items as $item){
                $src = pq($item)->text();
                if(!$src || !($src=trim($src))) continue;
                $f = $base. DIRECTORY_SEPARATOR .$src;
                if(!is_file($f)) continue;
                $f = new SplFileInfo($f);
                $name = $f->getFilename();
                $result[]=($attr_base?"$attr_base/":"").$name;
            }
        }
        $top->remove();
        return $result;
    }
}
?>