 <?php require APPLICATION_PATH._."admin"._."admin.head.php";?>
<script type="text/javascript" src="simplex/scripts/login.js"></script>
<link rel="stylesheet" type="text/css" href="simplex/css/login.css"/>
<?php if ($user = user()) :
    if ($user['power']>0) :
        require APPLICATION_PATH._."admin"._."admin.layer.php";
     endif;
else :?>
<a id="login-show-link" href="#" rel="tooltip" data-placement="top" title="[:accedi:]">
       <i class="icon-user icon-white"></i>
</a>
<div id="login-panel" class="login hide">
    <form id="login-form" action="#">
        <div class="row-fluid">
            <div class="span4">
               <label for="email"><i class="icon-user" style="font-size:24px"></i>&nbsp;[:email:]</label>
            </div>
            <div class="span8">
                <input name="email" type="text" class="mandatory" value=""/>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span4">
               <label for="password"><i class="icon-lock" style="font-size:24px"></i>&nbsp;[:password:]</label>
            </div>
            <div class="span8">
                <input name="password" type="password" class="mandatory"  value=""/>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span8">
               &nbsp;
            </div>
            <div class="span4" style="text-align:right">
                <button class="btn btn-primary">
                     <i class="icon-signin" style="font-size:18px"></i>
                     &nbsp;
                    [:go:]
                   
                </button>
            </div>
        </div>
    </form>
</div>

<?php endif;?>
<script type="text/javascript">
    $(function (){
       $('[rel=tooltip]').tooltip();
    })
</script>