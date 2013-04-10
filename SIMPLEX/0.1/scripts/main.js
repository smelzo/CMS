function maxZ (){
	var mz =  Math.max.apply(null,$.map($('body *'), function(e,n){
			if($(e).css('position')=='absolute')
				 return parseInt($(e).css('z-index'))||1 ;
			})
	 );
	return (mz||0)+1;
}
var assets = {
    assets: {},
    include: function (asset_name, callback) {
        if (typeof callback != 'function')
            callback = function () { return false; };

        if (typeof this.assets[asset_name] != 'undefined' )
            return callback();

        var html_doc = document.getElementsByTagName('head')[0];
        var st = document.createElement('script');
        st.setAttribute('language', 'javascript');
        st.setAttribute('type', 'text/javascript');
        st.setAttribute('src', asset_name);
        st.onload = function () { assets._script_loaded(asset_name, callback); };
        html_doc.appendChild(st);
    },
    _script_loaded: function (asset_name, callback) {
        this.assets[asset_name] = true;
        callback();
    },
	include_chain : function (chain,callback){
		if($.isArray(chain)){
			var asset_name = chain.shift();
			if(!chain.length){
				assets.include(asset_name,callback);
			}
			else {
				assets.include(asset_name,function (){
					assets.include_chain (chain,callback);
				});
			}
		}
	}
};
var Weext = {}; //weext namespace

;(function($){ //unselectable plugin
	unselectable = function (e){
		var apply_unselectable = function (el){
			$(el).addClass('unselectable').attr('unselectable','on');
		}
		$(e).find('*').each(function (){
			apply_unselectable(this);
		});
		apply_unselectable(e);
	}
		// This actually adds the .contextMenu() function to the jQuery namespace
	$.fn.unselectable = function(menu,options) {
		return this.each(function(){
			unselectable(this);
		});
	};
})(jQuery);

var utime_format = function (utime){
	if(!utime) return "";
	var date = new Date(utime*1000);
	// hours part from the timestamp
	var hours = date.getHours();
	// minutes part from the timestamp
	var minutes = date.getMinutes();
	// seconds part from the timestamp
	var seconds = date.getSeconds();
	return  $.datepicker.formatDate('dd/mm/yy',date) + ' ' + hours + ':' + minutes ;
}
function number_format( number, decimals, dec_point, thousands_sep ) {
	var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
	var d = dec_point == undefined ? "," : dec_point;
	var t = thousands_sep == undefined ? "." : thousands_sep, s = n < 0 ? "-" : "";
	var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

var size_format = function (filesize){
	if(!filesize) return "0";
		if (filesize >= 1073741824) {
		filesize = number_format(filesize / 1073741824, 2, ',', '') + ' Gb';
		} else {
				if (filesize >= 1048576) {
				filesize = number_format(filesize / 1048576, 2, ',', '') + ' Mb';
				} else {
				if (filesize >= 1024) {
				filesize = number_format(filesize / 1024, 0) + ' Kb';
				} else {
				filesize = number_format(filesize, 0) + ' bytes';
				};
			};
		};
		return filesize;
}
	
var is_array = function (a){
	return $.isArray(a);
}
var call_user_function_array =  function (fx_name,args ){
	var context = window;
	if(is_array(fx_name)) {
		context = fx_name[0];
		fx_name = fx_name[1];
	}
	if(typeof args!='object' && !is_array(args)) args = [];
	
	if($.isFunction(context[fx_name])){
		var fx = context[fx_name];
		return fx.apply(context , args);
	}
	return null;
}

var call_user_function = function (fx_name/*,arg1 , arg2 ecc...*/){
	var args=[];
	for(var i=0,l=arguments.length;i<l;++i){
		if(!i) continue;
		args.push(arguments[i]);
	}
	return call_user_function_array(fx_name,args);
}

jQuery.serverPost = function (action,body,success){
	call_user_function('ON_BEFORE_AJAX');
	var data = $.extend({action:action},body||{});
	$.ajax({
		type: 'POST',
		url: window.location.href,
		data: data,
		success: function (){
			if($.isFunction(success)) {
				success.apply(window,arguments);
				call_user_function('ON_AFTER_AJAX');
			}
		},
		error: function (XMLHttpRequest, textStatus, errorThrown){
			call_user_function('ON_AFTER_AJAX');
			call_user_function('ON_AJAX_ERROR',XMLHttpRequest, textStatus,errorThrown);
			call_user_function([console,'log'],'serverPost error');
		},
		dataType:'json'
	  });
}


var activate_actions = function (context){
	if(context==undefined) context='body'
    $(context).find('*[data-action]').each(function (){
		var self=this;
		var action = $(this).attr('data-action');
		var action_bind = $(this).attr('data-action-bind') || 'click';
		if(!$(this).data('actions')){
			$(this).data('actions',[])  ;
		}
		var a  = $(this).data('actions');
		a.push(action);
		$(this).data('actions',a);
		var action_args = $(this).attr('data-action-args');
		if(!action_args) action_args = [];
		else action_args = action_args.split(',');
		var activate_action = function (evt){
            evt.preventDefault();
			
			var fx = null;
			eval('fx=' + action);
            if($.isFunction(fx)){
                fx.apply(self,action_args);
            }
        }
        $(this).bind(action_bind,activate_action);
    })
}
var _onload = [];

var add_onload = function (fx,args){
    if(!$.isArray(args)) args = [];
    _onload.push(function (){
        if($.isFunction(fx)) fx.apply(window,args);
    })
}



//list control
function listController(e,options){
	e = $(e);

	var default_options = {
		//properties
		item_selector : '.list-item'
		, cls_selected : 'list-item-selected'
		//events
		,select : $.noop
		,unselect : $.noop
		,click : null
		,dblclick : null
		//callbacks
		,item_to_object : function (item){
			return item;
		}
	}
	options = jQuery.extend(default_options,options||{});
	var o = options;
	var items = e.find(o.item_selector);
	var _sel = null;
	items.each(function (){
		unselectable(this);
	})
	var raise_select_event = function (item){
		if($(item).hasClass(o.cls_selected)){
			o.select(item);
		}
		else {
			o.unselect (item);
		}
	}
	
	var unselect_items =function (filter){
		var range = items;
		if(filter!=undefined) range = range.filter(filter);
		range.removeClass(o.cls_selected);
		range.each(function (){
			raise_select_event(this);
		});
	}
	var select_item = function (item , range){
		var index = items.index(item);
		if(!range) {
			items.filter(':not(:eq(' + index + '))').removeClass(o.cls_selected);
			$(item).toggleClass(o.cls_selected);
			raise_select_event(item);
		}
		else {
			var first = items.filter('.' + o.cls_selected + ':first').index();
			if(first==-1) select_item(item , false);
			else {
				items.removeClass(o.cls_selected);
				var from_index = Math.min(index,first);
				var to_index = Math.max(index,first);
				for(var i=from_index;i<=to_index;++i){
					items.eq(i).addClass(o.cls_selected);
					raise_select_event(items.eq(i));
				}
			}
		}
	}
	
	items.click(function (event){
		select_item(this,event.shiftKey);
	});
	if($.isFunction(o.click)){
		items.bind('click',function (event){
			o.click(this,event);
		})
	}
	if($.isFunction(o.dblclick)){
		items.bind('dblclick',function (event){
			o.dblclick(this,event);
		});
	}
	var filter_item = function (selector){
			if(!selector) throw "missing param selector";
			var item= null;
			if($.isNumber(selector)){
				item = items.eq(selector);
			}
			else item = items.filter(selector);
			return item;
	}
	//pubblic methods
	var methods = {
		/**
		 * @param mixed selector index,selector
		*/
		select : function (selector){
			var item= filter_item(selector);
			if(item && item.length) item.addClass(o.cls_selected);
			return item;
		},
		unselect : function (filter){
			unselect_items(filter);
			return null;
		},
		selected : function (to_objects){
			var range = items.filter('.' + o.cls_selected);
			if(to_objects){
				return methods.get_objects(range);
			}
			return range;
		},
		get_objects : function (selector){
			var item= filter_item(selector);
			var ret = [];
			item.each(function (){
				var obj = o.item_to_object(this);
				ret.push(obj);
			});
			return ret;
		}
	}
	//add method to data
	$.each(methods,function (name,fx){
		e.data('listController_' + name,methods[name]);
	});
}
jQuery.fn.listController = function (options){
	if(typeof options == 'string'){
		var e = this;
		var fxname = 'listController_'	+ options;
		var fx = $(e).data(fxname);
		if(fx && $.isFunction(fx)) {
			var args = $.makeArray(arguments);
			args.shift();
			return fx.apply(e,args);
		}
		return null;
	}
	return $(this).each (function (){
		listController(this,options);
	})
}
function _(name,def){
    if(def==undefined) def = name;
    if(window['resources'] && resources[name]){
        return resources[name];
    }
    return def;
}

$(function (){
    //activate actions
    activate_actions();
    $.each(_onload,function (){
        this();
    })
});



var get_qs = querystring = function (){
  var h = location.search,qs={};
  if(!h) return qs;
  var q = h.indexOf("?");
  if(q==-1) return qs
	q=h.substr(q+1);
	var parts = q.split('&');
	$.each(parts,function (i,pair){
		var p=pair.split('=');
		if(p.length>=2 && p[0]){
			qs[p[0]] = decodeURIComponent(p[1]);
		}
	});
	return qs;
}
function glue(obj){
		var a = [];
		$.each(obj,function (key,value){
			if(value!==null){
			a.push(key+'='+ encodeURIComponent(value));
			}
		});
		return a.join('&');
	}
function makeRequest(o,page){
	function pagename (){
		var uri = window.location.href;
		return uri.substring(0,uri.indexOf("?"));
	}
	
	var qs = get_qs();
	qs=$.extend(qs,o);
    page = (page)?page:pagename();
	return page  + '?' + glue(qs);
}


function inTimeValidation(input,errorOptions){
	errorOptions = $.extend({
		mode:'style'
		},errorOptions||{});
	$(input).keypress(function(event) {
		if(!event.charCode) return ;
		var ns = $(this).val()+ String.fromCharCode(event.charCode);
		if(ns && !validate_input(input,ns,errorOptions)){
			event.preventDefault();
		}
	});
}
jQuery.fn.inTimeValidation = function (errorOptions){
	if(typeof errorOptions=='undefined') errorOptions = {};
	return this.each(function (){
		inTimeValidation(this,errorOptions);
	});
}

jQuery.isDecimal = function (v){
	if(typeof v == 'undefined' || v=='' ||v==null) return false;
	v = v.replace(/,/,'.');
	var pattern = /^\d+\.*\d*?$/;
	var result = v.match(pattern);
	return result;
}
//RECUPERO DATI FORM
function form_collect_data(f,options){
	options = $.extend({
        fields_selector : '.field[name]'
		//default
	}, options||{});
        var data = {};
        $(f).find(options.fields_selector).each(function (){
            var v = $(this).val();            
            if(v==null) v='';
            if($(this).hasClass('datepicker') && v) {
                v= $.datepicker.formatDate('yymmdd',$(this).datepicker('getDate'));
            }
            if($(this).hasClass('decimal') && v) {
                //se è decimal converte la stringa in un float
                v = v.replace(/,/g,'.');
                v = parseFloat(v);
                if(isNaN(v)) v = 0;
                else v = v.toFixed(2)
            }
            data[$(this).attr('name')]= v;
        });
        return data;
}

jQuery.fn.collectData = function(options) {
	var f = this.first();
	if (f.is('form')) {
		return form_collect_data(f, options || {})
	}
	return null;
}

//VALIDAZIONE FORM STANDARD

var MESSAGES = {
	'mandatory' : 'Il campo è obbligatorio'
	,'mandatory_alt' : 'Uno dei campi è obbligatorio'
	,'numeric' : 'Il campo non è un numero'
	,'decimal' : 'Il campo non è un numero decimale'
    ,'_':function (name){
        var def = this[name];
        return _(name,def);
    }
}
function get_mandatory_groups (container){
	var groups = {};
	$(container).find('[data-mandatory-alternate]').each(function (){
		var groupname = $(this).attr('data-mandatory-alternate');
		if(!groups[groupname]) groups[groupname]=[];
		groups[groupname].push(this);
	});
	return $.isEmptyObject(groups)?null:groups;
}
var view_error = function(input, msg, options) {
	options = $.extend({
		error_class: 'input-error',
		mode: 'popover', // standard | popover | style
		close_delay: 6000,
		before_popover : function (){ return true; },
		popover_title: _('error'),
		popover_placement: 'bottom'
	}, options || {});
	//override options su data-* attribute
	$.each(options, function(name, value) {
		var attr_data = $(input).attr('data-' + name);
		if (attr_data) options[name] = attr_data;
	})

	var error_el = $('<span style="margin:0 "></span>').addClass(options.error_class).html(msg);

	var close = function() {
		$(input).removeClass(options.error_class);
		switch (options.mode) {
		case 'popover':
			$(input).popover('hide');
			break;
		case 'style':
			break;
		case 'standard':
		default:
			error_el.remove();
		}
	}
	var show_popover = function() {
		var popoverOptions = {
			placement: options.popover_placement,
			trigger: 'manual',
			content: function() {
				return $('<div></div>').append(error_el).html();
			},
			title: options.popover_title,
			template: '<div class="popover popover-' + options.error_class + '"><div class="arrow"></div><div class="popover-inner"><h4 class="popover-title"></h4><div class="popover-content"><p></p></div></div></div>'
		}
		$(input).data('popover',null).popover(popoverOptions);
		if(!options.before_popover(input,popoverOptions)) {
			return $(input);
		}
		return $(input).popover('show');
	}
	var show = function() {
		$(input).addClass(options.error_class);
		switch (options.mode) {
		case 'popover':
			var pop = show_popover();
			//console.log(pop);
			break;
		case 'style':
			break;
		case 'standard':
		default:
			error_el.insertAfter(input);
		}
		if (options.close_delay) {
			window.setTimeout(close, options.close_delay);
		}
        $('body').one('mousedown',function (){
            close();
        })
	}
	show();
}
/**
 * @example
 * validate_input(input)
 * validate_input(input,v,errorOptions)
 * validate_input(input,errorOptions)
*/
function validate_input(input,v,errorOptions){
	if($.isPlainObject(v)) { // signature (input,errorOptions)
		errorOptions = v;
		v = $(input).val();
	}
	else {
		if(typeof v=='undefined') v = $(input).val();
		if(typeof errorOptions=='undefined') errorOptions = {};
	}
	var empty = (v==null || v=='');
	
	if($(input).hasClass('mandatory') && empty){
		view_error(input, MESSAGES._('mandatory'),errorOptions);
		return false;
	}
	if($(input).is('[data-number],.number,.numeric') && !empty && !$.isNumber(v)){
		view_error(input, MESSAGES._('numeric'),errorOptions);
		return false;
	}
	if($(input).is('[data-decimal],.decimal') && !empty && !$.isDecimal(v)){
		view_error(input, MESSAGES._('decimal'),errorOptions);
		return false;
	}
	
	return true;
}

function validate_form(form,errorOptions){
	errorOptions = $.extend({
		mode:'popover'
		}, errorOptions||{});
	var check_result = true;
	////mandatory
	//$(form).find('.mandatory').each(function (i,item){
	//	if(!$(this).val()){
	//		check_result = false;
	//		view_error(this, MESSAGES.mandatory);
	//		return false;
	//	}
	//});
	if(!check_result) return check_result; // STOP
	//mandatory_groups controllato da data-mandatory-alternate='<nome_gruppo>'
	var mandatory_groups = get_mandatory_groups(form);
	if(mandatory_groups){
		$.each(mandatory_groups,function (groupname , group){
			var filled = false;
			$.each(group,function (i, control){
				if($(control).val()){
					filled=true;
					return  false;
				}
			});
			if(!filled) {
				check_result = false;
				$.each(group,function (){
					view_error(this, MESSAGES._('mandatory_alt'),errorOptions);
				});
				
			}
		})
	}
	if(!check_result) return check_result; // STOP
	//data-mandatory-if-set
	$(form).find('[data-mandatory-if-set]').each(function (){
		var selector = $(this).attr('data-mandatory-if-set]');
		var dep =$(form).find('[name='+selector+']');
		if(dep.length && dep.val() && !$(this).val()) {
			check_result = false;
			view_error(this, MESSAGES._('mandatory'),errorOptions);
			return false;
		}
	});
	
	//altri controlli demandati a validate_input
	$(form).find('input').each(function (){
		var valid = validate_input(this,errorOptions);	   
		if(!valid){
			check_result = false;
			return false;
		}
	});
	
	return check_result;
}
function validate_form_interactive(form){
	$(form).find('input[data-validation-interactive]').each(function (){
		var eventType = $(this).attr('data-validation-interactive');
		$(this).bind(eventType,function (){
			validate_input(this);
		})
	})
}
