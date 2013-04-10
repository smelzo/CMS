<?php
//fb('EXTENSION AUTH LOADED');
//function remove_parse_templates(){
//    Simplex::remove_parser('parse_templates');
//}
class AuthClass {
    static $evalued = false;
    static function eval_user(){
        if(!self::$evalued) self::onLoad();
        $u= getGlobal('CURRENT_USER',null);
        return $u;
    }
    static function onLoad(){
        if(!self::$evalued) {
            setGlobal('CURRENT_USER',Users::eval_user());
            
            self::$evalued=true;
            hook_call('user_evalued');
        }
    }
    static function installLogin($doc=null){
      
        if(!$doc) {
            Simplex::add_parser(array('AuthClass',__FUNCTION__));
            return ;
        }
        $login_area = $doc->find('#login-area');
        if(!$login_area->length){
            $login_area = pq('<div id="login-area"></div>',$doc)->appendTo('body');
            //pq('<iframe src="about:blank" width="100%" height="80"></iframe>',$doc)->prependTo('body');
        }
        $buffer = Admin::eval_template('login');
        $node = pq($buffer)->appendTo($login_area);
        
        Admin::extract_scripts($login_area,$doc);
    }
}
function user(){
    return AuthClass::eval_user();
}
if(getGlobal('ADMIN_MODE')==true && defined('AUTH')) {
    hook_register('extensions_loaded',array('AuthClass','onLoad'));
    hook_register('after_standard_parsers',array('AuthClass','installLogin'));
}
?>