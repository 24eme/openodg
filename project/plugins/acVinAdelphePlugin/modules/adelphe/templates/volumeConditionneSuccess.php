<?php include_partial('adelphe/breadcrumb', array('adelphe' => $adelphe )); ?>

<?php include_partial('adelphe/step', array('step' => 'volume_conditionne', 'adelphe' => $adelphe)) ?>
<div class="page-header">
    <h2>Volume conditionné <small>pour l'année <?php echo $adelphe->getPeriode() ?></small></h2>
</div>

<p>Le volume conditionné annuel est repris et calculé depuis vos DRM disponibles sur DeclarVins.
<br /><br />Veuillez vérifier sa véracité et au besoin le saisir ou le corriger.</p>

<form action="<?php echo url_for("adelphe_volume_conditionne", $adelphe) ?>" method="post" class="form-horizontal">
  <?php echo $form->renderHiddenFields(); ?>
  <div class="row row-margin">
      <div class="col-xs-12 ml-2">
          <p><span class="glyphicon glyphicon-info-sign"></span> Si votre volume total conditionné est supérieur a <strong><?php echo $adelphe->getMaxSeuil() ?> hl</strong>, vous serez redirigé automatiquement sur le site de l'Adelphe après avoir cliqué sur 'Valider et Continuer'.</p>
      </div>
      <br/><br/>
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
