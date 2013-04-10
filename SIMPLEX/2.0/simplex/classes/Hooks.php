<?php
define('HOOK_STOP_RESULT',-1);
class Hooks  {
    private $hooks = array();
    private $called_hooks = array();
    function register($hook_name,$callback){
        if(!isset($this->hooks[$hook_name])){
            $this->hooks[$hook_name] = array();
        }
        $this->hooks[$hook_name][]=$callback;
    }
    //register only one callback
    //newer override previous
    function register_one($hook_name,$callback){
        $this->hooks[$hook_name]=array($callback);
    }
    function call($hook_name){
        if(!in_array($hook_name,$this->called_hooks)){
            $this->called_hooks[]=$hook_name;
        }
        if(isset($this->hooks[$hook_name])){
            $args = func_get_args();
            array_shift($args);
            $_args = array();
            foreach($args as $item){
                $_args[] = $item;
            }
            $hook = $this->hooks[$hook_name];
            $cbk_result = null;
            foreach($hook as $callback){
                $cbk_result =  call_user_func_array($callback,$_args);
                if($cbk_result===HOOK_STOP_RESULT) break;
            }
            return $cbk_result;
        }
    }

    function get_called_hooks(){
        return $this->called_hooks;
    }
}
/**
 * @param $name
 * + mixed param array
*/
function hook_call($name){
    global $HOOKS ;
    $args = func_get_args();
    if($HOOKS)
        return call_user_func_array(array($HOOKS,'call'),$args);
}
function hook_register($name,$callback){
    global $HOOKS ;
    if($HOOKS)
        $HOOKS->register($name,$callback);
}
function hook_register_one($name,$callback){
    global $HOOKS ;
    if($HOOKS)
        $HOOKS->register_one($name,$callback);
}
?>