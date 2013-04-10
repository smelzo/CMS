<?php
    //@param phpQueryObject $doc
    function parse_templates($doc){
        $parser = new SelectorParser($doc,'template,content','template_eval');
        $parser->parse();
    }
    
    
    Simplex::add_parser('parse_templates');
    #################################
    
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
    
    function parse_link_script($doc){
        $doc->find('body link[rel=stylesheet],body script[src]')->appendTo('head');
        
    }    
    Simplex::add_parser('parse_link_script');
    #################################
    

    
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