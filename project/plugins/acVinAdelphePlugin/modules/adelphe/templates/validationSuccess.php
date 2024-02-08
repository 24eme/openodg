<?php include_partial('adelphe/breadcrumb', array('adelphe' => $adelphe )); ?>

<?php include_partial('adelphe/step', array('step' => 'validation', 'adelphe' => $adelphe)) ?>
<div class="page-header">
    <h2>Validation <small>de votre déclaration</small></h2>
</div>


<?php include_partial('adelphe/recap', array('adelphe' => $adelphe)); ?>

<form action="<?php echo url_for("adelphe_validation", $adelphe) ?>" method="post" class="form-horizontal">
    <div class="row row-margin row-button pt-3">
    <div class="col-xs-6"><a href="<?php echo ($adelphe->conditionnement_bib)? url_for("adelphe_repartition_bib", $adelphe) : url_for("adelphe_volume_conditionne", $adelphe) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-6 text-right">
              <button type="submit" class="btn btn-primary btn-upper"><?php if ($adelphe->redirect_adelphe): ?>Déclarer sur le site de l'ADELPHE <span class="glyphicon glyphicon-chevron-right"></span><?php else: ?>Valider la déclaration<?php endif; ?></button>
        </div>
    </div>
</form>
