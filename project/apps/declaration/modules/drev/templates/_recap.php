<div class="row">
    <div class="col-xs-12">
        <?php include_partial('drev/revendication', array('drev' => $drev)); ?>
    </div>
    <div class="col-xs-12">
        <?php include_partial('drev/prelevements', array('drev' => $drev)); ?>
    </div>
    <div class="col-xs-12">
    	<?php include_partial('drev/documents', array('drev' => $drev, 'form' => isset($form) ? $form : null)); ?>
    </div>
</div>