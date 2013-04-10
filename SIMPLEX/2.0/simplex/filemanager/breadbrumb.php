<?php
    $c_parents = count($folder->parents);
?>
<ul id="breadcrumb" data-count="<?php echo $c_parents?>">
<?php

foreach($folder->parents as $i=>$d):
$last = ($i==$c_parents-1);
?>
    <li data-index="<?php echo $i?>" class="<?php if ($last) echo 'active'?>">
    
        <a href="<?php echo $d->link?>" title="<?php echo $d->url?>">
            <?php echo $d->name?>
        </a>
        <?php if (!$last) :?>
        <span class="divider"><i class="icon-chevron-right"></i></span>
        <?php endif ;?>
    </li>
<?php endforeach; ?>
</ul>
<style type="text/css" media="all">
    #breadcrumb {
        display:block;
        height:30px;
        margin:0;
        padding:0 10px;
        font-family:'Lucida Grande','Segoe UI';
        list-style: none;
    }
    #breadcrumb li{
        display: inline-block;
        vertical-align: middle;
        line-height:30px;
    }
    #breadcrumb a {
        display: inline-block;
        vertical-align: middle;
        height:30px;
        line-height:20px;
    }
    #breadcrumb .divider{
        display: inline-block;
        vertical-align: middle;
        font-size: 20px;
        height:25px;
        line-height:20px;
        margin-top:3px;
        color:#f5790a;
         /*line-height:30px;*/
    }
</style>
