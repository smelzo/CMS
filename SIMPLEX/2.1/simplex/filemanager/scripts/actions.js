//BUILD CONTEXT MENU ITEM
var get_menu_item = function (name,options){
    var menu_item = {};
    menu_item[name]=options;
    return menu_item;
}

function pick_file(file){
    //invio file ad applicazione host
    var fx = null,host=null;
    if(window.parent && $.isFunction(window.parent[HOST_PICK_FILE_FUNCTION_NAME])){
        fx = window.parent[HOST_PICK_FILE_FUNCTION_NAME];
        host = window.parent;
    }
    else {
        if(window.top && $.isFunction(window.top[HOST_PICK_FILE_FUNCTION_NAME])){
            fx = window.parent[HOST_PICK_FILE_FUNCTION_NAME];
            host = window.parent;
        }
    }
    if(fx){
        host[HOST_PICK_FILE_FUNCTION_NAME].call(host,file);
        if($.isFunction(host['WHEN_FILE_PICKED'])){
            host['WHEN_FILE_PICKED'].call(host,file);
        }
        return ;
    }
    alert(HOST_PICK_FILE_FUNCTION_NAME + ' function not defined in host');
}

var actions = {
    delete_folders:function (){
        var selected = $('#directory-list').listController('selected',true);
        console.log(selected);
        if(!selected || !selected.length) {
            alert(_('none_selected'));
            return ;
        }
        if(confirm(_('confirm_delete_folders'))){
            $.serverPost('delete_folders',{selected:$.encode(selected)},function (){
                window.location.reload();
            })
        }
    }
    , add_folder : function (){
        var win = $('#add_folder_dialog');
        var title = win.attr('title') || window['add_folder_title'];
        window['add_folder_title'] = title;
        var send_add_folder = function (form){
            var name = $(form).find('input[name=name]').val();
            if(name && name.match(/^[\w.-]+$/g)){
                $.serverPost('add_folder',{name:name},function (){
                    window.location.reload();
                })
                console.log('send_add_folder');
            }
            else {
                alert(_('invalid_name'));
            }
        }
        win.dialog({
            height: 210,
            minHeight: 210,
            width: 482,
            minWidth: 482,
            modal: true,
            title:title,
            open : function (){
                var f = win.find('form')
                .unbind( 'submit' )
                .submit(function (evt){
                    evt.preventDefault();
                    send_add_folder(f);
                });
                f.find('button[type=submit]').click(function (){
                    f.submit();
                })
            }
        });
    },
   
};

// activation
$(function (){
    $('.dialog-close').each(function (){
        var self=this;
        $(this).click(function (){
            $(self).parents('.ui-dialog-content').dialog('close');
        })
    })
    //$('[data-action]').each(function (){
    //    var action = $(this).attr('data-action');
    //    var bind = $(this).attr('data-action-bind') || 'click';
    //    $(this).bind(bind , function (event){
    //        event.preventDefault();
    //        actions[action].call (this);
    //    })
    //})
})