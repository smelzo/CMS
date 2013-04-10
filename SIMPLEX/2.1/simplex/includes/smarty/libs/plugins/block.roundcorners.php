<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {roundcorners}{/roundcorners} block plugin
 *
 * Type:     block function<br>
 * Name:     roundcorners<br>
 * Purpose:  wrap rounded corners around any content
 *           <br>
 * @link http://smarty.php.net/manual/en/language.function.textformat.php {textformat}
 *       (Smarty online manual)
 * @param array
 * <pre>
 * Params:   style: string (email)
 *           indent: integer (0)
 *           wrap: integer (80)
 *           wrap_char string ("\n")
 *           indent_char: string (" ")
 *           wrap_boundary: boolean (true)
 * </pre>
 * @author Francesco Smelzo<francesco at smelzo dot it>
 * @param string contents of the block
 * @param Smarty clever simulation of a method
 * @return string string $content re-formatted
 */
function _sbr_par(&$params , $name, $default){
	if(!is_array($params)) return $default;
	if(is_array($name)) {
		foreach($name as $nm){
			if(isset($params[$nm])) return _sbr_par($params,$nm,$default);
		}
	}
	if(isset($params[$name])) return $params[$name];
	return $default;
}

function _sbr_url ($baseurl,$type,$radius,$color,$bgcolor,$border,$border_color) {
	if(strpos($baseurl,'?')!==false) {
		$parts = explode('?',$baseurl);
		$baseurl = $parts[0];
	}
	$params = array(
		'l'=>$type,
		'r'=>$radius,
		'c'=>trim($color,'#'),
		'bg'=>trim($bgcolor,'#'),
		'b'=>$border,
		'bc'=>$border_color
	);
	$url_query=array();
	foreach($params as $key=>$value){
		$url_query[]="$key=".urlencode($value);
	}
	return "$baseurl?".implode('&amp;',$url_query);
}

function smarty_block_roundcorners($params, $content, &$smarty){
	$generator_url='';
	if(isset($params['generator_url'])) {
		$generator_url=$params['generator_url'];
	}
	else {
		if(isset($smarty->roundcorners_generator_url)) {
			$generator_url=$smarty->roundcorners_generator_url;
		}
		else {
			return "
			<div style=\"padding:10px;background:#FFFF99;border:1px solid red\">
			<b>roundcorners error</b> - <i>generator_url</i> parameter is undefined.
			you can set this parameter in roundcorners declaration or in \$smarty object:
			<ul style='font:1.0em monospace'>
				<li>In roundcorners declaration<br/>
				<b>{roundcorners generator_url=\"pathtourl/mygenerator.php\"}</b>
				</li>
				<li>in \$smarty object<br/>
				<b>\$smarty->roundcorners_generator_url=\"pathtourl/mygenerator.php\"</b>
				</li>
			</ul>
			Urls can be relative
			</div>
			";
		}
	}
	
	$border = _sbr_par($params,'border',0);
	
	$border_color= _sbr_par($params,array('bc','border_color'),'000000');
	$color= _sbr_par($params,'color','DDDDDD');
	$bgcolor = _sbr_par($params,'bgcolor','FFFFFF');
	$radius = _sbr_par($params,'radius',10);
	if(!is_numeric($border)) $border=0;
	if(!is_numeric($radius)) $radius=10;
	$padding = floor($radius/2);
	$padding = _sbr_par($params,'padding',$padding);
	
	$padding_left = _sbr_par($params,'padding_left',$padding);
	$padding_top = _sbr_par($params,'padding_top',$padding);
	$padding_bottom = _sbr_par($params,'padding_bottom',$padding);
	$padding_right = _sbr_par($params,'padding_right',$padding);
	
	$tl_bgcolor = _sbr_par($params,'tl_bgcolor',$bgcolor);
	$tr_bgcolor = _sbr_par($params,'tr_bgcolor',$bgcolor);
	$bl_bgcolor = _sbr_par($params,'bl_bgcolor',$bgcolor);
	$br_bgcolor = _sbr_par($params,'br_bgcolor',$bgcolor);
	
	$tl_radius = _sbr_par($params,'tl_radius',$radius);
	$tr_radius = _sbr_par($params,'tr_radius',$radius);
	$bl_radius = _sbr_par($params,'bl_radius',$radius);
	$br_radius = _sbr_par($params,'br_radius',$radius);
	
	
	$tl_url = _sbr_url($generator_url,'TL',$tl_radius,$color,$tl_bgcolor,$border,$border_color);
	$tr_url = _sbr_url($generator_url,'TR',$tr_radius,$color,$tr_bgcolor,$border,$border_color);
	$bl_url = _sbr_url($generator_url,'BL',$bl_radius,$color,$bl_bgcolor,$border,$border_color);
	$br_url = _sbr_url($generator_url,'BR',$br_radius,$color,$br_bgcolor,$border,$border_color);
	$border_url = ($border)?_sbr_url($generator_url,'BORDER',0,$color,'000',$border,$border_color):'';
	
	$css_all_elements="width: auto;margin: 0;padding: 0;border: 0;position: relative;";
	
	$css_corners_t ="$css_all_elements
	background-color: #$color;
    background-image: url($border_url);
    background-repeat: repeat-x;
    background-position: top;";
	
	$css_corners_tl="$css_all_elements
	height: 100%;
    background-image: url($tl_url);
    background-repeat: no-repeat;
    background-position: left top;";

	$css_corners_tr="$css_all_elements
	height: 100%;
    background-image: url($tr_url);
    background-repeat: no-repeat;
    background-position: right top;";
	
	$css_corners_bl="$css_all_elements
	height: 100%;
    background-image: url($bl_url);
    background-repeat: no-repeat;
     background-position: left bottom;";
	
	$css_corners_br="$css_all_elements
	height: 100%;
    background-image: url($br_url);
    background-repeat: no-repeat;
    background-position: right bottom;";

	$css_corners_l="$css_all_elements
    height: 100%;
    background-image: url($border_url);
    background-repeat: repeat-y;
    background-position: left;";
	
	$css_corners_r="$css_all_elements
    height: 100%;
    background-image: url($border_url);
    background-repeat: repeat-y;
    background-position: right;";
	
	$css_corners_b="$css_all_elements
    height: 100%;
    background-image: url($border_url);
    background-repeat: repeat-x;
    background-position: bottom;";

	$css_corners="$css_all_elements
	height: 100%;
	padding-top:{$padding_top}px;
	padding-left:{$padding_left}px;
	padding-right:{$padding_right}px;
	padding-bottom:{$padding_bottom}px;
	";
	
	$div='<div style="%s" class="%s">';
	$div_close='</div>';
	$out = array(
		sprintf($div,$css_corners_t,_sbr_par($params,'t_class','empty_class')),
		sprintf($div,$css_corners_l,_sbr_par($params,'l_class','empty_class')),
		sprintf($div,$css_corners_r,_sbr_par($params,'r_class','empty_class')),
		sprintf($div,$css_corners_b,_sbr_par($params,'b_class','empty_class')),
		sprintf($div,$css_corners_tl,_sbr_par($params,'tl_class','empty_class')),
		sprintf($div,$css_corners_tr,_sbr_par($params,'tr_class','empty_class')),
		sprintf($div,$css_corners_bl,_sbr_par($params,'bl_class','empty_class')),
		sprintf($div,$css_corners_br,_sbr_par($params,'br_class','empty_class')),
		sprintf($div,$css_corners,_sbr_par($params,'c_class','empty_class')),
		$content,
	);
	$result = implode("\r\n",$out) . str_repeat($div_close,count($out)-1) ;
	return $result;
}

?>