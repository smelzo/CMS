<?php
    $maxGalleryImages = 9;
    # Load config process
    
    # -> default.config.ini in simplex dir
    ConfigLoader::load_default_config();
    # -> config.ini in data dir
    ConfigLoader::load_custom_config();
    
   //carica il file di config.php eventualmente presente nella root del sito
   $custom_config_files = array(
        BASE_PATH.'/config.php',
        DATA_PATH.'/config.php',
        APPLICATION_PATH.'/config.php'
   );
   $config_included_files = 0;
   foreach($custom_config_files as $i=>$config_file){
       if(is_file($config_file)){
            include_once $config_file;
            $config_included_files++;
            break;
       }
   }
   if(!$config_included_files){
        echo "
        <div>
            <b>You Must define a config.php file in directories:</b>
            <ul>
                <li>".BASE_PATH."</li>
                <li>".DATA_PATH."</li>
                <li>".APPLICATION_PATH."</li>
            </ul>
        </div>";
        die;
   }
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