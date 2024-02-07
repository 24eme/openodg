<?php include_partial('adelphe/breadcrumb', array('adelphe' => $adelphe )); ?>

<?php include_partial('adelphe/step', array('step' => 'volume_conditionne', 'adelphe' => $adelphe)) ?>
<div class="page-header">
    <h2>Volume conditionné <small>pour l'année <?php echo $adelphe->getPeriode() ?></small></h2>
</div>

<p>Ce chiffre correspond au volume conditionné mis en marché en France uniquement et hors vrac. Les ventes à l’export ne doivent pas être inclues dans ce chiffre.</p>
<p class="mb-5">Toute modification des données pre-saisies engage votre responsabilité. Le Syndicat et InterRhone ne pourront être responsables de données inexactes transmises.</p>

<form action="<?php echo url_for("adelphe_volume_conditionne", $adelphe) ?>" method="post" class="form-horizontal">
  <?php echo $form->renderHiddenFields(); ?>
  <div class="row row-margin pb-5">
    <div class="col-xs-12">
      <div class="form-group">
          <?php echo $form["volume_conditionne_total"]->renderError(); ?>
          <?php echo $form["volume_conditionne_total"]->renderLabel("Volume total conditonné en ".$adelphe->getPeriode(), array("class" => "col-xs-4 control-label")); ?>
          <div class="col-xs-2" style="padding-right: 5px">
              <?php echo $form["volume_conditionne_total"]->render(array("class" => "form-control text-right")); ?>
          </div>
          <div class="col-xs-1 text-left" style="padding: 7px 0 0;">
             <span class="text-muted">hl</span>
          </div>
      </div>
    </div>
  </div>

  <div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $adelphe->identifiant, 'campagne' => $adelphe->campagne)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à mon espace</a></div>
    <div class="col-xs-6 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
  </div>
</form>
