<?php
$type = querystring('type','news'); // news | rassegna | schede
$cat = querystring('cat');
$typeName = querystring('typeName',$type);
require 'news.types.php';
$cats = News::getCategories($type,current_lang());
$maxImages = $maxGalleryImages; //da config.php
function __def($name,$ucfirst=true,$def=''){
    global $newsTypes,$type;
    if(!$def) $def = $name;
    $v=array_get(array_get($newsTypes,$type,array())
              ,$name
              ,$def);
    if($ucfirst) $v=ucfirst($v);
    echo $v;
}
?>

<script type="text/javascript">
    window.current_type = '<?php echo $type?>';
    window.current_cat = '<?php echo $cat?>';
    window.current_lang = '<?php echo current_lang()?>';
    window.langs = <?php echo json_encode($langs)?>;
    var news_size = <?php echo json_encode($news_thumb_size)?>;
    var lang = '<?php echo current_lang()?>';
    var categories = <?php echo json_encode($cats)?>;
    var maxGalleryImages = <?php echo $maxGalleryImages?>;
    var listAppendType = '<?php __def('listAppendType',false,'prependTo')?>';
    var showDate = <?php __def('showDate',false,'1')?>;
    var showAbstract = <?php __def('showAbstract',false,'1')?>;
    var showCompactList = <?php __def('showCompactList',false,'0')?>;
</script>

<script id="cats-drop-menu-li-template" type="text/html">
    <li>
        <a data-id="{{id}}" data-title="{{title}}" href="#">{{title}}</a>
    </li>
</script>

<script type="text/javascript" src="simplex/admin/jquery.tokeninput.js"></script>
<script type="text/javascript" src="simplex/admin/mustache.js"></script>
<script type="text/javascript" src="simplex/admin/phpjs.js"></script>
<script type="text/javascript" src="simplex/admin/news.js"></script>
<?php
$js_file_name = "news.$type.js";
$js_file_path = dirname(__FILE__) . "/$js_file_name";
if(is_file($js_file_path)) :?>
<script type="text/javascript" src="simplex/admin/<?php echo $js_file_name?>"></script>
<?php endif; ?>
<div class="waiter">
    <div class="inner">
        <i class="icon-cogs"></i>
        <span><?php echo a_('loading')?> ...</span>
    </div>
</div>
<div id="save-msg" class="fixed-msg">
    <p>
        Dati salvati
    </p>
</div>
<div class="header">
    <ul class="nav-header unstyled" style="float:left;margin-left:10px">
        <li>
            <h2><?php echo $typeName?></h2>
        </li>
    </ul>
    <ul class="nav-header unstyled" style="float:right;margin-right:10px">
        <li>
            <label for="lang" style="display:inline-block;vertical-align:middle;padding:2px;padding-left:5px">Lingua:
            <select class="dropdown-langs" name="lang">
                <?php
                    foreach($langs as $lg=>$language):
                    $is_current = ($lg == current_lang());
                ?>
                <option <?php echo $is_current?'selected="selected"':''?> value="<?php echo $lg?>"><?php echo $language?></option>
                <?php
                    endforeach;
                ?>
            </select>
            </label>
        </li>
        <li>
             <button id="btn-new" class="btn">
                <i class="icon-plus-sign" style="color:#005300;"></i>
                <b><?php __def('nuovo') ?></b>
            </button>
        </li>
        <li>
            <button id="btn-save" class="btn btn-primary">
                <i class="icon-save" ></i>
                <?php __def('salva')?>
            </button>
        </li>
        <li>
             <button id="btn-close" onclick="window.location.href='<?php echo makeRequest(array('new'=>null),true,true)?>'" class="btn">
                <i class="icon-signout" style="color:#9B0333;"></i>
                <?php __def('chiudi')?>
            </button>
        </li>

    </ul>

</div>

<div id="news-editor" class="full-panel <?php echo $type?> new" style="height: 90%;top: 10%">
    <div class="splitter" style="">
        
        <div class="" id="left-pane">
            <div class="pane-content">
                <ul id="news-list" class="nav nav-list"></ul>
            </div>
        </div>
        <div class="" id="center-pane">
            <div class="pane-content">
                <div class="page-header" style="margin-top:0;margin-left:10px;padding-bottom:0;margin-bottom:4px">
                    <h3 id="action-display"><span class="edit-mode">Modifica</span><span class="new-mode">Nuovo</span></h3>
                </div>
                <form id="form-news" class="" data-role="main-form">
                        <div class="row-fluid">
                            <div class="span8">
                              <label for="title" style="line-height: 36px;margin: 0"><?php __def('titolo') ?></label> <input name="title" type="text" class="field mandatory full-width k-textbox" >
                            </div>
                            <div class="span4 rassegna-remove">
                                <div class="row-fluid">
                                    <div class="span4">
                                        <label for="title" style="line-height: 36px;margin: 0">Categorie</label>        
                                    </div>
                                    <div class="span8">
                                    <ul class="nav nav-pills" style="margin: 0">
                                      <li><a href="#" class="edit-categories"><i class="icon-pencil"></i>modifica</a></li>
                                      <li class="dropdown">
                                        <a class="dropdown-toggle"  role="button" data-toggle="dropdown" href="#">scegli <b class="caret"></b></a>
                                        <ul id="cats-drop-menu" class="dropdown-menu" role="menu" >
                                           
                                        </ul>
                                      </li>
                                    </ul>
                                    </div>
                                </div>
                                <!--categories-->
                                <div id="categories">
                                    <input id="category-list" type="text" autocomplete="off">
                                </div>
                                
                            </div>
                        </div>
                        
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab-description"  data-toggle="tab">Descrizione</a></li>
                            <li class="edit-mode"><a href="#tab-image" data-toggle="tab">Immagini</a></li>
                            <li><a id="ctrl-contenuto" href="#tab-contenuto" data-toggle="tab"><?php __def('contenuto') ?></a></li>
                            <li class="edit-mode"><a href="#tab-downloads" data-toggle="tab">Downloads</a></li>
                          </ul>
                        <div class="tab-content">
                            <!--tab-description-->
                            <div id="tab-description" class="row-fluid tab-pane active">
                                <div class="span12">
                                    <textarea name="description" class="full-width " style="height:60px"></textarea>
                                </div>
                            </div>
                            <!--tab-image-->
                            <div id="tab-image" class="row-fluid  tab-pane">
                                <div class="span4">
                                    <p><label for="">Immagine principale</label></p>
                                    <div id="main-image" class="thumbnail">
                                    </div>
                                </div>
                                <div class="span8">
                                    <p><label for="">Gallery</label></p>
                                    <ul id="gallery-thumbnails" class="thumbnails ui-sortable">

                                    </ul>
                                </div>
                            </div>
                            <!--tab-contenuto-->
                            <div id="tab-contenuto" class="row-fluid tab-pane">
                                <div class="span12">
                                    <div id="news-content">
                                        <textarea id="news-content-textarea" style="width:100%;height:300px"></textarea>
                                    </div>
                                </div>
                            </div>
                        
                            <div id="tab-downloads" class="row-fluid tab-pane">
                              <div class="span12">
                                <ul id="files-list-header" class="nav nav-tabs nav-stacked">
                                    <div class="file-rows file-header row-fluid">
                                        <div class="span file-actions">
                                            <a class="files-list-add" data-role="tooltip" title="aggiungi file" href="#">
                                                <span class="button-action">
                                                    <span>
                                                        <i class="icon-plus"></i>
                                                    </span>
                                                </span>
                                            </a>
                                        </div>
                                        <div class="span file-pathname">
                                            File
                                        </div>
                                        <div class="span file-name">
                                            Nome visualizzato
                                        </div>
                                        <div class="span1">
                                            Lingua
                                        </div>
                                    </div>
                                </ul>
                                <ul id="files-list" class="nav nav-tabs nav-stacked">
                                </ul>
                              </div>  
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>



<script type="text/javascript">
    function raiseImageError(img,placehold){
        console.log(img.src);
        img.src = placehold;
    }

    $(function (){

        
         
    })
</script>