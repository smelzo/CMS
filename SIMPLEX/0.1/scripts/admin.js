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