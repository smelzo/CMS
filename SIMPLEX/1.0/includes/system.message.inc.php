<!DOCTYPE html>
<html>
<head>
    <title><?php echo getGlobal('system_title','Simplex message')?></title>
    <script type="text/javascript" src="cdn/jquery/1.7/jquery.min.js"></script>
    <script type="text/javascript" src="cdn/jquery/plugins/jquery-plus-min.js"></script>
    <link rel="stylesheet" type="text/css" href="cdn/bootstrap/css/bootstrap.min.css"/>
    <script type="text/javascript" src="cdn/bootstrap/js/bootstrap.min.js"></script>    
</head>
<body>
    <div class="alert" style="margin:100px auto; width:600px">
        <?php echo getGlobal('system_message','Simplex error!') ?>
    </div>
</body>
</html>