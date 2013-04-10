<?php
if(!$ADMIN_MODE):
    function news_admin_hook($doc=null){    
        if(!$doc) {
            Simplex::add_parser(__FUNCTION__);
            return ;
        }
    //parser
    ob_start();
    ?>    
        <script type="text/javascript">
        add_onload(function (){
            var item = add_toolbar_item(function (){
                var url = makeRequest({'module':'news'},'admin.php');
                var el = $('<div class="news-editor-host"></div>')
                .appendTo('body');
                var win= el.adminWindow({
                        iframe :true,
                        modal:true,
                        width: "940px",
                        height: "540px",
                        minHeight :540,
                        minWidth :940,
                        title: "News Editor",
                        actions: ["Maximize", "Close"],
                        content: url,
                        close:function (){
                            win.destroy();
                           // window.location.reload();
                        }
                    }).data('kendoWindow');
                win.center();
                win.maximize();
                win.open();
                window.close_news_window = function (){
                    win.close();
                }
            },'News','rss').insertBefore('#admin-toolbar li.last');
        })
        </script>
    <?php
        $iniect = ob_get_clean();
        pq($iniect,$doc)->appendTo('head');
    }//news_admin_hook
    hook_register('after_standard_parsers','news_admin_hook');
else:
    //ADMIN MODE
endif;
?>
