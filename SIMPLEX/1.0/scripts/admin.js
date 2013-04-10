$(function(){

    //ini plugin

    jQuery.event.freezeEvents = function(elem) {

    	if (typeof(jQuery._funcFreeze)=="undefined")
    		jQuery._funcFreeze = [];

    	if (typeof(jQuery._funcNull)=="undefined")
    		jQuery._funcNull = function(){ };

    	// don't do events on text and comment nodes
    	if ( elem.nodeType == 3 || elem.nodeType == 8 )
    		return;

    	var events = jQuery.data(elem, "events"), ret, index;

    	if ( events ) {

    		for ( var type in events )
    		{
    			if ( events[type] ) {

    				var namespaces = type.split(".");
    				type = namespaces.shift();
    				var namespace = RegExp("(^|\\.)" + namespaces.slice().sort().join(".*\\.") + "(\\.|$)");

    				for ( var handle in events[type] )
    					if ( namespace.test(events[type][handle].type) ){
    						if (events[type][handle] != jQuery._funcNull){
    							jQuery._funcFreeze["events_freeze_" + handle] = events[type][handle];
    							events[type][handle] = jQuery._funcNull;
    						}
    					}
    			}

    		}
    	}
    }

    jQuery.event.unFreezeEvents = function(elem) {

    	// don't do events on text and comment nodes
    	if ( elem.nodeType == 3 || elem.nodeType == 8 )
    		return;

    	var events = jQuery.data(elem, "events"), ret, index;

    	if ( events ) {

    		for ( var type in events )
    		{
    			if ( events[type] ) {

    				var namespaces = type.split(".");
    				type = namespaces.shift();

    				for ( var handle in events[type] )
    					if (events[type][handle]==jQuery._funcNull)
    						events[type][handle] = jQuery._funcFreeze["events_freeze_" + handle];

    			}
    		}
    	}
    }

    jQuery.fn.freezeEvents = function() {

    	return this.each(function(){
    		jQuery.event.freezeEvents(this);
    	});

    };

    jQuery.fn.unFreezeEvents = function() {

    	return this.each(function(){
    		jQuery.event.unFreezeEvents(this);
    	});

    };

});
var adminScriptURL = (function() {
    var scripts = document.getElementsByTagName('script');
    var index = scripts.length - 1;
    var myScript = scripts[index];
    return  myScript.src; ;
})();

//setup
function admin_ui_setup(){
    //var uri = parseUri(adminScriptURL);
    var ui_base = Path.set(adminScriptURL).parent().parent().get();
    //window.CKEDITOR_BASEPATH=ui_base+'/ckeditor/';
    Loader([
            //{   src:ui_base+'/ckeditor/ckeditor.js',type : 'script'},
            {  src:ui_base+'/admin/ui/activate_editors.js',type : 'script'}
            ],{
    
    });
}
function toolbar_setup(){
    admin_ui_setup();
    var instance = {};
    var tb =  instance.toolbar = $('#admin-toolbar').prependTo('body');
    tb.find('.collapser').click(function (){
        tb.toggleClass('collapsed');
        if(tb.hasClass('collapsed')){
            $.cookie("ADMIN_TOOLBAR","collapsed");
            tb.find('.collapser i').removeClass().addClass('icon-caret-down');
        }
        else {
            tb.find('.collapser i').removeClass().addClass('icon-caret-up');
            $.cookie("ADMIN_TOOLBAR","expanded");
        }
        //recalc();
    });
    var at_state = $.cookie("ADMIN_TOOLBAR");
    if(at_state=='collapsed'){
        tb.addClass('collapsed');
        tb.find('.collapser i').removeClass().addClass('icon-caret-down');
    }
    window.AdminToolbar = instance;
    return instance;
}


function add_toolbar_item(item_action,text,icon){
    var template = '<li>' ;
      template += '<a href="#">';
      if(icon){
        template += '<i class="icon-' + icon +'"></i>'
      }
      template += '<span>' + text + '</span>';
      template += '</a>'+
    '</li>';
    template = $(template);
    if($.isFunction(item_action)){
        template.find('a').click(function (event){
            event.preventDefault();
            item_action.call(this);
        })
    }
    else {
        template.find('a').attr('href',item_action);
    }
    return template;
}

//translation stub
var a_ = function (name){
    if(typeof admin_resources !='undefined' && admin_resources[name]){
        return admin_resources[name];
    }
    return name;
}
;(function ($){
    function adminWindow(el,options){
            options = $.extend({
                
            },options||{});
            return $(el).kendoWindow(options);
    }
    $.fn.adminWindow = function (options){
        return this.each(function (){
            adminWindow(this,options);
        })
    }
})(jQuery);


;(function ($){
    function apply_dialog(el,options){
        options = $.extend({
            url : false
        },options||{});
        if((!el ||!$(el).is('iframe')) && !options.url) {
            console.log('not call')
            return ;
        }
        if(options.url) {
            //build iframe
            el = $('<iframe marginWidth="0" marginHeight="0" frameBorder="0"  scrolling="auto"></iframe>')
                .attr('src',options.url)
                .appendTo('body');
        }
        options.open = function (){
            $(this).css({margin:'0',padding:'0',width:'100%'});
        }
        options.close = function (){
            $(this).remove();
        }
        return $(el).dialog(options);
    }
    $.fn.iframe_dialog = function (options){
        var jq = jQuery();
        $(this).each(function (i,el){
            if(!$(el).is('iframe')) el = $('body');
            jq.add(apply_dialog.call(el,el,options));
        });
        return jq;
    }
    $.iframe_dialog = function (url,options){
        options.url = url;
        //console.log(options);
        return apply_dialog(null,options);
    }
})(jQuery);

;(function ($){
    function open_images_dialog(url,options){
        if(typeof url == 'undefined' || !url) url = 'simplex/filemanager/index.php?m=images&t=images';
        options = $.extend( {
            url_params :null
        },  options||{});
        if(options.url_params) {
            url += '&' + glue(options.url_params);
        }
        var el = $('<div class="open_images_dialog-host"></div>')
        .appendTo('body');
        var win= el.kendoWindow({
                iframe :true,
                modal:false,
                width: parseInt($(window).width()*0.95) + "px",
                height: parseInt($(window).height()*0.95) + "px",
                title: "Images",
                actions: ["Maximize", "Close"],
                content: url,
                close:function (){
                     win.destroy();
                }
            }).data('kendoWindow');
        win.center();
        win.open();
        return win;
    }
    window.open_images_dialog = open_images_dialog;
})(jQuery);

;(function($, undefined) {

var kendo = window.kendo,
    extend = $.extend,
    Editor = kendo.ui.editor,
    EditorUtils = Editor.EditorUtils,
    dom = Editor.Dom,
    registerTool = EditorUtils.registerTool,
    ToolTemplate = Editor.ToolTemplate,
    RangeUtils = Editor.RangeUtils,
    Command = Editor.Command,
    keys = kendo.keys,
    INSERTIMAGE = "Insert Image",
    KEDITORIMAGEURL = "#k-editor-image-url",
    KEDITORIMAGETITLE = "#k-editor-image-title";

var ImageCommand = Command.extend({
    init: function(options) {
        var that = this;
        Command.fn.init.call(that, options);
        that.async = true;
        that.attributes = {};
    },

    insertImage: function(img, range) {
        var attributes = this.attributes;
    
        if (attributes.src && attributes.src != "http://") {
            if (!img) {
                img = dom.create(RangeUtils.documentFromRange(range), "img", attributes);
                img.onload = img.onerror = function () {
                        img.removeAttribute("complete");
                        img.removeAttribute("width");
                        img.removeAttribute("height");
                    };

                range.deleteContents();
                range.insertNode(img);
                range.setStartAfter(img);
                range.setEndAfter(img);
                RangeUtils.selectRange(range);
                return true;
            } else {
                dom.attr(img, attributes);
            }
        }

        return false;
    },

    redo: function () {
        var that = this,
            range = that.lockRange();

        if (!that.insertImage(RangeUtils.image(range), range)) {
            that.releaseRange(range);
        }
    },

    exec: function () {
        
        var that = this,
            range = that.lockRange(),
            applied = false,
            img = RangeUtils.image(range),
            windowContent, dialog;


        function apply(e) {
            that.attributes = {
                src: $(KEDITORIMAGEURL, dialog.element).val(),
                alt: $(KEDITORIMAGETITLE, dialog.element).val()
            };

            applied = that.insertImage(img, range);

            close(e);

            if (that.change) {
                that.change();
            }
        }

        function close(e) {
            e.preventDefault();
            dialog.destroy();

            dom.windowFromDocument(RangeUtils.documentFromRange(range)).focus();
            if (!applied) {
                that.releaseRange(range);
            }
        }

        function keyDown(e) {
            if (e.keyCode == keys.ENTER) {
                apply(e);
            } else if (e.keyCode == keys.ESC) {
                close(e);
            }
        }
        dialog = open_images_dialog(null,{modal:true});
        window.pick_file = function (file){
            if(file.is_image) {
                var src= file.link_get;
                that.attributes = {
                    src: file.link_get,
                    alt: ''
                };
                applied = that.insertImage(img, range);
            }
            dialog.destroy()
        }
    
    }

});
kendo.ui.editor.ImageCommand = ImageCommand;

registerTool("insertImage", new Editor.Tool({ command: ImageCommand, template: new ToolTemplate({template: EditorUtils.buttonTemplate, title: INSERTIMAGE}) }));

})(jQuery);