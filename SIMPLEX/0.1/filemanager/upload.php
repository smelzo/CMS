<?php
header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
$handler_options = array();
		$max_width = querystring('fmaxw',0,'number');
		$min_width = querystring('fminw',0,'number');
		$max_height = querystring('fmaxh',0,'number');
		$min_height = querystring('fminh',0,'number');
if($max_width) $handler_options['max_width']  = $max_width;
if($min_width) $handler_options['min_width']  = $min_width;
if($max_height) $handler_options['max_height']  = $max_height;
if($min_height) $handler_options['min_height']  = $min_height;
$upload_handler = new UploadHandler($folder,$handler_options);
switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        break;
    case 'HEAD':
    case 'GET':
        $upload_handler->get();
        break;
    case 'POST':
        $upload_handler->post();
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');
}

?>