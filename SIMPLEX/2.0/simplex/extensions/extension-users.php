<?php
if(admin_mode()){
    //admin file
    if($admin_file=PathUtils::get_admin_extension_filename(__FILE__)) {
        require_once($admin_file);
    }    
}
?>