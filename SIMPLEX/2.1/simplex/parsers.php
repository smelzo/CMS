<?php
    //@param phpQueryObject $doc
    function parse_templates($doc){
        $parser = new SelectorParser($doc,'template,content','template_eval');
        $parser->parse();
    }
    Simplex::add_parser('parse_templates');
    #################################

    function parse_call($doc){
        $nodeset = $doc->find('call');
        foreach($nodeset as $call){
            $callback = pq($call)->attr('function');
            $arguments= pq($call)->attr('arguments');
            if(!$arguments) $arguments=array();
            else $arguments = explode(",",$arguments);
            $s = call_user_func_array($callback,$arguments);
            pq($call)->replaceWith($s);
        }
    }
    Simplex::add_parser('parse_call');
    #################################
    # parse <meta ...>  
    function parse_meta($doc){
        $doc->find('body meta')->insertAfter('title');
        $doc->find('body')->addClass(Simplex::fix_name(Simplex::get_pagename()));
        $title = array();
        $title[] = $doc->find('head title')->text();
        $page_title = $doc->find('body .page h1');
        if($page_title->length){
            $title[] = strip_tags(pq($page_title[0])->html());
        }
        $title = implode(' &bull; ',$title);
        $doc->find('head title')->html($title);
    }
    Simplex::add_parser('parse_meta');
    
    #################################
    # parse <link rel="stylesheet" type="text/css" href=""/> | <script type="text/javascript">
    function parse_link_script($doc){
        if(defined('KEEP_LINK_SCRIPT')) return ;
        $doc->find('body link[rel=stylesheet],body script[src]')->appendTo('head');
    }    
    Simplex::add_parser('parse_link_script');
    #################################
    /**
     * @param phpQueryObject $doc
    */
    function parse_parent_classes($doc){
        $ptype = array('html','body');
        foreach($ptype as $t){
            $nodes = $doc->find('[data-'.$t.'-class]');
            foreach($nodes as $node){
                $classes = pq($node)->attr('data-'.$t.'-class');
                $doc->find($t)->addClass($classes);
            }
        }
    }
    Simplex::add_parser('parse_parent_classes');
    
    #################################
    # parse <a href="page://...">  
    function page_link_replace($a,$i=0){
        $href = pq($a)->attr('href');
        if($o=hook_call('parse_page_link',substr($href,7))){
            
        }
        else {
            $o = array(
                'p'=>substr($href,7)
            );
        }
        pq($a)->attr('href',makeRequest($o,true,true));
    }
    
    function parse_page_link($doc){
        $a = pq('a[href^=page://]',$doc)->each('page_link_replace');
    }    
    Simplex::add_parser('parse_page_link');
    #################################
    
?>