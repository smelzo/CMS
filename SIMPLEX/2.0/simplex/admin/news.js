function open_cats_dialog(options){
    var url =makeRequest({module:'cats',type:window.current_type,dtv:((new Date).valueOf())});
    options = $.extend( {
        url_params :null
    },  options||{});
    
    var el = $('<div class="open_cats_dialog-host"></div>')
    .appendTo('body');
    var win= el.kendoWindow({
            iframe :true,
            modal:true,
            width: ( Math.min(940, parseInt($(window).width()*0.95)) ) + "px",
            height: ( Math.min(720, parseInt($(window).height()*0.95)) ) + "px",
            title: "Categorie",
            actions: ["Maximize", "Close"],
            content: url,
            close:function (){
                
                win.destroy();
            }
        }).data('kendoWindow');
    window.open_cats_dialog_close = function (){
        win.destroy();
        window.open_cats_dialog_close =$.noop;
    }
    News.onCloseCategories = function (){
        win.destroy();
        News.onCloseCategories = $.noop;
        return true;
    }
    win.center();
    win.open();
    return win;
}

var News = {
    page : 1,
    nrows : 80,
    newsList : null,
    mainForm : null,
    currentSet : [], //
    currentItem:null,
    contentEditor:null,
    admin_path:'simplex/admin/',//path dove cercare il template
    onChangeCategories : function (){
        $.serverPost('News.getCategories',{type:window.current_type , lang : window.current_lang},function (data){
            window.categories = data;
            News.setupCategories(News.currentItem);
        })
    },
    getTemplate : function (template,data,callback){
        $.ajax({
            url:this.admin_path + template
            ,cache : false
            ,dataType:'html'
        })
        .success(function (html){
            callback(html,data);
        })
    }
    ,getItems : function (callback){
        //$=null,$cat=null,$page=1,$nrows=20,$orderby=false,$where=false
        $.serverPost('News.getItems',{
            'type':window.current_type
            ,lang:window.current_lang
            ,cat:window.current_cat?window.current_cat:0
            ,page : News.page
            ,nrows : News.nrows
            ,orderby:0
            ,where:0
            },function (items){
                callback(items);
            });
    }
    ,formatters : {
        format_date : function (v){
            if (!v) return v;
            var av = v.split(' ');
            var dt = $.datepicker.parseDate('yy-mm-dd', av[0]);
            return $.datepicker.formatDate('dd/mm/yy',dt);
        }
        ,format_abstract : function (v){
            v=str_truncate(strip_tags(v),20,'&hellip;');
            return v;
        }
    }
    
    //MARK > cambia lingua
    ,changeLang : function (lg){
        window.current_lang =
        window.lang = lg;
        var self=this;
        var prevId = 0;
        if(self.currentItem) {
            prevId = self.currentItem.id;
        }
        self.loadPanel(function (){
            if(prevId) {
                self.openItem(prevId);
            }
            else {
                self.openNew();
            }
        });
    }
    ,reset : function(){
        $('#news-editor').removeClass('new edit');
        News.setDescription('');
        News.setContent('');
        $('input.field').val('');
    }

    ,openItem : function(id){
        var self=this;
        $.serverPost('News.getItem',{id:id,lang:window.current_lang},function (item){
            self.reset();
            self.currentItem = item;
            self.newsList.find('>li.active').removeClass('active');
            self.newsList.find('>li[data-id="' + id +'"]').addClass('active');
            
            self.newsEditorPanel.find('input[name="title"]:first').val(self.currentItem.title);
            News.setDescription(self.currentItem['abstract']||'');
            News.setContent(self.currentItem['content']||'');
            self.setupCategories(News.currentItem);
            self.setupImages(News.currentItem);
            self.setupFiles(News.currentItem);
            $('#news-editor').addClass('edit');        
        })

    }
    ,openNew : function (){
        var self=this;
        $(self.newsList).find('li.active').removeClass('active');
        self.currentItem = null;
        self.reset();
        self.setupCategories();
        self.setupImages(News.currentItem);
        self.setupFiles(News.currentItem);
        $('.nav-tabs').each(function (){
            $(this).find('li:first a').tab('show');
        })
        $('#news-editor').addClass('new');
    }
    , removeItem : function (item){
        //prima rimuove da currentSet
        var self=this;
        var newsList = self.newsList;
        var newCurrentSet = [];
        $.each(self.currentSet,function (i,member){
            if(member.id != item.id) newCurrentSet.push(member);
        });
        self.currentSet = newCurrentSet;
        //poi dal DB
        $.serverPost('News.removeItem',{id:item.id},function (){
            //dopo cancella anche dal pannello
            var liToDelete = newsList.find('li[data-id=' + item.id + ']');
            var nextLi = $(liToDelete).next();
            if(!nextLi.length) {
                nextLi = $(liToDelete).prev();
            }
            liToDelete.remove();
            //MARK > controlla lo stato del pannello di destra
            var isEdit =  self.newsEditorPanel.is('.edit');
            if(isEdit) {
                if(!nextLi.length) {
                    self.openNew();
                }
                else {
                    self.openItem(nextLi.attr('data-id'));
                }
            }
        });
    }
    ,prependListItem : function (item){
        var self=this;
        var html = self.panelLiTemplate;
        var newsList = self.newsList;
        var e = $(html);
        e.attr('data-id',item.id);
        newsList.prepend(e);
        self.updateListItem(item,e);
    }
    //MARK > aggiorna li dopo update
    ,updateListItem : function (item,e){
        var self=this;
        var newsList = self.newsList;
        if(e==undefined){
            e = newsList.find('li[data-id=' + item.id + ']');
        }
        $(e).find('[data-content]').each(function (){
            var v = item[$(this).attr('data-content')];
            var df=$(this).attr('data-format');
            if (df) {
                if ($.isFunction(self.formatters[df])) {
                    v = self.formatters[df].call(self,v);
                }
            }
            $(this).html(v);
        });
        
    }
    //MARK > aggiorna pannello sinistra non ricarica dati dal server
    ,fillPanelList : function (data,html){
        html = html || self.panelLiTemplate;
        var self=this;
        var newsList = self.newsList;
        newsList.empty();
        $.each(data,function (i,item){
            var e = $(html);
            e.attr('data-id',item.id);
            newsList.append(e);
            self.updateListItem(item,e);
            
            
        });  
    }
    //MARK > reloadPanel dopo cambiamenti 
    ,reloadPanel : function (){
        var data = this.currentSet;
        this.fillPanelList(data);
    }
    //carica pannello news
    ,loadPanel : function (callback){
        var self=this;
        var newsList = self.newsList;
        
        this.getItems(function (data){
            self.currentSet = data.result;
            //template : news.item.template
            self.getTemplate('news.item.template',data,function (html,data){
                self.panelLiTemplate = html;
                if (self.currentSet) {
                    //code
                    self.fillPanelList(self.currentSet,html);
                }
                if($.isFunction(callback)) {
                    callback();
                }
            })
        })
    }
    ,backIndex : function (){
       window.location.href = makeRequest({},'manager.php');
    }
    // trova membro di currentSet
    ,findItem : function (item){
        if (!$.isNumeric(item)) {
            item = parseInt($(item).attr('data-id')||0);
        }
        if (!News.currentSet) return null;
        
        if (!$.isNumeric(item)) {
            throw 'function findItem item argument is invalid';
            return null;
        }
        var result = null;
        $.each(News.currentSet,function (i,member){
            if (item == member.id) result = member;
        });
        return result;
    }
    ,addCategory:function (id,title){
        $('#category-list')
            .tokenInput("add",{id:id,title:title})
    }
    
    ,setupCategories : function (item){ // usabile in refresh
        item = item || null;
        var self=this;
        self.categoriesControl = $('#categories');
        $(self.categoriesControl).empty();
        var html = '<input id="category-list" type="text" autocomplete="off">';
        $(self.categoriesControl).html(html);
        var prepopulate = [];
        var values = [];
        if (item && item.categories){
            prepopulate = item.categories;
        }
        //tokenInput reference : http://loopj.com/jquery-tokeninput/
        $(self.categoriesControl)
            .find('#category-list')
            .tokenInput(categories,{
                propertyToSearch:'title'
                ,prePopulate:prepopulate
                ,preventDuplicates:true
                ,theme:'facebook'}
                );
        //update lista dropdown
        var dropdownList = $('#cats-drop-menu'); // <ul>
        dropdownList.empty();
        var liTemplate = Mustache.compile($('#cats-drop-menu-li-template').html());
        $.each(window.categories,function (i,data){
            var li = $(liTemplate(data)).appendTo(dropdownList);
            li.find('a').click(function (event){
                event.preventDefault();
                var id_cat = $(this).attr('data-id');
                var title = $(this).attr('data-title');
                self.addCategory(id_cat,title);
            })
        })
    }
    ,insertImageInGallery : function (thumbnail,file){
        if(!$(thumbnail).is('li')) {
            $(thumbnail).parents('li:first').data('controller').insertImage(file);
        }
        else {
            $(thumbnail).data('controller').insertImage(file);
        }
        
    }

    ,setupMainImage : function (item){
        item = item || null;
        var self=this;
        var empty = '<div class="thumb-placeholder thumb-empty" style="height: 240px">\
                        <i class="icon-camera"></i>\
                    </div>';
        var normal = '<div class="thumb" style="height: 240px">\
                        <span class="remove" data-role="remove-thumbnail"><span><i class="icon-minus"></i></span></span>\
                       <div class="thumb-image"></div>\
                    </div>';
        
        var element = $('#main-image');
        var setController = function (){
            var controller = {};
            controller.emptyTemplate = empty;
            controller.normalTemplate = normal;
            controller.element = element;
            controller.setEmpty = function (){
                $(element).empty().html(empty);
                $(element).removeAttr('data-url');
                return $(element);
            }
            controller.setNormal = function (){
                $(element).empty().html(normal);
                $(element).find('span.remove').one('click',function (event){
                    event.stopPropagation();
                    controller.setEmpty();
                })
                return $(element);
            }
            controller.insertImage= function (arg){
                var url =undefined;
                controller.setNormal();
                if(arg['url']!=undefined) {
                    var file = arg; // da file, restituito da imagePicker
                    var parsed = parse_url(file.url);
                    url = file.url;
                    if(parsed.host == window.location.host) {
                        url = parsed.path;
                    }                    
                }
                else if (arg['image']!=undefined) {
                    //da DB
                    url = arg['image'];
                }
                
                if(url!=undefined && url!='') {
                    $(element).find('.thumb .thumb-image').css({'background-image':'url(' + url + ')'});
                    $(element).attr('data-url',url);
                }
                else {
                    controller.setEmpty();
                }
            }
            $(element).data('controller',controller);
            return $(element);
        }
        element.empty();
        setController();
        if(item) element.data('controller').insertImage(item);
        else element.data('controller').setEmpty();
        return element;
    }
    
    ,setGalleryImage : function (url,li){
        var self=this;
        var id_img =li.attr('data-id-img') || 0;
        $.serverPost('News.setGalleryImage',{id:id_img,id_news:self.currentItem.id,url:url,position:$(li).index()},function (response_id){
            li.attr('data-id-img',response_id);
            self.showSaveMsg();
        });
    }
    ,removeGalleryImage : function (li){
        var self=this;
        var id_img =li.attr('data-id-img') || 0;
        $.serverPost('News.removeGalleryImage',{id:id_img},function (){
            self.showSaveMsg();
        });
    }
    ,setupImages : function (item){ // usabile in refresh  | item = news Object
        item = item || null;
        var self=this;
        self.setupMainImage(item);
        var empty = '<div class="thumbnail gallery-thumbnail">\
                        <div class="thumb-placeholder thumb-empty" style="height: 120px">\
                        <i class="icon-camera"></i>\
                        </div>\
                    </div>';
        var normal = '<div class="thumbnail gallery-thumbnail">\
                        <div class="thumb">\
                        <span class="remove" data-role="remove-thumbnail"><span><i class="icon-minus"></i></span></span>\
                       <div class="thumb-image"></div>\
                       <p class="thumb-text">\
                       <input name="description" type="text" class="thumb-textbox">\
                       </p>\
                       </div>\
                    </div>';
        var ul = $('#gallery-thumbnails');
        //image gallery li controller
        var setController = function (li){
            var controller = {};
            controller.emptyTemplate = empty;
            controller.normalTemplate = normal;
            controller.ul = ul;
            controller.setEmpty = function (){
                $(li).empty().html(empty);
                $(li).removeAttr('data-url');
                $(li).removeAttr('data-id-img');
                return $(li);
            }
            controller.setNormal = function (){
                $(li).empty().html(normal);
                $(li).find('span.remove').one('click',function (event){
                    event.stopPropagation();
                    self.removeGalleryImage(li);
                    controller.setEmpty();
                })
                return $(li);
            }
            controller.insertImage= function (arg){
                var url =undefined, description = undefined;
                controller.setNormal();
                if(arg['url']!=undefined) {
                    var file = arg; // da file, restituito da imagePicker
                    var parsed = parse_url(file.url);
                    url = file.url;
                    if(parsed.host == window.location.host) {
                        url = parsed.path;
                    }
                    description = undefined;
                    self.setGalleryImage(url,li);
                    
                }
                else if (arg['path']!=undefined) {
                    //da DB
                    url = arg['path'];
                    description = arg['description'];
                    li.attr('data-id-img',arg['id']);
                }
                
                if(url!=undefined) {
                    $(li).find('.thumb .thumb-image').css({'background-image':'url(' + url + ')'});
                    $(li).attr('data-url',url);
                }
                $(li).attr('data-image-id',0);
                if(arg['id']!=undefined){
                    $(li).attr('data-image-id',arg['id']);
                }
                if(description!=undefined) $(li).find('input[name="description"]').val(description);
            }
            $(li).data('controller',controller);
            return $(li);
        }
        ul.empty();
     
        for(var i=0,l=maxGalleryImages;i<l;++i){
            var li = $('<li class="span2"></li>').appendTo(ul);
            setController(li).data('controller').setEmpty();
        }
        if(item) {
            $.each(item.images,function (i,imageObj){
                var li = ul.find('>li:eq('+i+')');
                $(li).data('controller').insertImage(imageObj);
            })
        }
        var onSortGallery = function (){
            var series = [];
            $(ul).find('>li[data-image-id]').each(function (i,el){
                var id_img = $(el).attr('data-image-id');
                series.push({id:id_img,position:(i+1)});
            });
            if(series.length) {
                $.serverPost('News.setPositions',{table:'news_images',series:$.encode(series)},function (){
                    self.showSaveMsg();
                })
            }
        }
        $(ul).sortable({
            update: function( event, ui ) {
                onSortGallery()
            }
        });
    }
    ,setFileDownload : function (url,li){
        var self=this;
        var id_file =li.attr('data-id-file') || 0;
        $.serverPost('News.setFileDownload',{id:id_file,id_news:self.currentItem.id,url:url,position:$(li).index()},function (response_id){
            li.attr('data-id-file',response_id);
            self.showSaveMsg();
        });
    }
    ,removeFileDownload : function (li){
        var self=this;
        var id_file =li.attr('data-id-file') || 0;
        $.serverPost('News.removeFileDownload',{id:id_file},function (){
            self.showSaveMsg();
        });
    }
    ,setupFiles : function (item){
        item = item || null;
        var self=this;
        var row = '<div class="file-rows file-row row-fluid">\
                       <div class="span file-actions" data-role="tooltip" title="Rimuovi file">\
                            <span class="remove" data-role="remove-file"><span><i class="icon-minus"></i></span></span>\
                       </div>\
                       <div class="span file-pathname">\
                            <i class="icon-file"></i>\
                            <span class="filename"></span>\
                       </div>\
                       <div class="span file-name">\
                            <input name="description" type="text" class="name-textbox">\
                       </div>\
                    </div>';
        var ul = $('#files-list');
        //controller to ul#files-list
        var setController = function (){
            var controller = {};
            var createRow = function (){
                var li = $('<li>' + row + '</li>').appendTo(ul);
                $(li).find('span.remove').one('click',function (event){
                    event.stopPropagation();
                    if(!confirm('Rimuovere file?')) return ;
                    self.removeFileDownload (li);
                    li.remove();
                })
                $(li).find('[data-role="tooltip"]').tooltip();
                $(li).find('[name="description"]').change(function (){
                    var id = 0 , table = '' , value = $(this).val() , idname = '', lg = current_lang;
                    id = $(li).attr('data-id-file');
                    table = 'news_downloads_text';
                    idname = 'id_news_downloads';
                    if(id) {
                        $.serverPost('News.changeCaption',{table:table,id:id,idname:idname,value:value,lang:lg},function (){
                            self.showSaveMsg();
                        })
                    }
                })
                .focus(function (){
                    $(this).select();
                })
                
                return $(li);
            }
            controller.insertFile= function (arg){
                var url =undefined, description = undefined;
                var li = createRow();
                if(arg['url']!=undefined) {
                    var file = arg; // da file, restituito da imagePicker
                    var parsed = parse_url(file.url);
                    url = file.url;
                    if(parsed.host == window.location.host) {
                        url = parsed.path;
                    }
                    description = undefined;
                    self.setFileDownload(url,li);
                }
                else if (arg['path']!=undefined) {
                    //da DB
                    url = arg['path'];
                    description = arg['description'];
                    li.attr('data-id-file',arg['id']);
                }
                
                if(url!=undefined) {
                    $(li).find('.filename').text( basename(url));
                    $(li).attr('data-url',url);
                }
                
                if(description==undefined) description = basename(url);
                $(li).find('input[name="description"]').val(description);
            }
            $(ul).data('controller',controller);
            return $(ul);
        }
        $(ul).empty();
        setController();
        if(item && item['downloads']!=undefined) {
            $.each(item.downloads,function (i,file){
             $(ul).data('controller').insertFile(file)
            });
        }
        var onSortFiles = function (){
            var series = [];
            $(ul).find('>li[data-id-file]').each(function (i,el){
                var id_file = $(el).attr('data-id-file');
                series.push({id:id_file,position:(i+1)});
            });
            if(series.length) {
                $.serverPost('News.setPositions',{table:'news_downloads',series:$.encode(series)},function (){
                    self.showSaveMsg();
                })
            }
        }
        $(ul).sortable({
            update: function( event, ui ) {
                onSortFiles()
            }
        });
        return ul;
//open_file_dialog        
    }
    ,collectData : function (){
        var self=this;
        var panel = self.newsEditorPanel;
        var data = {'type':window.current_type,lang:window.current_lang,title:'',image:'',description:'',categories : []};
        data.id = News.currentItem?News.currentItem.id:0;
        
        data.title = $(panel).find('input[name="title"]:first').val();
        data.description = News.getDescription();
        data.content = News.getContent();
        data.categories = $('#category-list').tokenInput("get");
        data.image = $('#main-image').attr('data-url');
        $.each(data,function (k,v){
            if(v==undefined) data[k]=null;
        })
        return data;
    }
    ,showSaveMsg : function (msg){
        msg = msg || 'Dati salvati correttamente...';
        $('#save-msg').html('<p>' + msg + '</p>').fadeIn(800).delay(2000).fadeOut(800);
    }
    //save
    ,save : function (){
        var self=this;
        var data = self.collectData();
        if(!data.title) {
            alert('Dati insufficienti');
            return ;
        }
        
        if(!data.id) {
            //aggiungi
        }
        $.serverPost('News.save',{data:$.encode(data)},function (response){
            if(response && response['item']!=undefined){
              //TODO > aggiorna dopo salva
              var item = response['item'];
              
              var id = item.id;
              var existentItem = self.findItem(id);
              if(existentItem) self.updateListItem(item);
              else {
                if(!$.isArray(self.currentSet)) self.currentSet = [];
                self.currentSet.unshift(item);
                self.prependListItem(item);
                self.openItem(item.id);
              }
              self.showSaveMsg();
            }
        })
    }
    //prepara spazio di lavoro
    ,setupWorkspace : function (){
        var self=this;
        //layout elements
        self.newsList = $('#news-list');
        self.newsEditorPanel = $('#news-editor');
        self.mainForm = $('[data-role="main-form"]');
        self.actionDisplayElement = $('#action-display');
        //remove by type
        $('.' + current_type + '-remove').remove();
        //hover
        $('[data-hover]').each(function (){
            $(this).mouseenter(function (){
                $(this).addClass($(this).attr('data-hover'));
            })
            $(this).mouseleave(function (){
                $(this).removeClass($(this).attr('data-hover'));
            })
        });
        //events
        self.mainForm.submit(function (event){
            event.preventDefault();
            self.save();
        });
        $('#btn-save,[data-role="save"]').click(function (){
            $('#form-news,form[data-role="main-form"]').submit();
        });
        $('#btn-close').click(function (){
            self.backIndex();
        });
        $('body').on('click','.edit-categories',{},function (event){
            event.stopPropagation();
            event.preventDefault();
            open_cats_dialog({});
        });
        $('body').on('click','.thumb-textbox',{},function (event){
            event.stopPropagation();
        });
        $('body').on('click','.gallery-thumbnail',{},function (event){
            var thumbnail = $(this);
            var win = open_images_dialog(null,{
                modal:true
                ,url_params:{
                    fminw:news_size.width
                    ,fminh:news_size.height
                }
            });
            window.pick_file = function (file){
                self.insertImageInGallery(thumbnail,file);
                win.destroy();
            }
        });
        $('[data-role="tooltip"]').tooltip();
        //apre news
        $('body').on('click','.open-item,[data-role="open-item"]',{},function (event){
                event.preventDefault();
                var li = $(this).parents('li:first');
                var id = li.attr('data-id');
                self.openItem(id);
            });
        //delete news
        $('body').on('click','.trash-item,[data-role="delete-item"]',{},function (event){
            event.preventDefault();
            if(confirm('Stai per cancellare un Elemento.Confermi?')){
                var item = self.findItem($(this).parents('li:first'));
                self.removeItem(item);
            }
        });
        //modalità nuovo
        $('body').on('click','#btn-new',{},function (event){
            event.preventDefault();
            self.openNew();
        });
        //inserisce immagine
        $('body').on('click','#main-image',{},function (event){
            event.preventDefault();
            //var win = window.top.open_images_dialog(null,{
            var win = open_images_dialog(null,{
                modal:true
                ,url_params:{
                    fminw:news_size.width
                    ,fminh:news_size.height
                }
            });
            window.pick_file = function (file){
                if(!file.is_image) {
                    alert('il file non è un\'immagine');
                    return ;
                }
                $('#main-image').data('controller').insertImage(file);
                win.destroy()
            }
        });
        //inserisce file
        $('body').on('click','.files-list-add',{},function (event){
            var win = open_file_dialog(null,{
                modal:true
                ,url_params:{
                   
                }
            });
            window.pick_file = function (file){
                $('#files-list').data('controller').insertFile(file);
                win.destroy()
            }
        });
        //cambia il testo a un thumbnail
        $('body').on('change','.thumb-text input',{},function (event){
            var id = 0 , table = '' , value = $(this).val() , idname = '', lg = current_lang;
            
            if($(this).parents('[data-image-id]').length) {
                id = $(this).parents('[data-image-id]').attr('data-image-id');
                table = 'news_images_text';
                idname = 'id_news_images';
            }
            if($(this).parents('[data-file-id]').length) {
                id = $(this).parents('[data-file-id]').attr('data-file-id');
                table = 'news_downloads_text';
                idname = 'id_news_downloads';
            }
            if(id) {
                $.serverPost('News.changeCaption',{table:table,id:id,idname:idname,value:value,lang:lg},function (){
                    self.showSaveMsg();
                })
            }
        });
        //setup controls
        self.setupCategories(self.currentItem);
        self.setupImages(self.currentItem);
        self.setupFiles(self.currentItem);
        //splitter
        $('.splitter').kendoSplitter({
            panes:[
                 {collapsible:true,size:'260px',min:'260px'}
                ,{collapsible:false}
            ]
        });
        var dropdownLangs = $('.dropdown-langs').kendoDropDownList({
            change : function (e){
                self.changeLang(this.value());
            }
        });
        description_textarea = $('#news-editor textarea[name=description]');
        description_textarea.kendoEditor({
            culture:'it-IT',
            tools:[
                "bold",
                "italic",
                "underline",                
            ]
        });
        content_textarea = $('#news-content-textarea');
        $('#ctrl-contenuto').on('shown',function (event){
            if ($(event.target).hasClass('editor-installed')) {
                return ;
            }
            $(event.target).addClass('editor-installed');
                News.contentEditor = content_textarea.kendoEditor({
                    culture:'it-IT',
                    imageDialog : false,
                    tools:[
                        "bold",
                        "italic",
                        "underline",
                        "fontSize",
                        "justifyLeft",
                        "justifyCenter",
                        "justifyRight",
                        "justifyFull",
                        "insertUnorderedList",
                        "insertOrderedList",
                        "indent",
                        "outdent",
                        "formatBlock",
                        "createLink",
                         "unlink",
                         "viewHtml",
                         "insertImage"
                    ]
                    ,stylesheets : [
                        "simples/css/editor.css",
                        "simples/css/page.css"
                    ]
                });
        }) ;
        $('input.field').attr('autocomplete','off');
        $('.waiter').remove();
        $('#news-editor,.header').css('visibility','visible');
        self.loadPanel();
    }
    //GET SET da editor
    ,_getEditorValue : function (){
        if($(this).is('.k-content')) {
            return $(this).data('kendoEditor').value();
        }
        else {
            return $(this).val();
        }
    }
    ,_setEditorValue : function (s){
        if($(this).is('.k-content')) {
            $(this).data('kendoEditor').value(s);
        }
        else {
            $(this).val(s);
        }
    }
    ,getContent : function (){
        return News._getEditorValue.call(content_textarea);
    }
    ,setContent : function (s){
        News._setEditorValue.call(content_textarea,s);
    }
    ,getDescription : function (){
        return News._getEditorValue.call(description_textarea);
    }
    ,setDescription : function (s){
        News._setEditorValue.call(description_textarea,s);
    }
}

jQuery(document).ready(function (){
    News.setupWorkspace();
})
/**
 * Get
*/