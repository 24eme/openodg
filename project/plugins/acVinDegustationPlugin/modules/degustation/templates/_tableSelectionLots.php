<div class="input-group" style="margin-bottom: 0; position: relative;">
    <span class="input-group-addon">Filtrer le tableau</span>
    <input id="table_filtre" type="text" class="form-control" placeholder="par nom, logement, produit, volume, ..." autocomplete="off" />
    <a href="" id="btn_annuler_filtre" tabindex="-1" class="small hidden" style="z-index: 3; right: 10px; top: 10px; position: absolute; color: grey;"><span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span></a>
</div>
<table class="table table-bordered table-condensed table-striped table_filterable" style="border-width: 0;">
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
      <tr class="vertical-center cursor-pointer searchable" data-adherent="<?php echo $lot->declarant_identifiant ?>">
        <td><?php echo DateTime::createFromFormat('Y-m-d', $dates[$lot->unique_id])->format('d/m/Y') ?></td>
        <td><?php echo $lot->declarant_nom; ?></td>
        <td>
        <?php if ($lot instanceof stdClass): ?>
            <a href="<?php echo url_for('degustation_lot_historique', array('identifiant' => $lot->declarant_identifiant, 'unique_id'=> $lot->unique_id));  ?>">
                <?php if ($lot->initial_type === PriseDeMousseClient::TYPE_MODEL): ?>
                    <?php echo PriseDeMousseClient::INITIAL_TYPE_PDM; ?>
                <?php else: ?>
                    <?php echo (property_exists($lot, 'type_document')) ? $lot->type_document : $lot->initial_type; ?>
                <?php endif ?>
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
