<h2 class="h2-border">Récapitulatif</h2>
<div class="row">
    <div class="col-md-6">
        <?php include_partial('drev/revendication', array('drev' => $drev)); ?>
    </div>
    <div class="col-md-6">
        <?php include_partial('drev/prelevements', array('drev' => $drev)); ?>
    </div>
</div>