<h2 class="h2-border">Récapitulatif</h2>
<div class="row">
    <div class="col-xs-12">
        <?php include_partial('drev/revendication', array('drev' => $drev)); ?>
    </div>
    <div class="col-xs-12">
        <?php include_partial('drev/prelevements', array('drev' => $drev)); ?>
    </div>
</div>