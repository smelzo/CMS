<?php
    $type = querystring('type','news'); // news | rassegna | schede
    //$cats = News::getCategoriesAll($type);
?>
<script type="text/javascript">
    window.current_type = '<?php echo $type?>';
    window.current_lang = '<?php echo current_lang()?>';
    var news_size = <?php echo json_encode($news_thumb_size)?>;
    var lang = '<?php echo current_lang()?>';
</script>
<script type="text/javascript" src="simplex/admin/phpjs.js"></script>
<script type="text/javascript" src="simplex/admin/mustache.js"></script>
<script type="text/javascript" src="simplex/admin/cats.js"></script>
<script id="cat-li-template" type="text/html">
    <li data-id="{{id}}">
       <div class="btn-group" >
         <button class="btn trash-btn" data-role="delete-item">
          <span><i class="icon-trash"></i></span>
        </button>
        <button class="btn caption" data-role="open-item">
            {{caption}}
        </button>
        <button class="btn dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
        </button>
       
        <ul class="dropdown-menu">
            {{#titles}}
            <li>
                <div class="row-fluid">
                    <div class="span1">&nbsp;</div>
                    <div class="span2">
                        <b>{{lang}}</b>
                    </div>
                    <div class="span9">
                        {{content}}
                    </div>
                </div>
            </li>
            {{/titles}}
        </ul>
      </div>
    </li> 
</script>
<script id="mainimage-empty-template" type="text/html">
    <div class="thumb-placeholder thumb-empty" style="height: 240px">
        <i class="icon-camera"></i>
    </div>
</script>
<script id="mainimage-fill-template" type="text/html">
    <div class="thumb" style="height: 240px">
        <span class="remove" data-role="remove-thumbnail"><span><i class="icon-minus"></i></span></span>
       <div class="thumb-image" style="background-image:url({{url}})"></div>
    </div>
</script>

<div class="waiter">
    <div class="inner">
        <i class="icon-cogs"></i>
        <span><?php echo a_('loading')?> ...</span>
    </div>
</div>
<div id="cats-editor" class="full-panel <?php echo $type?> new" style="height: 100%;">
    <div class="splitter" style="">
        <div class="" id="left-pane">
            <div class="pane-content">
                
                <ul id="cats-list" class="nav nav-list">

                </ul>
            </div>
        </div>
        <div class="" id="center-pane">
            <div class="cats-menu">
                <ul class="nav nav-pills" >
                    <li>
                         <button id="btn-new" class="btn">
                            <i class="icon-plus-sign" style="color:#005300;"></i>
                            Aggiungi categoria
                        </button>
                    </li>
                    <li>
                        <button id="btn-save" class="btn btn-primary">
                            <i class="icon-save" ></i>
                            <?php echo a_('save')?>
                        </button>
                    </li>
                    <li>
                         <button id="btn-close"  class="btn">
                            <i class="icon-signout" style="color:#2b2c27;"></i>
                            <?php echo a_('close')?>
                        </button>
                    </li>
                </ul>
            
            </div>
            <div class="pane-content">
                 <div class="page-header" style="margin-top:0;margin-left:10px;padding-bottom:0;margin-bottom:4px">
                    <h3 id="action-display"><span class="edit-mode">Modifica</span><span class="new-mode">Nuovo</span></h3>
                </div>
                <form id="form-cats" class="" data-role="main-form">
                <div class="row-fluid">
                    <div class="span6">
                        <p><label for="">Nome</label></p>
                        <?php
                            foreach($langs as $lg=>$language):
                        ?>
                        <div class="input-prepend" style="font-size: 0;white-space: nowrap">
                            <span class="add-on" style="vertical-align: top;font-size: 12px;font-weight: bold"><?php echo $lg?></span>
                            <input class="span3 field" lang="<?php echo $lg?>" maxlength="50" name="title" type="text" placeholder="<?php echo $language?>"/>
                          </div>
                        <?php
                            endforeach;
                        ?>
                    </div>
                    <div class="span6">
                         <p><label for="">Immagine</label></p>
                        <div id="main-image" class="thumbnail"></div>
                    </div>
                    
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
