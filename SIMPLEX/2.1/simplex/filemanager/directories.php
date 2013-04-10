<div class="subnavbar">
    <div class="subnavbar-inner">
        <div class="container">
            <ul>
                <?php if(!$folder->is_root) :
                    $c = count($folder->parents);
                    $d = $folder->parents[$c-2];
                ?>
                    <li>
                        <a href="<?php echo $d->link?>" title="<?php echo $d->url?>">
                        <i class="icon-circle-arrow-left icon-red"></i>
                        <span><?php echo fm_('back')?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="#" data-action="actions.add_folder" title="<?php echo fm_('add_folder')?>" >
                        <i class="icon-plus-sign icon-green"></i>
                    </a>
                </li>
                <li>
                    <a href="#" data-action="actions.delete_folders" title="<?php echo fm_('delete_folders')?>" >
                        <i class="icon-trash icon-red"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>


<ul id="directory-list" class="unstyled list-controller" style="position:relative;">
<?php foreach($folder->directories as $d):?>
    <li class="list-item directory-item clearfix">
        <i class="icon icon-folder-close"></i>
        <span class="name"><?php echo $d->name?></span>
        <a href="<?php echo $d->link?>" class="link hide"></a>
    </li>
<?php endforeach;?>
<?php if (!$folder->directories) : ?>
    <li class="no-directory-item">
        <?php echo fm_('no_folders')?>
    </li>
<?php endif; ?>
</ul>
<script type="text/javascript">
    $(function (){
        $('#directory-list').listController({
            item_to_object : function (item){
                return $.trim( $(item).find('.name').text())
            }
            ,dblclick : function (item,event){
                window.location.href =   $(item).find('a.link:first').attr('href');
            }
        });
        //$('#directory-list').listController('select',2);
        
    })
</script>