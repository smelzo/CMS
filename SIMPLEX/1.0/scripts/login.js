add_onload(function (){
    
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
                view_error($(f),_('login_fail'));
                return ;
            }
            else {
                window.location.reload();
            }
        })
    })
    $('#login-show-link').click(function (event){
        event.preventDefault();
        $('#login-panel').show().dialog({
            width:390
            ,height:210
            ,title : '<h6>Login</h6>'
        });
        //display: block; z-index: 1006; outline: 0px none; position: absolute; height: 207.8px; width: 385.8px; top: 506px; left: 202px;
    });

})