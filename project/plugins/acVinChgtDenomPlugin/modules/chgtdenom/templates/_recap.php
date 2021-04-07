<style>
  #declassement_filigrane{
    position:absolute;
    font-size: 4em;
    top: 22%;
    left: 37%;
    rotate: -33deg;
    opacity: 0.3;
    text-transform: uppercase;
  }
  .block-chgtDenom{
    background-color: #f8f8f8;
    border: 1px solid #e7e7e7;
  }
</style>
<?php include_partial('infoLotOrigine', array('lot' => $chgtDenom->getLotOrigine(), 'opacity' => true)); ?>

<div class="col-sm-12 mb-5">
  <div class="text-center">
    <strong>Devient</strong><br />
    <span class="glyphicon glyphicon-chevron-down"></span>
  </div>
</div>

<?php
  foreach($chgtDenom->lots as $k => $lot):
?>
  <div class="alert block-chgtDenom col-sm-<?php if (count($chgtDenom->lots) == 1): ?>12<?php else: ?>6<?php endif; ?>">
  <?php if($chgtDenom->isDeclassement() && $lot->statut == Lot::STATUT_DECLASSE): ?>
    <div id="declassement_filigrane" class="text-danger">Déclassé</div>
  <?php endif; ?>
    <div class="row">
      <div class="col-xs-9">
        <h4>Dossier <?php echo $lot->campagne; ?> n°&nbsp;<strong><?php echo $lot->numero_dossier; ?></strong> – Lot n°&nbsp;<strong><?php echo $lot->numero_archive; ?></strong></h4>
      </div>
      <div class="col-xs-3 text-right">
      <?php if ($chgtDenom->isChgtDenomination() && !$chgtDenom->validation_odg && isset($form['lots']) && $sf_user->isAdmin()): ?>
        <div style="margin-bottom: 0;" class="<?php if($form['lots'][$lot->getKey()]->hasError()): ?>has-error<?php endif; ?>">
          <?php echo $form['lots'][$lot->getKey()]['affectable']->renderError() ?>
          <div class="col-xs-12">
              <?php echo $form['lots'][$lot->getKey()]['affectable']->render(array('class' => "chgtDenom bsswitch", "data-preleve-adherent" => "$lot->declarant_identifiant", "data-preleve-lot" => "$lot->unique_id",'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
          </div>
        </div>
      <?php else: ?>
        <?php if($chgtDenom->isChgtDenomination()): ?>
          <span>Dgstable :</span>
          <?php echo pictoDegustable($lot); ?>
        <?php endif; ?>
      <?php endif; ?>
      </div>
    </div>
    <div class="table table-condensed" style="margin: 0;">
            <div>
              <div style="border: none;" class="m-3">
                Logement :
                <?php if(!$chgtDenom->isValide()): ?>
                  <a href="#" data-toggle="modal" data-target="#modal_lot_<?php echo $k ?>">
                    <strong><?php echo $lot->numero_logement_operateur; ?></strong>&nbsp;<span class="glyphicon glyphicon-edit">&nbsp;</span>
                  </a>
                <?php else: ?>
                  <strong><?php echo $lot->numero_logement_operateur; ?></strong>
                <?php endif; ?>
              </div>

              <div style="border: none;" class="m-3">
                Produit : <strong><?php echo showProduitLot($lot) ?></strong>
              </div>

              <div style="border: none;" class="m-3">Volume : <strong><?php echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></div>
            </div>
        </div>
  </div>
<?php endforeach; ?>
