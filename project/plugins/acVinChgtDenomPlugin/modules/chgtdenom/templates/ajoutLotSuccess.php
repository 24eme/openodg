<?php use_helper('Date') ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
  <li class="active"><a href="">Changement de dénomination du <?php echo date('Y-m-d'); ?></a></li>
  <li class="active"><a href="">Ajout de Lot</a></li>
</ol>

<h2><?php echo $etablissement->getNom(); ?> - Ajout d'un lot </h2>

<form style="margin-top: 20px;" role="form" action="" method="post" id="form_ajout_lot" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <?php include_partial('degustation/lotForm', array('form' => $form, 'lot' => $lot)); ?>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('chgtdenom_lots', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok
"></span>&nbsp;Ajouter</button>
        </div>
    </div>
</form>
