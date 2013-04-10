<?php
if(!$ADMIN_MODE):
    function users_admin_hook($doc=null){    
        if(!$doc) {
            Simplex::add_parser(__FUNCTION__);
            return ;
        }
        if(! Users::eval_power(user(),Users::ADMINISTRATOR ) ) return ;
    //parser
    ob_start();
    ?>    
        <script type="text/javascript">
        add_onload(function (){
            var item = add_toolbar_item(function (){
                var url = makeRequest({'module':'users'},'admin.php');
                var el = $('<div class="users-editor-host"></div>')
                .appendTo('body');
                var win= el.kendoWindow({
                        iframe :true,
                        modal:true,
                        width: "940px",
                        height: "540px",
                        minHeight :540,
                        minWidth :940,
                        title: a_("Users Editor"),
                        actions: ["Maximize", "Close"],
                        content: url,
                        close:function (){
                            win.destroy();
                            window.location.reload();
                        }
                    }).data('kendoWindow');
                win.center();
                win.maximize();
                win.open();
            },a_('users'),'user').insertBefore('#admin-toolbar li.last');
        })
        </script>
    <?php
        $iniect = ob_get_clean();
        pq($iniect,$doc)->appendTo('head');
    }//users_admin_hook
    hook_register('after_standard_parsers','users_admin_hook');
else:
    //ADMIN MODE
endif;
?>
