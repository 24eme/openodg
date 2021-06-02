<?php use_helper('Date'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Float') ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant)); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant, 'campagne' => $lot->campagne)); ?>" ><?php echo $lot->campagne ?></a>
  <li><a href="" class="active" >N° dossier : <?php echo $lot->numero_dossier ?> - N° archive : <?php echo $lot->numero_archive ?></a></li>
  <li><a href="<?php echo url_for('degustation'); ?>">Affecter à une dégustation</a></li>
</ol>

<h2><?php echo $etablissement->getNom(); ?> - Affectation du lot n° <?php echo $lot->numero_archive; ?></h2>

<div class="row">
    <div class="col-xs-6" style="padding-top: 30px;">
      <?php include_partial('chgtdenom/infoLotOrigine', array('lot' => $lot, 'opacity' => false)); ?>

    </div>
    <div class="col-xs-6" style="padding-top: 30px;">

    </div>
</div>

<br/>

<form style="margin-top: 20px;" role="form" action="" method="post" id="form_affectation_lot" class="form-horizontal">
  <?php echo $form->renderHiddenFields(); ?>
  <?php echo $form->renderGlobalErrors(); ?>

  <div class="row form-group">
    <?php echo $form['degustation']->renderLabel("Degustation selectionnée :", array('class' => "col-sm-3 control-label")); ?>
    <div class="col-xs-9" style="padding:10px;">
      <?php echo $form['degustation']->render(); ?>
      <?php echo $form['degustation']->renderError(); ?>
    </div>
  </div>

  <div class="row form-group">
      <?php echo $form['preleve']->renderLabel("Prelevé :", array('class' => "col-sm-3 control-label")); ?>
    <div class="col-xs-9" style="padding-top: 10px;">
      <?php echo $form['preleve']->render(array('class' => "degustation bsswitch", "data-preleve-adherent" => "$lot->declarant_identifiant", "data-preleve-lot" => "$lot->unique_id",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
    </div>
  </div>

  <div class="row form-group">
      <?php echo $form['numero_table']->renderLabel("Table selectionnée :", array('class' => "col-sm-3 control-label")); ?>
    <div class="col-xs-9" style="padding:10px;">
        <?php echo $form['numero_table']->render(); ?>
        <?php echo $form['numero_table']->renderError(); ?>
    </div> 
  </div>

  <div class="form-group row row-margin row-button">
    <div class="col-xs-6">
        <a href="<?php echo url_for('degustation_lot_historique', ['identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id]) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Précédent</a>
    </div>
    <div class="col-xs-6 text-right">
        <button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button>
    </div>
  </div>

</form>
