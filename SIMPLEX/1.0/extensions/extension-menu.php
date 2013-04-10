<?php
    if(!defined('MENU_PATH')) define("MENU_PATH",BASE_PATH . _ . 'menus');
    
    function menu_placeholder_replace($doc=null){
        if(!$doc){
            Simplex::add_parser(__FUNCTION__);
            return ;
        }
        $menu_elements = $doc->find('menu');
        foreach($menu_elements as $e){
            $name = pq($e)->attr('name');
            $active = pq($e)->attr('active');
            $html = file_get_contents(MENU_PATH._."/$name.php");
            $ul = phpQuery::newDocumentHTML($html);
            $ul->find('.'.$active)->addClass('active');
            pq($e)->replaceWith($ul->htmlOuter());
        }
    }
    hook_register('after_standard_parsers','menu_placeholder_replace');
?>