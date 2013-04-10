<script type="application/javascript" src="simplex/scripts/config.js.php"></script>
<script type="text/javascript" src="simplex/scripts/admin.js"></script>
<link rel="stylesheet" type="text/css" href="simplex/css/admin.css"/>

<div id="admin-toolbar">
    <ul id="admin-menu">
        <li class="hide first"></li>
        <li class="last">
            <a id="logout-link" href="#">
                <i class="icon-signout"></i>
                <span>[:logout:]</span>
            </a>            
        </li>
    </ul>
</div>
<script type="text/javascript">
    add_onload(function (){
        toolbar_setup();
        $('#admin-toolbar').css('z-index',maxZ());
    })
</script>