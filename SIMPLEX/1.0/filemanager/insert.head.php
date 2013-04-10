    <script type="text/javascript" src="../../simplex/cdn/jquery.min.js"></script>
    <script type="text/javascript" src="../../simplex/cdn/jquery-plus-min.js"></script>
    <script type="text/javascript" src="../../simplex/cdn/bootstrap.min.js"></script>    
    
    <script language="javascript" type="text/javascript" src="../../simplex/cdn/jquery-ui.min.js"></script>
    <script language="javascript" type="text/javascript" src="../../simplex/cdn/jquery-ui-i18n.min.js"></script>
    <link rel="stylesheet" type="text/css" href="../../simplex/cdn/ui-theme/jquery-ui-1.8.16.custom.css"/>
    
    <!--START Kendo-->
    <script type="text/javascript" src="../../simplex/cdn/kendo/js/kendo.web.min.js"></script>
    <script type="text/javascript" src="../../simplex/cdn/kendo/js/cultures/kendo.culture.it-IT.min.js"></script>
    <link href="../../simplex/cdn/kendo/styles/kendo.common.min.css" rel="stylesheet" />
    <link href="../../simplex/cdn/kendo/styles/kendo.metro.min.css" rel="stylesheet" />
    <!--END Kendo-->
    
    <script type="text/javascript" src="../../simplex/scripts/splitter.js"></script>
    <script type="text/javascript" src="../../simplex/scripts/main.js"></script>
    <script type="text/javascript" src="../../simplex/scripts/admin.js"></script>
    
    <link rel="stylesheet" type="text/css" href="../../simplex/cdn/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="../../simplex/cdn/font-awesome.css"/>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600" rel="stylesheet" type="text/css">
<!--Insert stardard interface-->
<!--<link rel="stylesheet" type="text/css" href="weext/cdn/jqueryui/1.8/themes/weext/jquery-ui.css"/>-->

<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<script type="text/javascript">
    var ADMIN_RESOURCES = <?php echo json_encode(get_filemanager_resources())?>;
    //console.log(ADMIN_RESOURCES);
    function ad_(name,def){
       if(!def)def= name;
        if(ADMIN_RESOURCES[name]) return ADMIN_RESOURCES[name];
        return def;
    }
</script>
        