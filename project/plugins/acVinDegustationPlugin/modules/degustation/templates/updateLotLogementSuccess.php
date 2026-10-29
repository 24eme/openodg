<div class="page-header">
  <?php $l = $degustation->lots[$lot]; ?>
  <h2>Logement du lot <?= $l->getProduitLibelle() ?> <small class="text-muted"><?= $l->millesime ?></small></h2>
  <h3>Récapitulatif du lot :</h3>
  <div class="well">
    <?php if ($l->details): ?>
      <?= $l->details ?> <br/>
    <?php endif; ?>
    Spécificité : <?= $l->specificite ?> <br/>
    Volume : <?= $l->volume ?> <small class="text-muted">hl</small><br/>
    Opérateur : <?= $l->declarant_nom ?> &bull; Dossier : <?= $l->numero_dossier ?> &bull; Archive : <?= $l->numero_archive ?>
  </div>
</div>

<div class="container">
  <form action="<?= url_for('degustation_preleve_update_logement', ['id' => $degustation->_id, 'lot' => $lot]) ?>" method="post" class="form-horizontal">
    <?= $form->renderHiddenFields() ?>
    <div class="row">
      <div class="col-xs-5">
        <div class="form-group">
          <label for="oldLogement">Logement actuel</label>
          <input type="text" disabled class="form-control" id="oldLogement" value="<?= $degustation->lots[$lot]->numero_logement_operateur ?>">
        </div>
      </div>

      <div class="col-xs-5 col-xs-offset-2">
        <?= $form['lot_'.$lot]->renderLabel('Nouveau logement') ?>
        <?= $form['lot_'.$lot]->render(['class' => 'form-control']) ?>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-4">
        <a href="<?= url_for('degustation_preleve', ['id'=>$degustation->_id]) ?>" class="btn btn-default btn-upper">Retour</a>
      </div>
      <div class="col-xs-4 col-xs-offset-4 text-right">
        <button type="submit" href="<?= url_for('degustation_preleve', ['id'=>$degustation->_id]) ?>" class="btn btn-primary btn-upper">Valider</button>
      </div>
    </div>
  </form>
</div>
