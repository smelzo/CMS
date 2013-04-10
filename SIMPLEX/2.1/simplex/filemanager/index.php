<?php
/**
 * PARAMETERS IN QUERYSTRING
 * types|t = images|office|web|*|all default * file types handled
 * ctypes|ct = custom file types handled list i.e. 'docx?|xlsx?|pptx?'
 * root|r|mode = root folder default 'files' -> <site path>/files
 * folder = relative to root default '' -> <site path>/<folder>
 * fx = window.opener or window.top js function name for receive file choosed default 'pick_file'
 * ---------------------------------------
 * upload = if 1 then upload mode is active
 * if upload mode activated :
 * mw|max-width = image max width
 * mh|max-height= image max height
 * hue   |
 * sat   |----- image parameters for upload
 * light |
*/
require "config.php";
$folder = current_folder();
if(querystring('upload')==1){
    require 'upload.php';
    die;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<!-- start common         -->
        <?php require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'insert.head.php')?>
<!-- end common         -->
<script type="text/javascript">
    var FM_RESOURCES = <?php echo json_encode(get_filemanager_resources())?>;
    var _GET = <?php echo json_encode($_GET)?>;
    //console.log(FM_RESOURCES);
    function _(name,def){
        if(!def)def= name;
        if(FM_RESOURCES[name]) return FM_RESOURCES[name];
        console.log(name);
        return def;
    }
    function _get(name,def){
        if(!def)def= name;
        if(_GET[name]) return _GET[name];
        return def;
    }
    //var folder = <?php /*echo json_encode($folder)*/?>;
</script>
    <link rel="stylesheet" type="text/css" href="ui.css"/>
    <link rel="stylesheet" type="text/css" href="scripts/contextmenu/jquery.contextmenu.css"/>
    <script type="text/javascript" src="scripts/actions.js"></script>
    <script type="text/javascript" src="scripts/jquery.contextmenu.js"></script>
    <style type="text/css" media="all">
        #vertical .k-splitbar-static-vertical {
            display: none;
        }
    </style>
    </head>
    <body>
        <div class="fixed-full">
            <div id="vertical" style="height: 100%; width: 100%;border:none">
                <div id="top-pane">
                    <div class="pane-content">
                        <?php include 'breadbrumb.php'?>
                    </div>
                </div>
                <div id="middle-pane">
                     <div id="horizontal" style="height: 100%; width: 100%;border:none">
                        <div id="left-pane">
                            <div class="pane-content">
                                <?php include 'directories.php'?>
                            </div>  
                        </div>
                        <div id="center-pane">
                                <div id="files-pane-content" style="height: 100%; width: 100%;border:none">
                                    <?php include 'files.php'?>
                                </div>
                            
                        </div>
                     </div>
                    
                </div>
            </div>
        </div>
        <?php include 'folder_dialog.php'?>
        <div id="file-panel" class="row-fluid hide" style="width:400px;font-size:0;padding:10px">
            <div class="inline-block" style="width:10%;font-size: 12px; vertical-align:top;padding-top:5px">
                <i class="icon-share icon-green" style="font-size:32px"></i>
            </div>
            <div class="inline-block" style="width:20%;font-size: 12px; vertical-align:top;padding-top:3px">
                <?php echo fm_('add_file')?>
            </div>
            <div class="inline-block" style="margin-left:0%;width:70%;font-size: 12px;">
                <input id="fileupload" type="file" name="files[]" data-url="<?php echo makeRequest(array('upload'=>1),true,true) ?>" style="width:150px">
            </div>
            <div id="progress" class="progress hide">
                <div class="bar" style="width: 0%;"></div>
            </div>
        </div>    
        
        <script type="text/javascript">
            $(function (){
                $("#vertical").kendoSplitter({
                     orientation: "vertical",
                     panes: [
                            { collapsible: false ,resizable :false, size: "30px"},
                            { collapsible: false}
                        ]
                });
                $("#horizontal").kendoSplitter({
                     panes: [
                            { collapsible: true , size: "200px"},
                            { collapsible: false}
                        ]
                });

            //$('#files-pane-content').kendoSplitter({
            //         orientation: "vertical",
            //         panes: [
            //                { collapsible: false ,resizable :false, size: "30px"},
            //                { collapsible: false}
            //            ]
            //    });
              $('.group-buttons').each(function (){
                var group = $(this);
                $(this).find('a').click(function (){
                        group.find('a').removeClass('active');
                        $(this).addClass('active');
                    })
                });
                
                $('.subnavbar').each(function (){
                    var subnavbar=$(this);
                    var H = $(this).outerHeight();
                    var p = $(this).parents('.k-scrollable');
                    var paddingTop = parseInt( $(this).parent().css('paddingTop'));
                    //$(this).parent().css('paddingTop', (paddingTop + H) + 'px');
                    var wrapper = subnavbar.wrap('<div></div>').parent().addClass('subnavbar-absolute');
                    //subnavbar.addClass('subnavbar-absolute');
                    $('<div></div>').css('height',H+'px').insertAfter(wrapper);
                    $(p).scroll(function (){
                        wrapper.css('top',$(this).scrollTop() + 'px')
                    })
                    
                })
                
                $('[rel=tooltip]').tooltip();
            });
        </script>
    </body>
</html>