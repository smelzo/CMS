function LangsEditor(){

}
Cats = {
    compiledTemplates : {}
    ,currentItem:null
    ,callParentFunction : function (fx,params,objectName){
        var wparent = window.parent;
        if(!wparent){
            //console.log('parent non trovata')
            return ;
        }
        var object  = wparent;
        if(objectName) object = object[objectName];
        if(!object) {
            //console.log('object ???')
            return ;
        }
        fx = object[fx];
        if($.isFunction(fx)){
            if(!$.isArray(params)) params = [];
            return fx.apply(object,params);
        }
        return false;
    }
    ,backIndex : function (){
       
        if(Cats.callParentFunction('onCloseCategories',[],'News')){
            this.callParentFunction('onChangeCategories',[],'News');
            return ;
        }
        else {
            //window.location.href = makeRequest({},'manager.php');     
        }
    }
    ,setCategories : function (cats){
        this.callParentFunction('onChangeCategories',[],'News');
        window.categories = cats;
    }
    ,getCategories : function (){
        return window.categories;
    }
    
    ,reset : function(){
        var self=this;
        self.catsEditorPanel.removeClass('new edit');
        $('input.field,textarea.field').val('');
    }
    ,findItem : function (id){
        return window.categories[id] || null;
    }
    ,openItem : function(id){
        var self=this;
        self.reset();
        self.currentItem = self.findItem(id);
        self.catsList.find('>li.active').removeClass('active');
        self.catsList.find('>li[data-id="' + id +'"]').addClass('active');
        if(self.currentItem) {
            $('input.field[name="name"]').val(self.currentItem['name']);
        }
        if(self.currentItem && self.currentItem['titles']) {
            $.each(self.currentItem['titles'],function (n,titleItem){
                //console.log(titleItem);
                //console.log(self.catsEditorPanel.find('input[name="title"][lang="' + titleItem.lang + '"]'));
                self.catsEditorPanel.find('input[name="title"][lang="' + titleItem.lang + '"]').val(titleItem.content);
            })
        }
        if(self.currentItem && self.currentItem['descriptions']) {
            $.each(self.currentItem['descriptions'],function (lg,descriptionItem){
                var textarea = self.catsEditorPanel.find('textarea[name="description"][lang="' + lg + '"]');
                var keditor = textarea.data('kendoEditor');
                keditor.value(descriptionItem);
            })
        }
        self.setupMainImage(self.currentItem);
        self.catsEditorPanel.addClass('edit');
    }
    ,openNew : function (){
        var self=this;
        $(self.catsList).find('li.active').removeClass('active');
        self.currentItem = null;
        self.reset();
        self.setupMainImage(self.currentItem);
        self.catsEditorPanel.addClass('new');
    }
    ,showSaveMsg : function (msg){
        msg = msg || 'Dati salvati correttamente...';
        $('#save-msg').html('<p>' + msg + '</p>').fadeIn(800).delay(2000).fadeOut(800);
    }
    , onSortList : function (){
        var self=this,
            catsList = self.catsList;
        
        var series = [];
        $(catsList).find('>li[data-id]').each(function (i,el){
            var id = $(el).attr('data-id');
            series.push({id:id,position:(i+1)});
        });
        if(series.length) {
            $.serverPost('News.setPositions',{table:'news_categories',series:$.encode(series)},function (){
                self.showSaveMsg();
            })
        }
    }
    , loadPanel : function (activeId){
        var self=this;
        activeId = activeId || 0;
        $(self.catsList).empty();
        var globalCats = {};
        $.serverPost('News.getCategoriesAll',{type:window.current_type},function (data){
            if(!$.isArray(data)) return ;
            $.each(data,function (i,item){
                //window.categories.push(item);
                globalCats[item.id] = item;
                //prepare for templating
                var titles = [];
                $.each(item.titles,function (k,v){ titles.push({lang:k,content:v});});
                item.caption = titles[0].content;
                item.titles = titles;
                var arrayTitles = [];
                $.each(item.titles,function (i,t){
                    arrayTitles.push(t.content);
                });
                item.caption = arrayTitles.join('/');
                var li = $(self.compiledTemplates.catLi(item)).appendTo(self.catsList);
                if(activeId == item.id) li.addClass('active');
            });
            self.setCategories(globalCats);
            self.catsList.sortable({
                update: function( event, ui ) {
                    self.onSortList()
                }
            })
        })
    }
    ,setupMainImage : function (item){
        item = item || null;
        var self=this;
        var empty = '<div class="thumb-placeholder thumb-empty" style="height: 100px">\
                        <i class="icon-camera"></i>\
                    </div>';
        var normal = '<div class="thumb" style="height: 100px">\
                        <span class="remove" data-role="remove-thumbnail"><span><i class="icon-minus"></i></span></span>\
                       <div class="thumb-image"></div>\
                    </div>';
        
        var element = $('#main-image');
        var setController = function (){
            var controller = {};
            controller.emptyTemplate = self.compiledTemplates.mainImageEmpty;
            controller.fillTemplate = self.compiledTemplates.mainImageFill;
            controller.element = element;
            controller.setEmpty = function (){
                $(element).empty().html(controller.emptyTemplate({}));
                $(element).removeAttr('data-url');
                return $(element);
            }
            controller.setNormal = function (url){
                $(element).empty().html(controller.fillTemplate({url:url}));
                $(element).find('span.remove').one('click',function (event){
                    event.stopPropagation();
                    controller.setEmpty();
                })
                return $(element);
            }
            controller.insertImage= function (arg){
                var url =undefined;
                
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
                    controller.setNormal(url);
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
    
    ,collectData : function (){
        var self=this;
        var panel = self.catsEditorPanel;
        var data = {'type':window.current_type,image:''};
        data.id = self.currentItem?self.currentItem.id:0;
        
        data.titles = [];
        data.descriptions = [];
        data.image = $('#main-image').attr('data-url');
        data.name = $('input.field[name="name"]').val();
        var failed = (data.name)?false:true;
        
        //title
        $(panel).find('input[name="title"]').each(function (){
            var lg = $(this).attr('lang'), 
                content = $(this).val();
            if(!content) failed =true;
            data.titles.push({lang:lg,content:content});
        })
        //description
        $(panel).find('textarea[name="description"]').each(function (){
            var lg = $(this).attr('lang'), 
            description = $(this).data('kendoEditor').value();
            data.descriptions.push({lang:lg,content:description});
        })
        $.each(data,function (k,v){
            if(v==undefined) data[k]=null;
        })
        return failed?false : data;
    }

    , save : function (){
        var self=this;
        var data = self.collectData();
        if(!data) {
            alert('Dati insufficienti');
            return ;
        }
        $.serverPost('News.saveCat',{data:$.encode(data)},function (response){
              //TODO > aggiorna dopo salva
              var id = response;
              self.loadPanel(id);
              return ;
              if(Cats.callParentFunction('onCloseCategories',[],'News')){
                self.callParentFunction('onChangeCategories',[],'News');
                return ;
              }
        })
    }
    , removeItem : function (id){
        var self=this;
        var catsList = self.catsList;
        //poi dal DB
        $.serverPost('News.removeCatItem',{id:id},function (){
            self.openNew()
            self.loadPanel();
        });
    }
    , setupWorkspace : function (){
        var self=this;
        self.compiledTemplates.catLi = Mustache.compile($('#cat-li-template').html());
        self.compiledTemplates.mainImageEmpty = Mustache.compile($('#mainimage-empty-template').html());
        self.compiledTemplates.mainImageFill = Mustache.compile($('#mainimage-fill-template').html());
        //layout elements
        self.catsList = $('#cats-list');
        self.catsEditorPanel = $('#cats-editor');
        self.mainForm = self.catsEditorPanel.find('form:first');
         //remove by type
        $('.' + current_type + '-remove').remove();
        
        self.loadPanel();
        //events
        self.mainForm.submit(function (event){
            event.preventDefault();
            self.save();
        });
        $('#btn-save,[data-role="save"]').click(function (){
            self.mainForm.submit();
        });
        //apre news
        $('body').on('click','.open-item,[data-role="open-item"]',{},function (event){
                event.preventDefault();
                var id = $(this).parents('li:first').attr('data-id');
                self.openItem(id);
            });
        $('#btn-close').click(function (event){
            event.preventDefault();
            self.backIndex();
        });
        //modalità nuovo
        $('body').on('click','#btn-new',{},function (event){
            event.preventDefault();
            self.openNew();
        });
        //delete news
        $('body').on('click','.trash-item,[data-role="delete-item"]',{},function (event){
            event.preventDefault();
            if(confirm('Stai per cancellare un Elemento.Confermi?')){
                var id = $(this).parents('[data-id]:first').attr('data-id');
                self.removeItem(id);
            }
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
        //setup controls
         $('.nospace').keypress(function(event) {
            if (!(event.which == 32 ))return true;
                //ctrl + S
                event.preventDefault();
                var v = $(this).val() + '_';
                $(this).val(v);
                return false;
        });
        //description
        self.setupMainImage(self.currentItem);
        $('#description-tabs textarea').kendoEditor({
            culture:'it-IT',
            tools:[
                "bold",
                "italic",
                "underline",
                "viewHtml"
            ]
        });
        $('#description-tabs .tab-pane-impl').addClass('tab-pane');
        $('#description-tabs [data-toggle="tab-impl"]').attr('data-toggle','tab').each(function (){
            $(this).click(function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        })
        
        //splitter
        $('.splitter').kendoSplitter({
            panes:[
                 {collapsible:true,size:'260px',min:'260px'}
                ,{collapsible:false}
            ]
        });
       $('input.field,textarea').attr('autocomplete','off');
        $('.waiter').remove();
        $('#cats-editor,.footer').css('visibility','visible');
    }
}
jQuery(document).ready(function (){
    Cats.setupWorkspace();
})