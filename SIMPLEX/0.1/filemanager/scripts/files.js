var upload_handler = function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result, function (index, file) {
                if(file['error']) {
                    alert(_('error:'+ file['error']));
                }
                else {
                    var li = $('<li class="list-item file-item not-load"></li>').prependTo('#files-list');
                    file_setup(li,file.fileObject);
                    process_queue();
                    $('#files-list .no-files-item').hide();
                    $('#file-panel').hide();
                }
            });
            $('#progress').hide();
        }
        ,fail : function (e, data) {
            $('#progress').hide();
        }
        ,progressall: function (e, data) {
            $('#progress').show();
            var _progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar').css(
                'width',
                _progress + '%'
            );
        }
    });
};

var image_queue = [];
function image_add_queue(link_get , el){
    image_queue.push({link_get:link_get,el:el});
}
function file_filter(){
    var search = $('#file-filter').val();
    if(!search) search = search.toLowerCase();
    var file = null;
    $('#files-list .file-item').each(function (){
        if(!search) {
            $(this).show();
            return ;
        }
        file = $(this).data('file');
        $(this).toggle(file.name.toLowerCase().indexOf(search)!=-1);
    });
}

function delete_file(file,selection){
	
    if(!confirm(_('confirm_delete'))) return ;
    $.serverPost('Fm_File.delete_file',{file:$.encode(file)},function (ret){
        if(ret) {
			window.location.reload();
			return ;
            $.each(selection,function (){
                $(this).remove();
            });
            if(!$('#files-list .file-item').length) ('#files-list .no-files-item').show();
        }
        else alert (_('error'));
    });
}
function file_panel_toggle(){
    $('#file_panel_toggler').click(function (event){
        event.preventDefault();
        event.stopPropagation();
        var offset = $(this).offset();
        offset.top += $(this).outerHeight();
        $('#file-panel').css({top : offset.top + 'px',left:offset.left + 'px'})
        $('#file-panel').toggle();
    });
    $('#file-panel').click(function (event){
        event.stopPropagation();
    });
    $(document).click(function (){
        $('#file-panel').hide();
    });
    
}
function preview_image(file){
    var div = $('<div style="width:100%;height:100%;position:relative;background:#eee none no-repeat center"></div>').appendTo('body');
    var link_get = file.link_get + '&w=640&h=500';
    var img = new Image();
    img.src = link_get;
    img.onload= function (){
        div.css({backgroundImage:'url(' + link_get + ')'});
        div.dialog({
            width:640
            ,height:500
            ,title:false
            ,modal:true
            ,close:function (){
                div.remove();
            }
            ,open:function (){
                $('.ui-widget-overlay').one('click',function (){
                    div.dialog('close');
                })
            }
        })
    };
    img.onerror = function (){
        process_queue();
    }
    
}
function process_queue(){
    if(!image_queue.length) return ;
    var queueItem =  image_queue.shift();
    var img = new Image();
    img.onload= function (){
        $(queueItem.el).find('.placeholder').css({backgroundImage:'url(' + queueItem.link_get + ')'});
        process_queue();
    };
    img.onerror = function (){
        process_queue();
    }
    img.src = queueItem.link_get;
}

var set_info_panel = function (file,li){
    if(view_pref=='large'){
        var plc = $('<div class="placeholder"></div>').appendTo(li);
        var dida = $('<div class="dida"></div>').appendTo(li);
        var html = '<table cellpadding="0" cellspacing="0" width="">\
            <caption>\
                <b>' + file.name + '</b>\
            </caption>\
            <tr valign="top">\
                <td>'+ _('size') +'</td>\
                <td>' + size_format(file.size) + '</td>\
            </tr>\
            <tr valign="top">\
                <td>'+ _('created') +'</td>\
                <td>' + utime_format(file.ctime)  + '</td>\
            </tr>\
            <tr valign="top">\
                <td>'+ _('modified') +'</td>\
                <td>' + utime_format(file.mtime)  + '</td>\
            </tr>'
        if(file['width']){
             html +='<tr valign="top">\
                <td>'+ _('dimensions') +'</td>\
                <td>' + file.width + 'px/' + file.height+ 'px'  + '</td>\
            </tr>';
        }
        html +='</table>';
        dida.html(html);
    }
    else {
		if(!$(li).parents('ul:first').find('li.header:first').length){
			//header
			var lih = $('<li class="header"></li>').prependTo($(li).parents('ul:first'));
			var row = $('<div class="row-fluid"></div>').appendTo(lih);
			var col = $('<div class="span6 file-name"></div>').html( _('name')).appendTo(row);
			col = $('<div class="span1 file-size"></div>').html(_('size')).appendTo(row);
			//col = $('<div class="span1 file-ctime"></div>').html(utime_format(file.ctime)).appendTo(row);
			col = $('<div class="span3 file-mtime"></div>').html(_('created')).appendTo(row);
			if(file['width']){
				col = $('<div class="span2 file-dimensions"></div>').html(_('dimensions')).appendTo(row);
			}
			$(li).parents('ul:first').css('paddingTop',lih.outerHeight()+'px');
			$(function (){
				var p = lih.parents('.k-scrollable');
				$(p).scroll(function (){
					lih.css('top',$(this).scrollTop() + 'px')
				});
			});
			
		}
        var row = $('<div class="row-fluid"></div>').appendTo(li);
        var col = $('<div class="span6 file-name"></div>').html(file.name).appendTo(row);
        var plc = $('<div class="placeholder"></div>').prependTo(col);
        col = $('<div class="span1 file-size"></div>').html(size_format(file.size)).appendTo(row);
        //col = $('<div class="span1 file-ctime"></div>').html(utime_format(file.ctime)).appendTo(row);
        col = $('<div class="span3 file-mtime"></div>').html(utime_format(file.ctime)).appendTo(row);
        if(file['width']){
            col = $('<div class="span2 file-dimensions"></div>').html(file.width + 'px - ' + file.height+ 'px').appendTo(row);
        }
        
    }
}

var file_setup = function (li,data){
    li = $(li).removeClass('not-load');
    
    var file = data;
    if(typeof data == 'undefined') {
        file = $.decode( li.attr('data-set') );
        li.removeAttr('data-set');
    }
    li.data('file',file);
    //li.tooltip({title:_('right-click for options'),placement:'left'});
    set_info_panel(file,li);

    li.dblclick(function (){
        pick_file(file);
    })
    var cmenu = [];
    cmenu.push(
        get_menu_item('pick_item',{
            icon : 'png/accept.png',
            label: '<b>' + _('pick_file') + '</b>',
            onclick:function(menuItemClicked,menuObject) {
                var file = $(menuObject.target).data('file');
                pick_file(file);
            }
        })
    );
    cmenu.push(
        get_menu_item('delete_item',{
            icon : 'png/trash.png',
            label: _('delete'),
            onclick:function(menuItemClicked,menuObject) {
                var selection = $('#files-list').listController('selected',true);
                var file = $(menuObject.target).data('file');
                if(!selection.length) selection.push(file);
                var files = [];
                $.each(selection,function (){
                    files.push(this.fullpath);
                });
                delete_file(files,$('#files-list').listController('selected',false));
            }
        })
    );
    cmenu.push(
        get_menu_item('download',{
            icon : 'png/download.png',
            label: _('download'),
            onclick:function(menuItemClicked,menuObject) {
                var file = $(menuObject.target).data('file');
                window.open( file.link_get + '&d=1');
            }
        })
    );
    
    if(file.is_image){
         var link_get = file.link_get + ( (view_pref=='large') ? '&w=120&h=120' : '&w=20&h=20');
        image_add_queue(link_get,li);
        //add menu items
        cmenu.push($.contextMenu.separator);
        cmenu.push(
            get_menu_item(_('preview'),{
                icon : 'png/photo.png',
                onclick:function(menuItemClicked,menuObject) {
                    var file = $(menuObject.target).data('file');
                    preview_image(file);
                }
            })
        );
    }
    else {
        var icon = 'unknown';
        if($.inArray(file.extension,icons_list)!=-1){
            icon = file.extension;
        }
        li.find('.placeholder').css({backgroundImage:'url(icons/' + ( (view_pref=='large') ? '64' : '16') + '/'+icon + '.png'});
    }
    li.contextMenu(cmenu,{
        theme:'vista'
        ,afterShow : function (target){
            $('.file-item-active').removeClass('file-item-active');
            $(target).addClass('file-item-active');
        }
        ,afterHide : function (){
            $('.file-item-active').removeClass('file-item-active');
        }
    });
    li.unselectable();
    //console.log(file);
}
var files_view = function (){
    $('#files').addClass(view_pref);
    $('#files-list .file-item').each(function (){
        file_setup(this);
    });
    $('#view_pref_settings a[data-value=' + view_pref + ']').addClass('active');
    $('#view_pref_settings a').click(function (event){
        event.preventDefault();
        $.cookie('view_pref',$(this).attr('data-value'));
        window.location.reload();
    })    
    process_queue();
}

$(function (){
    upload_handler();
    window.view_pref = $.cookie('view_pref')||'large';
    $.contextMenu.shadow = false;
    files_view();
    file_panel_toggle();
    $('#files-list').listController({
        item_selector : '.file-item'
		, cls_selected : 'file-item-selected'
        ,item_to_object : function (item){
            return $(item).data('file');
        }
    });

})