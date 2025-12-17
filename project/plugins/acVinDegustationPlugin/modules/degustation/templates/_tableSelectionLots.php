<table class="table table-bordered table-condensed table-striped">
    <thead>
        <tr>
            <th class="col-xs-1">Degustation voulue<br/> à partir du</th>
            <th class="col-xs-3">Opérateur</th>
            <th class="col-xs-1">Provenance</th>
            <th class="col-xs-1">Logement</th>
            <th class="col-xs-5">Produit (millésime, spécificité)</th>
            <th class="col-xs-1">Volume</th>
            <th class="col-xs-1"><?php if($degustation->getType() == TourneeClient::TYPE_MODEL): ?>À prélever?<?php else: ?>À déguster<?php endif; ?></th>
        </tr>
    </thead>
    <tbody>
    <?php $dates = $form->getDateDegustParDrev();
        foreach ($form['lots'] as $key => $lotForm):
            $lot = $form->getLot($key);
    ?>
      <tr class="vertical-center cursor-pointer" data-adherent="<?php echo $lot->declarant_identifiant ?>">
        <td><?php echo DateTime::createFromFormat('Y-m-d', $dates[$lot->unique_id])->format('d/m/Y') ?></td>
        <td><?php echo $lot->declarant_nom; ?></td>
        <td>
        <?php if ($lot instanceof stdClass): ?>
            <a href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));  ?>">
                <?php echo (property_exists($lot, 'type_document')) ? $lot->type_document : $lot->initial_type ;?>
            </a>
        <?php else : ?>
          <?php if ($lot->getUniqueId()): ?>
              <a href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));  ?>">
          <?php endif ?>
          <?php echo $lot->getTypeProvenance() ?>
          <?php if ($lot->getUniqueId()): ?>
              </a>
          <?php endif ?>
        <?php endif ?>
        </td>
        <td><?php echo $lot->numero_logement_operateur; ?></td>
        <td><?php echo showProduitCepagesLot($lot, false) ?></td>
        <td class="edit text-right">
         <?php if ($lot->volume): ?>
          <?php echoFloat($lot->volume); ?><small class="text-muted">&nbsp;hl</small>
          <a title="Modifier le volume" href="<?php echo url_for("degustation_lot_historique", array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id)); ?>">
            <i class="glyphicon glyphicon-share-alt"></i>
          </a>
         <?php endif ?>
        </td>
        <td class="text-center" data-hash="<?php echo $lot->declarant_nom; ?>">
          <div style="margin-bottom: 0;" class="form-group <?php if($form['lots'][$key]['preleve']->hasError()): ?>has-error<?php endif; ?>">
            <?php echo $form['lots'][$key]['preleve']->renderError() ?>
              <div class="col-xs-12">
                <label class="switch-xl">
                    <?php echo $form['lots'][$key]['preleve']->render(array('class' => "degustation switch", "data-preleve-adherent" => $lot->declarant_identifiant, "data-preleve-lot" => $lot->unique_id)); ?>
                    <span class="slider-xl round"></span>
                </label>
              </div>
          </div>
        </td>
      </tr>
    <?php  endforeach; ?>
    </tbody>
</table>
