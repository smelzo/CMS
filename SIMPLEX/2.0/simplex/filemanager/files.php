<script src="scripts/upload/js/vendor/jquery.ui.widget.js"></script>
<script src="scripts/upload/js/jquery.iframe-transport.js"></script>
<script src="scripts/upload/js/jquery.fileupload.js"></script>
<script type="text/javascript">
    var icons_list = <?php echo json_encode( get_icons_list() ) ?>;
    var HOST_PICK_FILE_FUNCTION_NAME = '<?php echo querystring('fx','pick_file')?>';
</script>
<script type="text/javascript" src="scripts/files.js"></script>


<div id="subnavbar-files" class="subnavbar">
    <div class="subnavbar-inner">
        <div class="container">
            <ul class="nav">
                <li>
                    <a href="#" id="file_panel_toggler" title="<?php echo fm_('add_file')?>" >
                        <i class="icon-plus-sign icon-green"></i>
                        <span><?php echo fm_('add_file')?></span>
                    </a>
                </li>

                <li>
                    <span>
                        <input id="file-filter" placeholder="<?php echo fm_('search')?>..."  type="text" class="textbox" onblur="file_filter()" value=""/>
                        <i class="icon icon-search unselectable" unselectable="on" style="font-size: 22px;color:#666;cursor:pointer" onclick="file_filter()"></i>
                    </span>
                </li>
                <li>
                    <div class="group-buttons" id="view_pref_settings">
                        <a href="#" data-value="large" rel="tooltip" title="<?php echo fm_('large_view')?>"><i class="icon-th-large"></i></a>
                        <a href="#" data-value="compact" rel="tooltip" title="<?php echo fm_('details')?>"><i class="icon-th-list"></i></a>
                    </div>
                </li>
            </ul>
            
        </div>
    </div>
</div>
<div id="files">

    <ul id="files-list" class="unstyled list-controller" style="position:relative;">
        <?php foreach($folder->files as $f):?>
        <li class="list-item file-item not-load" data-set="<?php echo htmlspecialchars (json_encode($f))?>">
            &nbsp;
        </li>
        <?php endforeach;?>
        <li class="no-files-item <?php if ($folder->files) echo 'hide' ?>">
            <?php echo fm_('no_files')?>
        </li>
    </ul>
</div>
