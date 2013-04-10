<?php
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
            var insert_buttons = function (){
                var list = [
                    {type:'news',cls:'rss',name:"News"}
                    ,{type:'rassegna',cls:'file',name:"Rassegne stampa"}
                    ,{type:'vini',cls:'glass',name:"Schede Prodotti"}
                    //,{type:'slides',cls:'picture',name:"Sfondi"}
                ];
                $.each(list,function (i,item){
                    var url = makeRequest({'module':'news','type':item.type,typeName:item.name},'admin.php');
                    add_toolbar_item(function (){
                        window.location.href = url;
                    },item.name,item.cls).insertBefore('#admin-toolbar li.last');
                })
            }
            insert_buttons();
        })
        </script>
    <?php
        $iniect = ob_get_clean();
        pq($iniect,$doc)->appendTo('head');
    }//news_admin_hook
if(!$ADMIN_MODE):

    hook_register('after_standard_parsers','news_admin_hook');
else:
    //ADMIN MODE
    hook_register('after_standard_parsers','news_admin_hook');
endif;
?>
