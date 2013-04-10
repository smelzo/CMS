<?php
    require dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.php";
    header("content-type:application/javascript");
    $config=ConfigLoader::get_all();
    
?>
var config = <?php echo json_encode($config)?>;
