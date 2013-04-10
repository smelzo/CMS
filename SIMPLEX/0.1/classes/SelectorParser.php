<?php
class SelectorParser  {
    private $doc,$selector,$callback;
    private $iter=1;
    function __construct($doc,$selector,$callback){
        $this->doc=$doc;
        $this->selector=$selector;
        $this->callback=$callback;
    }
    
    function extract_vars_from_node($nd){
        $vars=array();
        foreach($nd->attributes as $attribute)
                $vars[$attribute->name]= $attribute->value;
        return $vars;
    }
    
    function eval_include ($filename,$vars=null){
        if(!is_array($vars)) $vars = array();
        $vars = array_merge($GLOBALS,$vars);
        extract($vars,EXTR_SKIP);
        if(!is_file($filename)) return "";
        ob_start();
        require $filename;
        $buffer = ob_get_clean();
        return $buffer;
    }
    
    function parse (){
        //@var phpQuery
        $nodeset = $this->doc->find($this->selector);
        $this->iter+=1;
        if(!$nodeset->length||$this->iter>10){
             return ;
        }
        foreach($nodeset as $item){
            $vars = $this->extract_vars_from_node($item);
            $s = call_user_func($this->callback,$vars,$item);
            pq($item)->replaceWith($s);
        }
        $this->parse();
    }
    
} 
?>