//CKEDITOR.disableAutoInline = true;
//CKEDITOR.config.toolbar_Full = [
//    ['Save','Preview','-','Templates','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
//    '/',
//    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
//    ['Link','Unlink','Anchor'],
//    '/',
//    ['Font','FontSize']
//];
//CKEDITOR.config.floatSpacePinnedOffsetY = 35;
//CKEDITOR.on('dialogDefinition',function (ev){
//    var dialogName = ev.data.name;
//	var dialogDefinition = ev.data.definition;
//})

var myEditor = {
	button : null,
	temp : null,
	content_editor:null,
	getSizeByElementType : function (){
		var nodeName = $(this).get(0).nodeName;
		var sizeType = 'normal'
		switch(nodeName.toLowerCase()){
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
			case 'a':
			case 'span':
			case 'p':
				sizeType ='small';
				break;
		}
		var ww = $(window).width();
		var wh = $(window).height();
		var size = {
			width:$(this).outerWidth(),
			height:$(this).outerHeight()
		}
		if(sizeType=='small'){
			size.width = Math.max( size.width,200);
			size.height = Math.max( size.height,100);
		}
		else {
			size.width = Math.max( size.width,ww/2);
			size.height = Math.max( size.height,wh/2);
		}
		return size;
	},
	activate : function (){
		myEditor.deactivate();
		//html
		var html = $(this).html();
		var self=this;
		var host_div = $('<div></div>').addClass('editing').appendTo('body');
		var textareaId = uniquid();
		var textarea = $('<textarea></textarea>').attr('id',textareaId).appendTo(host_div);
		textarea.val(html);
		var size = myEditor.getSizeByElementType.call(this);
		textarea.width(size.width).height(size.height);
		//extra height for toolbar
		//size.height+=100;
		//host_div.width(size.width).height(size.height);
		host_div.css({
				//width:size.width + 'px',
				zIndex:maxZ(),
				width:'auto',
				top : '50%',
				left : '50%',
				//height:size.height + 'px',
				height:'auto',
				minWidth: Math.max(size.width,100)+'px',
				//minHeight:'30px',
				minHeight:size.height + 'px',
				marginLeft: '-' + (size.width/2) + 'px',
				marginTop: '-' + (size.height/2) + 'px'
				});
		
		myEditor.content_editor = $('#'+textareaId).kendoEditor({
					culture:'it-IT',
					tools:[
						{
							name : "save",
							tooltip : 'Save changes',
							exec : function (e){
								var editor = $(this).data("kendoEditor");
								var pagename = $(self).parents('[data-pagename]');
								var save_error = function (reason){
									reason = reason || '';
									alert('this page cannot be modified ' + reason);
								}
								if(!pagename.length) {
									save_error('page name not defined');
									return ;
								}
								pagename = pagename.attr('data-pagename');
								var id_element = $(self).attr('id');
								if(!id_element) {
									save_error('id element not defined');
									return ;
								}
								var lang = $(self).attr('lang');
								
								var html = editor.value();
								
								var params = {
									pagename:pagename,
									id_element:id_element,
									lang:lang,
									html:html
								}
								$.serverPost('Simplex.save_page_html_fragment',params,function (response){
									if(response){
										$(self).html(html);		
									}
									else {
										save_error();
									}
								})
							}
						},
						"bold",
						"italic",
						"underline"
						
					]
				}).data('kendoEditor');
		
		host_div.draggable();
		$(document).one('click',function (){
			myEditor.deactivate();
		});
		host_div.click(function (event){
			event.stopPropagation();
		});
		myEditor.temp = host_div;
	},
	deactivate : function (){
		//window.editorMask.hide();

		if(myEditor.content_editor){
			var editor = myEditor.content_editor;
			if(editor && editor['destroy']) editor.destroy();
			myEditor.content_editor=null;
		}
		if(myEditor.temp) {
			myEditor.temp.fadeOut('fast',function (){
				$(this).remove();
			})
			myEditor.temp = null;
		}
	}
}

function createEditor(){
	//this = <a href="#"></a> edit della toolbar
	var button = $(this).parents('li').toggleClass('pressed');
	
	myEditor.button = button;
	
	if(button.hasClass('pressed')){
		$('html').addClass('editor-active');
	}
	else {
		$('html').removeClass('editor-active');
		myEditor.deactivate();
	}
}
function activateEditables(){
	var item = add_toolbar_item(createEditor,'Edit','edit').insertBefore('#admin-toolbar li.first'),
		editables = $('[data-content=editable]');
	//window.editorMask = $('<div class="editor-mask"></div>')
	//				.css({zIndex:maxZ()})
	//				.appendTo('body')
	//				.hide();
	var handy_zindex = maxZ();
	editables.each(function (){
		var handyTemplate = $('<div class="handy"><i class="icon-hand-down"></i></div>');
		var handy = $(this).linkedElement({template:handyTemplate,hcls:'content-editable',markerShow :'html.editor-active'});
		var self=this;
		handy.bind('click',function (event){
			if(!$('html').hasClass('editor-active')) return ;
			event.stopPropagation();
			event.preventDefault();
			myEditor.activate.call(self);
			
		});
	});
}

var SingleImageEditor = {
	activate : function (){
		var img = $(this);
		var srcWidth = img.width();
		var srcHeight = img.height();
		console.log(img);
		var win = open_images_dialog(null,{
                modal:true
                ,url_params:{
                   
                }
            });
		window.pick_file = function (file){
                if(!file.is_image) {
                    alert('il file non è un\'immagine');
                    return ;
                }
                //if(file.width < news_size.width || file.height< news_size.height) {
                //    alert('l\'immagine deve avere dimensioni di almeno ' +  news_size.width + 'x' + news_size.height + ' pixel');
                //    return ;
                //}
                var link_get = file.link_get +  '&w=' + srcWidth + '&h=' + srcHeight ;
				var oldSrc = img.attr('src');
                img.attr('src' , file.link_get)
				//.css({width:'auto',height:'auto'});
                //sectionProps.find('[itemprop=photo]').attr('src' , link_get);
                win.destroy();
				if(confirm('Save changes?')){
					//codice save
				}
				else {
					img.attr('src' , oldSrc);
				}
            }
	}
}
function activateSingleImageEdit(){
	var editables = $('[data-image=editable]');
	var handy_zindex = maxZ();
	editables.each(function (){
		var handyTemplate = $('<div class="handy"><i class="icon-picture"></i></div>');
		var handy = $(this).linkedElement({template:handyTemplate,hcls:'content-editable',markerShow :'html.editor-active'});
		var self=this;
		handy.bind('click',function (event){
			if(!$('html').hasClass('editor-active')) return ;
			event.stopPropagation();
			event.preventDefault();
			SingleImageEditor.activate.call(self);
		});
	});
}
function activateEditors(){
	activateEditables();
	activateSingleImageEdit();
}

if(window['App']!=undefined){
	App.pages_loaded(activateEditors);
}
else {
	activateEditors();
}
