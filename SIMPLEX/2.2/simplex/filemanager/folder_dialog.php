    <div id="add_folder_dialog" class="hide" title="<?php echo fm_('add_folder')?>">
        <form method="post" action="#" class="form-horizontal">
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="name"><?php echo fm_('name')?></label>
                    <div class="controls">
                      <input type="text" class="input-xlarge" id="name" name="name">
                    <p class="help-block">
                        In <?php echo $folder->url?>
                    </p>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="form-actions-inner">
                        <button type="submit" class="btn btn-primary"><?php echo fm_('ok')?></button>
                        <button type="button" class="btn dialog-close"><?php echo fm_('cancel')?></button>
                    </div>
              </div>
            </fieldset>
        </form>
    </div>
