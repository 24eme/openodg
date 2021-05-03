<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant)); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant, 'campagne' => $lot->campagne)); ?>" ><?php echo $lot->campagne ?></a>
  <li><a href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $etablissement->identifiant, 'unique_id' => $lot->unique_id));  ?>">N° dossier : <?php echo $lot->numero_dossier ?> - N° archive : <?php echo $lot->numero_archive ?></a></li>
  <li><a href="" class="active" >Modification du lot</a></li>
</ol>

<h2><?php echo $etablissement->getNom(); ?> - Modification du lot n° <?php echo $lot->numero_archive; ?></h2>

<form style="margin-top: 20px;" role="form" action="" method="post" id="form_lot_modification" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <?php include_partial('degustation/lotForm', array('form' => $form, 'lot' => $lot)); ?>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $etablissement->identifiant, 'unique_id' => $lot->unique_id)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok
"></span>&nbsp;Appliquer</button>
        </div>
    </div>
</form>
