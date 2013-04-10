<?php
class DomUtils  extends Simplex{
    static function set_html_node($node,$html,$selectors = array()){
        foreach($selectors as $target){
            $t = $node->find($target);
            if($t->length){
                $t->html($html);
                return ;
            }
        }
    }
} 
?>