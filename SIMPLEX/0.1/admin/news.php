<?php
$news = News::grabNews();
$current = null;
function vl($name){
    global $current;
    if(!$current) return  "";
    else return  array_get($current,$name,'');
}
function vl_($name){
    echo vl($name);
}

function decore_current(){
    global $current;
    //$str = file_get_contents($current['filename']);
    $doc = phpQuery::newDocumentFileHTML($current['filename']);
    $node =  $doc->find('details[lang=' . current_lang() . ']');
    $current['content'] = "";
    if($node->length){
        $current['content'] = $node->html();
    }
}
?>

<style type="text/css" media="all">
#left-pane .pane-content {
    padding: 10px 0;
}
</style>
<div class="waiter">
    <div class="inner">
        <i class="icon-cogs"></i>
        <span><?php echo a_('loading')?> ...</span>
    </div>
</div>
<div id="news-editor" class="full-panel" style="height: 90%;">
    <div class="splitter" style="">
        <div class="" id="left-pane">
            <div class="pane-content">
                <ul class="nav nav-list">
                    <?php
                    foreach($news as $n):
                        extract($n);
                        $basename = basename($filename);
                        $active=false;
                        if($name==querystring('new')) {
                            $current=$n;
                            decore_current();
                            $active=true;
                        }
                        $link = makeRequest(array('new'=>$name),true,true);
                    ?>
                    <li style="margin-bottom:10px" class="<?php echo $active?'active':'' ?>">
                    <div class="block">
                         <h4><a href="<?php echo $link?>"><?php echo $title?$title:a_('senza_titolo') ?></a></h4>
                        <div class="block-body">
                        <p>
                            <a href="<?php echo $link?>"><?php echo date('d/m/Y', $creation)?></a>
                        </p>
                        <p>
                            <a href="<?php echo $link?>"><?php echo str_truncate(strip_tags($description)) ?></a>
                        </p>
                        </div>
                        <div class="block-footer">
                          <button data-filename="<?php echo $filename?>"  type="button" class="trash-item" >
                              <i class="icon-trash"></i>
                          </button>
                        </div>
                      </div>
                        <div class="row-fluid hide">
                            <div class="span9">
                        <a href="<?php echo makeRequest(array('new'=>$name),true,true)?>">
                            <b><?php echo $title?></b>
                            <?php echo date('d/m/Y', $creation)?>
                            <?php
                            echo str_truncate(strip_tags($description))
                            ?>
                        </a>
                        </div>
                        <div class="span3">
                            <button type="button"  data-filename="<?php echo $filename?>" class="btn btn-small btn-danger trash-item">
                                <i class="icon-trash" style="font-size:18px;"></i>
                            </button>
                        </div>
                        </div>
                    </li>
                    <?php endforeach;?>
                </ul>
               
            </div>
        </div>
        <div class="" id="center-pane">
            <div class="pane-content">
                <div class="page-header" style="margin-top:0;margin-left:10px;padding-bottom:0;margin-bottom:4px">
                    <h3><?php echo (!$current)?'Nuova':'Modifica';?>&nbsp;News </h3>
                </div>
                <form id="form-news" class=".form-vertical">
                <div  style="padding:0 10px"> 
                        <div class="row-fluid">
                            <div class="span8">
                                <p><label for="title">Titolo</label> <input name="title" type="text" class="field mandatory full-width k-textbox" value="<?php vl_('title')?>"></p>        
                            </div>
                            <div class="span4">
                                <p><label for="datetime">Data</label> <input name="creation" type="text" class="field mandatory datepicker" value="<?php vl_('creation_str')?>"></p>
                            </div>
                        </div>
                        <div class="row-fluid">
                            <div class="span6">
                        <p><label for="description">Descrizione</label></p>
                        <textarea name="description" class="full-width " style="height:60px">
        <?php vl_('description')?>
        </textarea>
                            </div>
                            <div class="span6">
                                <p><label for="">Immagine</label></p>
                                <table cellspacing="4" cellpadding="0" class="k-widget k-editor k-header" style="width: 100%; height: auto;">
                                    <tbody>
                                        <tr>
                                            <td class="k-editor-toolbar-wrap">
                                                <ul class="k-editor-toolbar">
                                                    <li class="k-editor-button" >
                                                        <a title="Imposta immagine" data-hover="k-state-hover" unselectable="on" class="k-tool-icon k-insertImage insert-image-link" href="">Imposta immagine</a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="k-editable-area" valign="middle" align="center" style="padding:13px 0">
                                                <?php
                                                $placehold = "http://placehold.it/".$news_thumb_size['width']."/".$news_thumb_size['height'];
                                                $style = "width:". $news_thumb_size['width']."px;height:".$news_thumb_size['height']."px";
                                                ?>
                                                <?php if(vl('photo')) : ?>
                                                <img class="photo insert-image-link pointer" onerror="raiseImageError(this,'<?php echo $placehold?>')"
                                                                                                                             src="<?php vl_('photo')?>" >
                                                <?php else : ?>
                                                <img class="photo insert-image-link pointer" src="<?php echo $placehold?>" style="<?php echo $style?>">
                                                <?php endif ; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                   
                
                </div>
                <div style="padding:10px">
                    <p><label for="content">Contenuto</label></p>
                    <div id="news-content">
                    <?php if($current && isset($current['content'])):?>
                        <?php vl_('content') ?>
                    <?php else: ?>
                        <section class="hide" itemtype="#news" itemscope="itemscope">
                            <img src="" itemprop="photo">
                            <?php  foreach($langs as $lg=>$language): ?>
                                <var itemprop="title" lang="<?php echo $lg?>"></var>
                                <var itemprop="description" lang="<?php echo $lg?>"></var>
                            <?php endforeach; ?>                                
                            <datetime itemprop="creation"></datetime>
                        </section>
                    <?php endif;?>
                    </div>
                </div>
                
                </form>
            </div>
        </div>
    </div>
</div>
<div class="footer">
    <ul class="nav-footer unstyled" style="float:left;margin-left:10px">
        <li>
            <label for="lang" style="display:inline-block;vertical-align:middle;background:#eee;border:1px solid #ccc;padding:2px;padding-left:5px">Lingua:
            <select class="dropdown" name="lang">
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
        
    </ul>
    <ul class="nav-footer unstyled" style="float:right;margin-right:10px">
        <li>
             <button id="btn-new" onclick="window.location.href='<?php echo makeRequest(array('new'=>null),true,true)?>'" class="btn">
                <i class="icon-plus-sign" style="color:#005300;"></i>
                Aggiungi News
            </button>
        </li>
        <li>
            <button id="btn-save" class="btn btn-primary">
                <i class="icon-save" ></i>
                <?php echo a_('save')?>
            </button>
        </li>
        <li>
             <button id="btn-close" onclick="window.location.href='<?php echo makeRequest(array('new'=>null),true,true)?>'" class="btn">
                <i class="icon-signout" style="color:#2b2c27;"></i>
                <?php echo a_('close')?>
            </button>
        </li>

    </ul>

</div>


<script type="text/javascript">
function raiseImageError(img,placehold){
    console.log(img.src);
    img.src = placehold;
}
    var news_size = <?php echo json_encode($news_thumb_size)?>;
    var filename = <?php echo json_encode(vl('filename'))?>;
    var lang = '<?php echo current_lang()?>';
    var save = function (){
        //proprietà
        var f = $('#form-news');
        if(!validate_form(f)) return ;
        
        var data = f.collectData();
        
        var description = f.find('textarea[name=description]').data("kendoEditor").value();
        if(!description) {
            view_error($('label[for=description]'),MESSAGES.mandatory);
            return ;
        }
        data.description = description;
        //var _description = sectionProps.find('[itemprop=description][lang=' + lang + ']');
        //if(!_description.length) {
        //    _description = $('<var itemprop="description" lang=' + lang + '></var>').prependTo(sectionProps)
        //}
        //_description.html(description);
        
        //var _title = sectionProps.find('[itemprop=title][lang=' + lang + ']');
        //if(!_title.length) {
        //    _title = $('<var itemprop="title" lang=' + lang + '></var>').prependTo(sectionProps)
        //}
        //_title.html(data.title);
        
        //sectionProps.find('datetime[itemprop=creation]').html($('#news-editor [name=creation]').val());
        
        data.creation = $('#news-editor [name=creation]').val();
        data.photo = $('#news-editor img.photo').attr('src');
        //var section_html =  $('<div></div>').append(sectionProps.clone()).html();
        var content = content_editor.data("kendoEditor").value();
        if(!content) {
            view_error($('label[for=content]'),MESSAGES.mandatory);
            return ;
        }
        data = {section:data, content : content, filename:filename}
        $.serverPost('News.edit',{data : $.encode(data)},function (response){
            if(response) {
                window.location.reload();
                //$('#btn-new').click();
            }
        })
    }
    $(function (){
        $('[data-hover]').each(function (){
            $(this).mouseenter(function (){
                $(this).addClass($(this).attr('data-hover'));
            })
            $(this).mouseleave(function (){
                $(this).removeClass($(this).attr('data-hover'));
            })
        })
        $('#form-news').submit(function (event){
            event.preventDefault();
            save();
        });
        $('#btn-save').click(function (){
            $('#form-news').submit();
        });
        $('#btn-close').click(function (){
            window.top.close_news_window();
        });
        $('.trash-item').click(function (){
            if(confirm('Stai per cancellare una News.Confermi?')){
                $.serverPost('News.remove',{filename:$(this).attr('data-filename')},function (){
                    $('#btn-new').click();
                })
            }
        })
        $('.insert-image-link').click(function (event){
            event.preventDefault();
            //var win = window.top.open_images_dialog(null,{
            var win = open_images_dialog(null,{
                modal:true
                ,url_params:{
                    fminw:news_size.width
                    ,fminh:news_size.height
                }
            });
            window.pick_file = function (file){
                if(!file.is_image) {
                    alert('il file non è un\'immagine');
                    return ;
                }
                if(file.width < news_size.width || file.height< news_size.height) {
                    alert('l\'immagine deve avere dimensioni di almeno ' +  news_size.width + 'x' + news_size.height + ' pixel');
                    return ;
                }
                var link_get = file.link_get +  '&w=' + news_size.width + '&h=' + news_size.height ;
                $('#news-editor img.photo').attr('src' , link_get).css({width:'auto',height:'auto'});
                //sectionProps.find('[itemprop=photo]').attr('src' , link_get);
                win.destroy()
            }
            //window.top.WHEN_FILE_PICKED = function (file){
            //    
            //}
        })
        $('.splitter').kendoSplitter({
            panes:[
                {collapsible:true,size:'260px',min:'260px'}
                ,{collapsible:false}
            ]
        });
        var dropdown = $('.dropdown').kendoDropDownList({
            change : function (e){
                var href = makeRequest({lg:this.value()});
                window.location.href = href;
            }
        });
        $('#news-editor textarea[name=description]').kendoEditor({
            culture:'it-IT',
            tools:[
                "bold",
                "italic",
                "underline",                
            ]
            
        });
        $('#news-editor .datepicker').each(function (){
            $(this).kendoDateTimePicker ({
                culture:'it-IT'
                ,format:'yyyy-MM-dd HH:mm:ss'
                ,value:new Date
            })
        });
        
        //trova e mette da parte section
        sectionProps = $('#news-content section:first').addClass('hide').appendTo('body');
        
        content_textarea = $('<textarea></textarea>').css({width:'100%',height:'300px'}).insertAfter('#news-content');
        content_textarea.val($.trim($('#news-content').html()));
        content_editor = content_textarea.kendoEditor({
            culture:'it-IT',
            imageDialog : false,
            tools:[
                "bold",
                "italic",
                "underline",
                "fontSize",
                "justifyLeft",
                "justifyCenter",
                "justifyRight",
                "justifyFull",
                "insertUnorderedList",
                "insertOrderedList",
                "indent",
                "outdent",
                "formatBlock",
                "createLink",
                 "unlink",
                 "viewHtml",
                 "insertImage"
            ]
            ,stylesheets : [
                "css/editor.css",
                "css/page.css"
            ]
        });
        $('#news-content').remove();
        $('.waiter').remove();
        $('#news-editor,.footer').css('visibility','visible');
         
    })
</script>