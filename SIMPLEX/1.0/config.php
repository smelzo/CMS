<?php

    # Load config process
    
    # -> default.config.ini in simplex dir
    ConfigLoader::load_default_config();
    # -> config.ini in data dir
    ConfigLoader::load_custom_config();
    
    
    //$home_images_dir = "images/slides/home";
    //
    //$home_thumb_size = array(
    //                        'width'=>120,
    //                        'height'=>60,
    //                         );
    //$home_full_size = array(
    //                        'width'=>600,
    //                        'height'=>300,
    //                        );
    //
    //$thumb_size = array(
    //                        'width'=>120,
    //                        'height'=>60,
    //                         );
    //$full_size = array(
    //                        'width'=>600,
    //                        'height'=>300,
    //                        );
    //$news_thumb_size = array(
    //    'width'=>219,
    //     'height'=>124,
    //);
    //$langs = array(
    //    'it'=>'italiano'
    //    ,'en'=>'english'
    //)
    //directory con sotto directory con nomi gruppi
    //$thumb_src = "images/slides/sito";
    //$thumb_gallery = "images/slides/gallery";
?>