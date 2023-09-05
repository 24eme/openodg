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
        <td><?php echo DateTime::createFromFormat('Ymd', $dates[$lot->id_document])->format('d/m/Y') ?></td>
        <td><?php echo $lot->declarant_nom; ?></td>
        <td>
          <?php if ($lot->id_document !== $degustation->_id): ?>
          <a href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));  ?>">
            <?php if ($lot->initial_type && strpos($lot->initial_type, 'Degustation:') === 0): ?>
                ALÉ<?php echo (strpos($lot->initial_type, 'renforce') !== false) ? 'R' : '' ?>
            <?php else: ?>
                <?php echo substr($lot->id_document, 0, 4).' n°&nbsp;'.$lot->numero_dossier; ?>
            <?php endif ?>
          </a>
          <?php endif ?>
        </td>
        <td><?php echo $lot->numero_logement_operateur; ?></td>
        <td><?php echo showProduitCepagesLot($lot, false) ?></td>
        <td class="edit text-right">
         <?php if ($lot->id_document !== $degustation->_id): ?>
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
                <?php echo $form['lots'][$key]['preleve']->render(array('class' => "degustation bsswitch", "data-preleve-adherent" => $lot->declarant_identifiant, "data-preleve-lot" => $lot->unique_id, 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
              </div>
          </div>
        </td>
      </tr>
    <?php  endforeach; ?>
    </tbody>
</table>

