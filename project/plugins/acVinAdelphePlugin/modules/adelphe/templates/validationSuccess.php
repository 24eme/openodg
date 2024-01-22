<?php include_partial('adelphe/breadcrumb', array('adelphe' => $adelphe )); ?>

<?php include_partial('adelphe/step', array('step' => 'validation', 'adelphe' => $adelphe)) ?>
<div class="page-header">
    <h2>Validation</h2>
</div>

<?php if ($adelphe->redirect_adelphe): ?>

<?php include_partial('adelphe/redirect', array('adelphe' => $adelphe)); ?>
<form action="<?php echo url_for("adelphe_validation", $adelphe) ?>" method="post" class="form-horizontal">
    <div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("adelphe_volume_conditionne", $adelphe) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-6 text-right">
              <button type="submit" class="btn btn-primary btn-upper">Valider et continuer sur le site de l'ADELPHE<span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>

<?php else: ?>
<?php include_partial('adelphe/recap', array('adelphe' => $adelphe)); ?>

<form action="<?php echo url_for("adelphe_validation", $adelphe) ?>" method="post" class="form-horizontal">
    <div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("adelphe_repartition_bib", $adelphe) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-6 text-right">
              <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
</form>
<?php endif; ?>
