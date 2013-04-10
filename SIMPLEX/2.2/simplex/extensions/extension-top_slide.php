<?php
function top_slide_parse($doc=null){
    if(!$doc) {
        Simplex::add_parser(__FUNCTION__);
        return ;
    }
    $container = $doc->find('.top_slide-container');
    $model = pq($container)->find('[data-repeater=top_slide]');
    if(!$model->length) return ;
    if(($meta_content = $doc->find('meta[name=slides.config.href][content]')) && $meta_content->length) {
        $href = BASE_PATH.$meta_content->attr('content');
        if(is_file($href)){
            extract($GLOBALS,EXTR_REFS);
            ob_start();
            require($href);
            $buffer = ob_get_clean();
            $model->replaceWith($buffer);
        }
        return ;
    }
    $slide_images = SlidesHandler::get_slide_images($doc);
    foreach($slide_images as $i=>$filename){
        $image = 'images/'. $filename;
        $clone = $model->clone();
        $clone->attr('data-background',$image);
        if(!$i) {
          $clone->css(array('background-image'=>"url($image)"));
        }
        $container->append($clone);
    }
    $model->remove();
}

hook_register('after_standard_parsers','top_slide_parse');

?>