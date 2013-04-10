<style type="text/css" media="all">
#left-pane .pane-content {
    padding:0;
    
}
</style>
<div class="waiter">
    <div class="inner">
        <i class="icon-cogs"></i>
        <span><?php echo a_('loading')?> ...</span>
    </div>
</div>
<div id="user_groups" class="hide">
    <table class="table table-striped">
        <thead>
            <th scope="col">
                <?php echo a_('name')?>
            </th>
            <th scope="col">
                <?php echo a_('power')?>
            </th>
        </thead>
        <tbody data-template="user_groups-row-template" data-bind="source: groups">
        </tbody>
    </table>
</div>
<div id="users-editor" class="full-panel" style="height: 100%;">
    <div style="margin:10px">
        <ul id="user_tab" class="nav nav-tabs">
            <li class="active"><a href="#panel_users" data-toggle="tab"><?php echo a_('users')?></a></li>

            <li><a href="#panel_groups" data-toggle="tab"><?php echo a_('groups')?></a></li>
        </ul>

        <div class="tab-content">
            <div id="panel_users" class="tab-pane active">
                <div id="users-host">
                    <!--users here -->
                    <table class="table table-striped">
                        <thead>
                            <th scope="col">
                                <?php echo a_('name')?>
                            </th>
                            <th scope="col">
                                <?php echo a_('email')?>
                            </th>
                            <th scope="col">
                                <?php echo a_('power')?>
                            </th>
                            <th scope="col">
                                <button onclick="add_user()" class="btn">
                                    <i class="icon-plus"></i>
                                    <span><?php echo a_('add_user')?></span>
                                </button> 
                            </th>
                        </thead>
                        <tbody data-template="users-row-template" data-bind="source: users">
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="panel_groups" class="tab-pane">
                <div id="groups-host">
                    <!--groups here -->
                    <table class="table table-striped">
                        <thead>
                            <th scope="col">
                                <?php echo a_('name')?>
                            </th>
                            <th scope="col">
                                <?php echo a_('power')?>
                            </th>
                            <th scope="col">
                                <button onclick="add_group()" class="btn">
                                    <i class="icon-plus"></i>
                                    <span><?php echo a_('add_group')?></span>
                                </button> 
                            </th>
                        </thead>
                        <tbody data-template="groups-row-template" data-bind="source: groups">
                        </tbody>
                    </table>                    
                </div>
            </div>
        </div>
    </div>
</div>
<script id="users-row-template" type="text/x-kendo-template">
    <tr id="users-row-#: get('id')#">
        <td data-bind="text: name"></td>
        <td data-bind="text: email"></td>
        <td >
            # if(get('power')==100 ){ #
                <?php echo a_('administrator')?>
            # } #
            # if(get('power')<100 && get('power')>=80){ #
                <?php echo a_('poweruser')?>
            # } #
            # if(get('power')<80 && get('power')>=50){ #
                <?php echo a_('user')?>
            # } #
            # if(get('power')<50 && get('power')>=30){ #
                <?php echo a_('lightuser')?>
            # } #
            # if(get('power')<30){ #
                <?php echo a_('none')?>
            # } #
        </td>
        <td>
            <button data-bind="events:{click: openEdit}" class="btn">   
                <i class="icon-edit"></i>
                <span><?php echo a_('edit')?></span>
            </button>
            <button onclick="openUserGroups(#: get('id') #)" class="btn">   
                <i class="icon-cog"></i>
                <span><?php echo a_('groups')?></span>
                <span class="label label-success">
                # if(typeof get('groups')!='undefined'){ #
                #:  get('groups').length #
                # } #
                </span>
            </button>
        </td>
    </tr>    
</script>
<script id="groups-row-template" type="text/x-kendo-template">
    <tr id="groups-row-#: get('id')#">
        <td data-bind="text: name"></td>
        <td >
            # if(get('power')==100 ){ #
                <?php echo a_('administrator')?>
            # } #
            # if(get('power')<100 && get('power')>=80){ #
                <?php echo a_('poweruser')?>
            # } #
            # if(get('power')<80 && get('power')>=50){ #
                <?php echo a_('user')?>
            # } #
            # if(get('power')<50 && get('power')>=30){ #
                <?php echo a_('lightuser')?>
            # } #
            # if(get('power')<30){ #
                <?php echo a_('none')?>
            # } #
        </td>
        <td>
            <button data-bind="click: openEdit" class="btn">
                <i class="icon-edit"></i>
                <span><?php echo a_('edit')?></span>
            </button>
        </td>
    </tr>    
</script>
<script id="user_groups-row-template" type="text/x-kendo-template">
    <tr id="user_groups-row-#: get('id')#">
        <td>
            <input type="checkbox" class="checkbox" data-id="#: get('id')#"/>
            #: get('name') #
        </td>
        <td >
            # if(get('power')==100 ){ #
                <?php echo a_('administrator')?>
            # } #
            # if(get('power')<100 && get('power')>=80){ #
                <?php echo a_('poweruser')?>
            # } #
            # if(get('power')<80 && get('power')>=50){ #
                <?php echo a_('user')?>
            # } #
            # if(get('power')<50 && get('power')>=30){ #
                <?php echo a_('lightuser')?>
            # } #
            # if(get('power')<30){ #
                <?php echo a_('none')?>
            # } #
        </td>
        
    </tr>    
</script>
<div id="user-window" data-template="users-edit-template" data-bind="source: this"></div>
<div id="group-window" data-template="groups-edit-template" data-bind="source: this"></div>
<script id="users-edit-template" type="text/x-kendo-template">
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span6">
            <label for="name"><?php echo a_('name')?></label>
            <input name="name" type="text" class="k-textbox" data-bind="value: name"/>
            <div>
                <label for="power"><?php echo a_('power')?> <span class="show_power label label-info"></span></label>
                <input name="power" data-role="slider" data-max="100" data-min="0" data-small-step="10" data-large-step="20"  data-bind="value: power" />
            </div>
        </div>
        <div class="span6 ">
            <label for="email"><?php echo a_('email')?></label>
            <span class="k-textbox"><input name="email" type="text"  data-bind="value: email"/></span>
            <label for="password"><?php echo a_('password')?></label>
            <span class="k-textbox"><input name="password" type="password"  data-bind="value: password"/></span>
        </div>
    </div>
    <hr>
    <div class="row-fluid">
        <div class="span6">&nbsp;</div>
        <div class="span6">
            <button data-bind="events:{click: edit}" class="btn">
                <i class="icon-save"></i>
                <span><?php echo a_('save')?></span>
            </button>
            # if(get('id')) { #
            <button data-bind="events:{click: remove}" class="btn">
                <i class="icon-trash"></i>
                <span><?php echo a_('remove')?></span>
            </button>
            # } #
        </div>
    </div>
</div>
</script>

<script id="groups-edit-template" type="text/x-kendo-template">
    <div class="container-fluid">
        <div class="row-fluid" style="margin:10px 0">
            <div class="span12">
                <label for="name"><?php echo a_('name')?></label>
                <input name="name" type="text" class="k-textbox" data-bind="value: name"/>
                
            </div>

        </div>
        <div class="row-fluid" style="margin:10px 0">
            <div class="span12 ">
               <div>
                    <label for="power"><?php echo a_('power')?> <span class="show_power label label-info"></span></label>
                    <input name="power" data-role="slider" data-max="100" data-min="0" data-small-step="10" data-large-step="20"  data-bind="value: power" />
                </div>
            </div>        
        </div>
        
        <hr>
        <div class="row-fluid" style="margin:10px 0">
            <div class="span12" style="text-align:right;padding-right:10px">
                <button data-bind="click: edit" class="btn">
                    <i class="icon-save"></i>
                    <span><?php echo a_('save')?></span>
                </button>
                # if(get('id')) { #
                <button data-bind="events:{click: remove}" class="btn">
                    <i class="icon-trash"></i>
                    <span><?php echo a_('remove')?></span>
                </button>
                # } #
            </div>
        </div>
    </div>
</script>

<script type="text/javascript">
    /**
     * add_user
    */add_user = function (){
        var model = {
            name:''
            ,password:''
            ,email:''
            ,power:''
        }
        open_window_user.call(model);
    }
    
    /**
     * group model
    */add_group = function (){
        var model = {
            name:''
            ,power:''
        }
        open_window_group.call(model);
    }
    var find_group = function (id){
        var result = null;
        $.each(window.GROUPS.groups,function (i,g){
            if(g.id==id){
                result = g;
                return false
            }
        });
        return result;
    }
    open_window_usergroups = function (){
        var groups = window.GROUPS;
        var user = this,win = null;
        var el = $('#user_groups').clone().removeClass('hide');
        kendo.bind(el,groups);
        win = el.kendoWindow({
                width: "620px",
                height: "450px",
                title: user.name + ' ' + a_('users_groups') + '',
                actions: ["Maximize", "Close"],
                close:function (){
                    win.destroy();
                }
            }).data('kendoWindow');
        win.center();
        win.open();
        var group_ids = [];
        if(user && user['groups']){
            $.each(user.groups,function (){
                group_ids.push(this.id);
            })
        }
        
        el.find('input:checkbox').each(function (){
            var id = $(this).attr('data-id');
            if($.inArray(id,group_ids)!=-1){
                $(this).attr('checked','checked');
            }
            $(this).change(function (){
                var checked = $(this).attr('checked')?true:false;
                var action = checked?'add_user_group':'remove_user_group';
                $.serverPost('Users.' + action , {id_user:user.id,id_group : id},function (response){
                    if(parseInt(response)<=0) {
                        console.log('errore');
                        return ;
                    }
                    if(!checked && user && user['groups']){
                        var ugroups = user.get('groups');
                        var remove_i = -1;
                        $.each(ugroups,function (i,ug){
                            if(ug.id==id){
                                remove_i = i;
                                return false;
                            }
                        });
                        if(remove_i>=0){
                            ugroups.splice(remove_i,1);
                            kendo.bind($("#users-host"), window.USERS);
                            //user.get('groups',ugroups);
                        }
                    }
                    else if(checked && user && user['groups']){
                        var g = find_group(id);
                        user.groups.push(g);
                        kendo.bind($("#users-host"), window.USERS);
                    }
                })
            })
            
        })
    }
    openUserGroups =  function (id){
        $.each(window.USERS.users,function (){
            if(this.id==id){
                open_window_usergroups.call(this);
                return  false;
            }
        })
    }
/**
 * open_window_group
*/    open_window_group = function (){
        var el = $("#group-window").clone();
        var src = this;
        
        var win= false;
        src.remove = function (){
            var self=this;
            if(self['id']) {
                $.serverPost ('Users.delete_group',{id:self.id},function (response){
                    if(response)  {
                        var delete_index = null,id=self.id;
                         $.each(window.GROUPS.groups,function (i,item){
                            if(parseInt(item.id) == parseInt(id)){
                                delete_index=i;
                                return false;
                            }
                        });
                         if(delete_index!==null){
                            window.GROUPS.groups.splice(delete_index,1);
                            win.destroy();
                            kendo.bind($("#groups-host"), window.GROUPS);
                         }
                    }
                })
                
            }
            win.destroy();
        }
        src.edit = function (){
            var ok = false;
            var self=this;
            if(!self['name']) {
                alert(a_('missing_group_name'));
                return ;
            }
            if(!self['id']) {
                $.serverPost ('Users.add_group',{g:$.encode(self)},function (id){
                    if(id)  {
                         $.cookie('USER_TAB',1);
                        window.location.reload();
                        //self.id = id;
                        //window.GROUPS.groups.push(self);
                        //ok=true;
                    }
                })
            }
            else {
                var id = self['id'];
                $.serverPost ('Users.edit_group',{id:id,data:$.encode(self)},function (esito){
                    if(esito){
                        $.each(window.GROUPS.groups,function (i,item){
                            if(parseInt(item.id) == parseInt(id)){
                                ok=true;
                                item = self;
                                return false;
                            }
                        });
                    }
                });
            }
            win.destroy();
            kendo.bind($("#groups-host"), window.GROUPS);
        }
        kendo.bind(el, src);
        //post bind
        
        win = el.kendoWindow({
                width: "320px",
                height: "250px",
                title: a_('group'),
                actions: ["Maximize", "Close"],
                close:function (){
                    win.destroy();
                }
            }).data('kendoWindow');
        win.center();
        win.open();
        var slider = el.find('input[name=power]').data("kendoSlider");
        slider.bind('change' , function (e){
            show_power(e.value,el.find('.show_power'));
        });
        show_power(src.power,el.find('.show_power'));
    }
/**
 * show_power format power value
*/    function show_power(value,control){
        var text = "";
            if(value==100 ){ text =a_('administrator');} 
            else if(value<100 && value>=80){ text =a_('poweruser');} 
            else if(value<80 && value>=50){ text =a_('user');} 
            else if(value<50 && value>=30){ text =a_('lightuser');} 
            else if(value<30){ text =a_('none');}
            $(control).text (text);
    }

/**
 * open_window_user
*/    open_window_user = function (){
        var el = $("#user-window").clone();
        var src = this;
        var win= false;
        src.remove = function (){
            var self=this;
            if(self['id']) {
                $.serverPost ('Users.delete_user',{id:self.id},function (response){
                    if(response)  {
                        var delete_index = null,id=self.id;
                         $.each(window.USERS.users,function (i,item){
                            if(parseInt(item.id) == parseInt(id)){
                                delete_index=i;
                                return false;
                            }
                        });
                         if(delete_index!==null){
                            window.USERS.users.splice(delete_index,1);
                            win.destroy();
                            kendo.bind($("#users-host"), window.USERS);
                         }
                    }
                })
                
            }
            win.destroy();
        }
        src.edit = function (){
            var self=this;
            if(!self['name']) {
                alert(a_('missing_user_name'));
                return ;
            }
            if(!self['email'] || !self['password']) {
                alert(a_('missing_user_login'));
                return ;
            }
            if(!self['id']) {
                $.serverPost ('Users.add_user',{u:$.encode(self)},function (id){
                    if(id)  {
                        $.cookie('USER_TAB',0);
                        window.location.reload();
                    }
                })
            }
            else {
                var id = self['id'];
                $.serverPost ('Users.edit_user',{id:id,data:$.encode(self)},function (esito){
                    if(esito){
                        $.each(window.USERS.users,function (i,item){
                            if(parseInt(item.id) == parseInt(id)){
                                item = self;
                                return false;
                            }
                        });
                    }
                });
            }
            win.destroy();
            kendo.bind($("#users-host"), window.USERS);
        }
        kendo.bind(el, src);
        var win = el.kendoWindow({
                width: "500px",
                height: "250px",
                title: a_('user'),
                actions: [  "Close"],
                close:function (){
                    win.destroy();
                }
            }).data('kendoWindow');
        win.center();
        win.open();
        var slider = el.find('input[name=power]').data("kendoSlider");
        slider.bind('change' , function (e){
            show_power(e.value,el.find('.show_power'));
        });
        show_power(src.power,el.find('.show_power'));        
    }
    
/**
 * bind_groups
*/    bind_groups = function (source){
        window.GROUPS = source;
        $.each(source,function (i,item){
            item.openEdit = function (){
                open_window_group.call(this);
            }
        })
        var viewModel = kendo.observable({
            groups : source
        });
        window.GROUPS = viewModel;
        kendo.bind($("#groups-host"), viewModel);
    }
    
/**
 * bind_users
*/    bind_users = function (source){
        
        $.each(source,function (i,item){
            item.openEdit = function (){
                open_window_user.call(this);
            }
           
        })
        window.USERS =  kendo.observable({
            users : source
        });
        kendo.bind($("#users-host"), window.USERS);
        $.serverPost('Users.list_groups',
        {},function (response){
            bind_groups(response);
        })
    }

/**
 * data_load
*/    function data_load(){
        $.serverPost('Users.list_users_with_groups',
        {},function (response){
            bind_users(response);
        })
    
    }
/**
 * MAIN
*/    $(function (){

        $('#user_tab').tab();
        var utab = $.cookie('USER_TAB')||0;
        $('#user_tab a:eq('+ utab +')').tab('show');
         $('.waiter').remove();
        $('#users-editor,.footer').css('visibility','visible');
        data_load();
    })
</script>
