<?php
    /**
     * @param phpQueryObject $doc
    */  
    function gallery_viewer_parse($doc){
        $galleries = $doc->find('[data-extension=gallery]');
        foreach($galleries as $gallery){
            $thumb_width = pq($gallery)->attr('data-thumb-width');
            $thumb_height = pq($gallery)->attr('data-thumb-height');
            $full_width = pq($gallery)->attr('data-full-width');
            $full_height = pq($gallery)->attr('data-full-height');
            if(!is_numeric($thumb_width)) $thumb_width = 150;
            if(!is_numeric($thumb_height)) $thumb_height = 150;
            if(!is_numeric($full_width)) $full_width = 800;
            if(!is_numeric($full_height)) $full_height = 600;
            foreach(pq($gallery)->find('a.gallery-item') as $item){
                //see if item contains yet image
                $item = pq($item);
                if($item->find('>img')->length) continue;
                $title = $item->attr('title');
                $href = $item->attr('href');
                $original_filename = PathUtils::trim_slash($href,false);
                $original_filename = BASE_PATH._. PathUtils::fix_slash($original_filename);
                
                if(!is_file($original_filename)) {
                    $item->remove();
                    continue;
                }
                Gallery::checkFileSize($original_filename,$full_width,$full_height);
                $thumb_filename = Gallery::get_thumb_path($original_filename,$thumb_width,$thumb_height);
                if(!is_file($thumb_filename)){
                    Gallery::createThumb($original_filename,$thumb_width,$thumb_height);
                }
                pq('<img/>')->attr('src',dirname($href)."/".basename($thumb_filename))->appendTo($item);
            }
        }
    }
    function gallery_viewer_hook(){
        Simplex::add_parser('gallery_viewer_parse');
    }
    hook_register('after_standard_parsers','gallery_viewer_hook');
    
?>