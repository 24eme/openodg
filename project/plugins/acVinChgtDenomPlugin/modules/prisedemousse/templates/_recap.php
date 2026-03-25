<?php use_helper('Lot'); ?>

<?php if ($lotOrigine = $prisedemousse->getLotOrigine()) : ?>
    <?php include_partial('infoLotOrigine', array('lot' => $lotOrigine, 'opacity' => true)); ?>
<?php else : ?>
    <div class="well">
        Déclare posséder un lot de <strong><?php echo $prisedemousse->getOrigineProduitLibelleAndCepages() ?></strong> de <strong><?php echoFloat($prisedemousse->getOrigineVolume()) ?></strong> <span class="text-muted">hl</span>
    </div>
<?php endif ?>

<div class="col-sm-12 mb-4">
  <div class="text-center">
    <strong>Devient</strong><br />
    <span class="glyphicon glyphicon-chevron-down"></span>
  </div>
</div>

<?php
  $lots = $prisedemousse->getLotsWithPseudoDeclassement();
  foreach($lots as $k => $lot):
?>
  <div class="alert block-prisedemousse col-sm-<?php if (count($lots) == 1): ?>12<?php else: ?>6<?php endif; ?>">
    <div class="row">
      <div class="col-xs-8">
        <h4>Dos. <?php echo $lot->campagne; ?>
          n°&nbsp;<strong><?php echo $lot->numero_dossier; ?></strong> –
          <?php if($lot->numero_archive):?>
             <a href="<?php echo url_for('degustation_lot_historique',array('identifiant' => $lot->declarant_identifiant, 'unique_id' => $lot->unique_id)); ?>">Lot n°&nbsp;<strong><?php echo $lot->numero_archive; ?></a></strong>
          <?php else: ?>
            <strong>Nouveau lot</strong>
          <?php endif; ?>
        </h4>
      </div>
      <div class="col-xs-4 text-right">
      <?php if (!$prisedemousse->validation_odg && $sf_user->isAdmin() && $lot->isLogementEditable() && !$lot->numero_archive): ?>
        <div style="margin-bottom: 0;" class="<?php if($form->hasErrors()): ?>has-error<?php endif; ?>">
          <?php echo $form['affectable']->renderError() ?>
          <div class="">
              Dégustable :
              <label class="switch-xl">
                  <?php echo $form['affectable']->render(array('class' => "prisedemousse", "data-preleve-adherent" => "$lot->declarant_identifiant", "data-preleve-lot" => "$lot->unique_id")); ?>
                <span class="slider-xl round"></span>
              </label>
          </div>
        </div>
      <?php else: ?>
          <span>Contrôle :</span>
          <?php echo pictoDegustable($lot); ?>
      <?php endif; ?>
      </div>
    </div>
    <div class="table table-condensed" style="margin: 0;">
            <div>
              <div style="border: none;" class="m-3">
                Logement :
                <?php if($lot->isLogementEditable()): ?>
                  <a href="#" data-toggle="modal" data-target="#modal_lot_logement_<?= ($lot->isLotOrigine()) ? 'origine' : 'change' ?>">
                    <strong><?php echo $lot->numero_logement_operateur; ?></strong>&nbsp;<span class="glyphicon glyphicon-edit">&nbsp;</span>
                  </a>
                <?php else: ?>
                  <strong><?php echo $lot->numero_logement_operateur; ?></strong>
                <?php endif; ?>
              </div>
              <?php if ($lot->adresse_logement): ?>
                  <div style="border: none;" class="m-3">
                  Adresse du site&nbsp;: <?php echo $lot->adresse_logement; ?>
                  </div>
              <?php endif; ?>
              <div style="border: none;" class="m-3">
                Produit : <strong><?php echo showProduitCepagesLot($lot) ?></strong>
              </div>

              <div style="border: none;" class="m-3">Volume : <strong><?php echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></div>
            </div>
        </div>
  </div>
<?php endforeach; ?>
