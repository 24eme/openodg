<?php include_partial('adelphe/breadcrumb', array('adelphe' => $adelphe )); ?>

<?php include_partial('adelphe/step', array('step' => 'repartition_bib', 'adelphe' => $adelphe)) ?>
<div class="page-header">
    <h2>Répartition du volume <small>conditionné en BIB</small></h2>
</div>

<p>Veuillez répartir votre volume conditonné</p>

<form action="<?php echo url_for("adelphe_repartition_bib", $adelphe) ?>" method="post" class="form-horizontal">
  <?php echo $form->renderHiddenFields(); ?>
  <div class="row row-margin">
    <div class="col-xs-12">

    </div>
  </div>

  <div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("adelphe_volume_conditionne", $adelphe); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
    <div class="col-xs-6 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
  </div>
</form>
