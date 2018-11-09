<div class="row">
    <div class="col-xs-12">
        <?php include_partial('drev/revendication', array('drev' => $drev)); ?>
    </div>
    <?php if($drev->declaration->hasVolumeRevendiqueVci()): ?>
    <div class="col-xs-12">
        <?php include_partial('drev/vci', array('drev' => $drev)); ?>
    </div>
    <?php endif; ?>
    <div class="col-xs-12">
        <?php include_partial('drev/prelevements', array('drev' => $drev)); ?>
    </div>
</div>