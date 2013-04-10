<?php
class Admin  {
    /**
     * ritorna buffer da include in simplex/admin/
    */
    static function eval_template($filename,$vars=null){
        if(!file_extension($filename)) $filename = "$filename.php";
        if(!dirname($filename) || dirname($filename)=='.') {
            $filename = APPLICATION_PATH._."admin"._.$filename;
        }
        
        $buffer = eval_include($filename,$vars);
        return $buffer;
    }
    /**
     * in un documento trova il primo elemento che ha un attributo corrispondente a un valore dato
    */
    static function element_attribute_find($doc,$selector,$attribute_name,$attribute_value){
        foreach($doc->find($selector) as $item){
            if(pq($item)->attr($attribute_name)==$attribute_value)  return $item;
        }
        return null;
    }
    //installa script e css che trova nel nodo nell'HEAD
    static function extract_scripts($node,$context){
        $refs = $node->find('link[rel=stylesheet],script[src]');
        foreach($refs as $ref){
            if(pq($ref)->is('link')){
                $href = pq($ref)->attr('href');
                if(!self::element_attribute_find($context,'head link[rel=stylesheet]','href',$href)) {
                    pq($ref)->appendTo($context->find('head'));
                }
                else pq($ref)->remove();
            }
            elseif(pq($ref)->is('script')){
                $src = pq($ref)->attr('src');
                $doc = $node->getDocument();
                if(!self::element_attribute_find($context,'head script[src]','src',$src)) {
                    pq($ref)->appendTo($context->find('head'));
                }
                else pq($ref)->remove();
            }
        }
    }
} 
?>