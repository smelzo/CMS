<?php
function news_viewer_hook($doc=null){
    if(!$doc) {
        Simplex::add_parser(__FUNCTION__);
        return ;
    }
    $container = $doc->find('.news-container');
    $model = pq($container)->find('[data-repeater=news]');
    if(!$model->length) return ;
    
    $news = News::grabNews();
    foreach($news as $i=>$item) {
        
        $name = array_get($item,'name');
        $photo = array_get($item,'photo');
        $title = array_get($item,'title');
        $creation = array_get($item,'creation');
        $creation = date('d-m-Y',$creation);
        $description = array_get($item,'description');
        $clone = $model->clone();
        if($photo) {
          $clone->find('.news-image img')->attr('src',$photo);
        }
        else {
            $clone->find('.news-image')->remove();
        }
        $link = makeRequest(array('p'=>$name),true,true);
        
        $clone->find('a[href=#link]')->attr('href',$link);
        DomUtils::set_html_node($clone,$title,array('.news-title a','.news-title'));
        DomUtils::set_html_node($clone,$description,array('.news-abstract a','.news-abstract'));
        //$clone->find('.news-title a')->html($title);
        //$clone->find('.news-abstract a')->html($description);
        $clone->find('.news-date')->text($creation);
        $container->append($clone);
    }
    $model->remove();    
}

function news_check_page_name_path($name){
    if(preg_match('/news\\-(\\d+)/',$name)) {
        return PAGES_PATH._.'news';
    }
    return false;
}

hook_register ('check_page_name_path','news_check_page_name_path');
hook_register('after_standard_parsers','news_viewer_hook');
if(admin_mode()){
    //admin file
    if($admin_file=PathUtils::get_admin_extension_filename(__FILE__)) {
        require_once($admin_file);
    }    
}
#################################
# parse <* itemtype="#news"...>  
function strip_news($doc=null){
    if(!$doc) {
        Simplex::add_parser(__FUNCTION__);
        return ;
    }
    $title = $doc->find('[itemprop=title][lang=' . current_lang() . ']');
    if($title->length) {
       $e =  pq('<h1 class="title" lang="' . current_lang() . '"></h1>')->html($title->html());
       $doc->find('[itemtype=#news]')->before($e);
      
    }
    $doc->find('[itemtype=#news]')->remove();
    $details = $doc->find('details');
    if($details->length){
        pq('<div></div>')->html($details->html())->insertBefore($details);
        $details->remove();
    }
    
}    
hook_register('after_standard_parsers','strip_news');
#################################
//function news_filter($item){
//    $lang = array_get($item,'lang',"");
//    if($lang && $lang!=current_lang()){
//        return false;
//    }
//    return true;
//}
//if(function_exists('user')){
//    hook_register('filter_news','news_filter');
//}
?>