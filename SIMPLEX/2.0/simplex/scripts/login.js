function toolbar_setup(){
    var instance = {};
    var tb =  instance.toolbar = $('#admin-toolbar').appendTo('body');
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
    $(window).scroll(function (){
        tb.css('top',$(window).scrollTop() + 'px');
    })
    tb.css('top',$(window).scrollTop() + 'px');
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

add_onload(function (){
    toolbar_setup();
    $('#admin-toolbar').css('z-index',maxZ());
    $('#logout-link').click (function (event){
        event.preventDefault();
        $.serverPost('Users.logout',{},function (response){
            window.location.reload();
        });
    })
    $('#login-form').submit(function (event){
        event.preventDefault();
        if(!validate_form(this)) return ;
        var data = $(this).collectData({fields_selector:'input:text,input:password'});
        var f = this;
        $.serverPost('Users.login',data,function (response){
            if(!parseInt(response)){
                view_error($(f).find('input:first'),_('login failed'));
                return ;
            }
            else {
                window.location.reload(true);
            }
        })
    });
    $('#login-panel').show().dialog({
        width:390
        ,height:'auto'
        ,dialogClass:"no-close"
        ,title : '<h6>Login</h6>'
        ,buttons : {
            'Ok' : function (){
                $('#login-form').submit();
            }
        }
    });
    $('#login-show-link').click(function (event){
        event.preventDefault();
        $('#login-panel').show().dialog({
            width:390
            ,height:210
            ,dialogClass:"no-close"
            ,title : '<h6>Login</h6>'
        });
        //display: block; z-index: 1006; outline: 0px none; position: absolute; height: 207.8px; width: 385.8px; top: 506px; left: 202px;
    });

})