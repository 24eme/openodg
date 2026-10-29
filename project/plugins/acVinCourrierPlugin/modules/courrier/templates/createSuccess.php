<?php use_helper('Float'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant)); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant, 'campagne' => $lot->campagne)); ?>" ><?php echo $lot->campagne ?></a>
  <li><a href="" class="active" >N° dossier : <?php echo $lot->numero_dossier ?> - N° archive : <?php echo $lot->numero_archive ?></a></li>
</ol>

<h2>Création d'un courrier pour <?php echo $etablissement->getNom(); ?></h2>

<?php include_partial('global/flash'); ?>

<?php include_partial('chgtdenom/infoLotOrigine', array('lot' => $form->getLot(), 'opacity' => false)); ?>


<form action="<?php echo url_for('courrier_lot_creation', array('identifiant' => $etablissement->identifiant, 'lot_unique_id' => $lot->unique_id)) ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>

    <div class="bg-danger">
    <?php echo $form->renderGlobalErrors(); ?>
    </div>

    <div class="row">
        <div class="form-group">
              <div class="col-sm-3 col-xs-12 text-right">
                  <?php echo $form["courrier_type"]->renderError(); ?>
                  <?php echo $form["courrier_type"]->renderLabel("Type de courrier à générer", array("class" => "")); ?> :
              </div>
              <div class="col-sm-8 col-xs-12">
                  <?php echo $form["courrier_type"]->render(array("class" => "form-control")); ?>
              </div>
        </div>
        <div class="col-xs-2">
        </div>
        <div class="col-xs-7">
        </div>
        <div class="col-xs-3">
            <button type="submit" class="btn btn-success">Générer le courrier</button>
        </div>
    </div>
</div>
