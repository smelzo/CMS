//
// news.rassegna
// --------------------------------------------------
var contentEditorCreate = function (){
    var rassegna_symbols = [
        '★'
        ,'★★'
        ,'★★★'
        ,'★★★★'
        ,'★★★★★'
        ,'5 grappoli'
        ,'4 grappoli'
        ,'3 grappoli'
        ,'2 grappoli'
        ,'1 grappolo'
    ];
    var panel = '<div>\
            <div class="container-fluid">\
                <div class="row-fluid">\
                    <div class="span12">\
                    <label>Numero rivista/data</label>\
                    <input name="title" type="text" value="">\
                    </div>\
                </div>\
                <div class="row-fluid">\
                    <div class="span4">\
                    <label>Simbolo</label>\
                    <input name="simbolo" type="text" value="">\
                    </div>\
                    <div class="span8">\
                    <label>Nome prodotto</label>\
                    <input name="prodotto" type="text" value="">\
                    </div>\
                </div>\
                <div class="row-fluid">\
                    <div class="span12">\
                    <label>Recensione</label>\
                    <textarea name="review" style="width:95%"></textarea>\
                    </div>\
                </div>\
            </div>\
    </div>';
    
    var openWindow = function (li){
        var editor = this;
        li = li || null;
        var data = {};
        if(li) {
            data.title = $(li).find('.title').text();
            data.simbolo= $(li).find('.valutazione').text();
            data.prodotto= $(li).find('.product').text();
            data.review= $(li).find('.review').html();
        }
        var $panel = $(panel);
        //fill
        $.each(data,function (k,v){
            $panel.find('[name="' + k + '"]').val(v);
        });
        var win= $panel.dialog({
            title : 'Aggiungi valutazione'
            ,modal :true
            ,width : 640
            ,height: 480
            ,close :function (){
                win.dialog('destroy').remove();
            }
            ,create: function( event, ui ) {
                
                $(this).find('[name="simbolo"]').kendoComboBox({
                    animation: false,
                    dataSource:{
                        data:rassegna_symbols
                    }
                });
            }
            ,buttons : {
                'Chiudi' : function (){
                    $(this).dialog('close')
                }
                ,'Ok' : function (){
                    save(li);
                    $(this).dialog('close')
                }
            }
        });
        
        var save = function (li){
            var data = {};
            li = li||null;
            data.simbolo = win.find('[name="simbolo"]').val();
            data.prodotto = win.find('[name="prodotto"]').val();
            data.review = win.find('[name="review"]').val();
            data.title = win.find('[name="title"]').val();
            var template = '<article class="news-rassegna">\
                           <div class="title">{{title}}</div>\
                           <div class="body">\
                           <div class="valutazione">{{simbolo}}</div>\
                                <div class="text">\
                                    <div class="product">{{prodotto}}</div>\
                                    <div class="review">{{review}}</div>\
                                </div>\
                           </div>\
                           </article>';
            var fxTemplate = Mustache.compile(template);
            
            var insert = $(fxTemplate(data));
            if(li) {
                updateItem(li,insert);
                } 
            else {
                li = createListItem(true);
                updateItem(li,insert);
            }
            update();
        } 
    } //openWindow

    var self=this;
    $(self).hide();
    var tbar = $('<div>\
                 <ul class="nav nav-pills" style="margin: 0">\
                 <li><button type="button" class="btn addReview"><i class="icon-plus"></i><span>Aggiungi valutazione</span></button></li>\
                 </ul>\
                 </div>').insertBefore(this);
    tbar.find('.addReview').click(function (event){
        event.stopPropagation();
        event.preventDefault();
        openWindow();
    })
    var host = $('<ul class="rassegna-list"></ul>').insertAfter(this);
    var update = function (){
        var html = [];
        host.find('li .article-host').each(function (){
            html.push($(this).html());
        });
        self.val(html.join("\n"));
    }
    var createListItem = function (prepend){
        prepend = prepend || false;
        var li = $('<li>\
                   <div class="row-fluid">\
                   <div class="span9 article-host"></div>\
                   <div class="span1">\
                   <button  type="button" class="btn trash-li" >\
                    <i class="icon-trash"></i>\
                    </button>\
                    <button  type="button" class="btn open-li" >\
                    <i class="icon-pencil"></i>\
                    </button>\
             </div></div></li>')
        if(prepend) li.prependTo(host);
        else li.appendTo(host);
        li.find('.trash-li').click(function (){
            event.stopPropagation();
            event.preventDefault();
            if(confirm('Confermi eliminazione?')){
                li.remove();
                update();
            }
        })
        li.find('.open-li').click(function (event){
                event.stopPropagation();
                event.preventDefault();
                openWindow(li);
            })
        return li;
    }
    var updateItem = function (li,insert){
        var e = $(li).find(".article-host").empty();
        $(insert).appendTo(e);
    }
    
    //make
    var make = function (){
        var e = $('<div></div>').html($(self).val());
        host.empty();
        $(e).find('article').each(function (){
            var li = createListItem();
            $(this).clone().appendTo(li.find('.article-host'));
        })
    }
    make();
    News.handleEvent('News.setContent',function (event){
        make();
    });
    var onSortList = function (){
        update();
    }
    $(host).sortable({
        update: function( event, ui ) {
            onSortList()
        }
    });
    return null;
}
var contentEditorCreateK = function (){


    var kEditor = $(this).kendoEditor({
                    culture:'it-IT',
                    imageDialog : false,
                    tools:[
                         {
                            template : kendo.template('<button onclick="window.addReview()" type="button" class="btn addReview"><i class="icon-plus"></i><span>Aggiungi valutazione</span></button>'),
                            name: "addReview",
                            tooltip: "Aggiungi valutazione",
                            exec: function(e) {
                                //var editor = $(this).data("kendoEditor");
                                //openWindow.call(editor);
                                // ...
                            }
                        }
                    ]
                    ,stylesheets : [
                       "css/style.css"
                    ]
                });
    window.addReview = function (){
        var editor = $(kEditor).data("kendoEditor");
        openWindow.call(editor);
    }
    return kEditor;
}