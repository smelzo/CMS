    <!--admin.head-->
    <script type="text/javascript" src="simplex/cdn/jquery1.7.min.js"></script>
    <script type="text/javascript" src="simplex/cdn/jquery-plus-min.js"></script>
    <script type="text/javascript" src="simplex/cdn/bootstrap.min.js"></script>    
    
    <script language="javascript" type="text/javascript" src="simplex/cdn/jquery-ui.min.js"></script>
    <script language="javascript" type="text/javascript" src="simplex/cdn/jquery-ui-i18n.min.js"></script>
    <link rel="stylesheet" type="text/css" href="simplex/cdn/ui-theme/jquery-ui-1.8.16.custom.css"/>
    
    <!--START Kendo-->
    <script type="text/javascript" src="simplex/cdn/kendo/js/kendo.web.min.js"></script>
    <script type="text/javascript" src="simplex/cdn/kendo/js/cultures/kendo.culture.it-IT.min.js"></script>
    <link href="simplex/cdn/kendo/styles/kendo.common.min.css" rel="stylesheet" />
    
    <link href="simplex/cdn/kendo/styles/kendo.default.min.css" rel="stylesheet" />
    <!--END Kendo-->
    
    <script type="text/javascript" src="simplex/scripts/splitter.js"></script>
    <script type="text/javascript" src="simplex/scripts/main.js"></script>
    <script type="text/javascript" src="simplex/scripts/admin.js"></script>
    
     <link rel="stylesheet" type="text/css" href="simplex/cdn/css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="simplex/cdn/font-awesome.css"/>
    <link rel="stylesheet" type="text/css" href="simplex/css/open-sans/stylesheet.css"/>
    <link rel="stylesheet" type="text/css" href="simplex/css/admin.css"/>
    <!--end admin.head-->
    <?php
    if(isset($AdminResourceHandler)){
        echo "\n",$AdminResourceHandler->js_string();
    }
    ?>